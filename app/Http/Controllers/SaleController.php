<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(): View
    {
        $sales = Sale::query()
            ->with(['product', 'customer', 'staff'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('sales.history', compact('sales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isCartOrder = is_array($request->input('items'));
        $productRule = [
            'required',
            'integer',
            Rule::exists('products', 'id')->whereNull('deleted_at'),
        ];
        $rules = $isCartOrder
            ? [
                'items' => ['required', 'array', 'min:1', 'max:100'],
                'items.*.product_id' => $productRule,
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            ]
            : [
                'product_id' => $productRule,
                'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
                'quantity' => ['required', 'integer', 'min:1'],
            ];

        $validated = $request->validate($rules, [
            'product_id.exists' => 'The selected product is archived or unavailable.',
            'items.*.product_id.exists' => 'One of the selected products is archived or unavailable.',
            'quantity.required' => 'Enter a quantity to complete this order.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'items.required' => 'Add at least one item to the order.',
            'items.min' => 'Add at least one item to the order.',
            'items.*.quantity.required' => 'Every order item needs a quantity.',
            'items.*.quantity.integer' => 'Order quantities must be whole numbers.',
            'items.*.quantity.min' => 'Order quantities must be at least 1.',
        ]);

        $requestedLines = $isCartOrder
            ? $validated['items']
            : [[
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]];
        $quantities = collect($requestedLines)
            ->groupBy('product_id')
            ->map(fn ($lines) => $lines->sum('quantity'))
            ->sortKeys();

        $saleDetails = DB::transaction(function () use ($quantities, $validated, $request, $isCartOrder): array {
            $products = Product::query()
                ->whereIn('id', $quantities->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $quantities->count()) {
                throw ValidationException::withMessages([
                    $isCartOrder ? 'items' : 'product_id' => 'One or more selected products are archived or unavailable.',
                ]);
            }

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                $quantity = (int) $quantity;

                if ($quantity > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        $isCartOrder ? 'items' : 'quantity' => "Only {$product->stock_quantity} unit(s) of {$product->name} are currently available.",
                    ]);
                }

                Sale::create([
                    'product_id' => $product->id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'sold_by' => $request->user()->id,
                    'quantity' => $quantity,
                    'total_price' => number_format((float) $product->price * $quantity, 2, '.', ''),
                ]);

                $product->stock_quantity -= $quantity;
                $product->save();
            }

            return [
                'item_count' => $quantities->sum(),
                'single_product' => $quantities->count() === 1 ? $products->first() : null,
            ];
        });

        if ($isCartOrder) {
            $itemLabel = $saleDetails['item_count'] === 1 ? 'item' : 'items';

            return redirect()->route('dashboard')->with(
                'status',
                "Order complete. {$saleDetails['item_count']} {$itemLabel} charged."
            );
        }

        $product = $saleDetails['single_product'];

        return redirect()->route('dashboard')->with('status',
            "Order complete. {$product->name} has {$product->stock_quantity} unit(s) remaining."
        );
    }
}
