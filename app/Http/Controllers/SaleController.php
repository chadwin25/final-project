<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
    public function create(): View
    {
        $products = Product::query()
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock_quantity']);

        $customers = Customer::query()
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('sales.create', compact('products', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'product_id.exists' => 'The selected product is archived or unavailable.',
            'quantity.required' => 'Enter a quantity to record this sale.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
        ]);

        $saleDetails = DB::transaction(function () use ($validated, $request): array {
            $product = Product::query()
                ->whereKey($validated['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    'product_id' => 'The selected product is archived or unavailable.',
                ]);
            }

            $quantity = (int) $validated['quantity'];

            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock_quantity} unit(s) are currently available.",
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

            return [
                'product_name' => $product->name,
                'stock_quantity' => $product->stock_quantity,
            ];
        });

        return redirect()->route('sales.create')->with(
            'status',
            "Sale recorded. {$saleDetails['product_name']} has {$saleDetails['stock_quantity']} unit(s) remaining."
        );
    }
}