<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $search = mb_substr($search, 0, 100);
        $category = in_array($request->query('category'), ['food', 'drinks', 'add_on'], true)
            ? $request->query('category')
            : 'all';

        $products = Product::query()
            ->when(
                $category !== 'all',
                fn ($query) => $query->where('category', $category)
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where('name', 'like', '%'.$search.'%')
            )
            ->orderBy('name')
            ->get();

        $cartQuantities = [];
        $requestedCart = $request->query('cart', []);

        if (is_array($requestedCart)) {
            foreach ($requestedCart as $productId => $quantity) {
                $productId = filter_var($productId, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

                if ($productId && $quantity && $quantity > 0) {
                    $cartQuantities[$productId] = min($quantity, 99);
                }
            }
        }

        $cartProducts = Product::query()
            ->whereIn('id', array_keys($cartQuantities))
            ->get()
            ->keyBy('id');

        $cartLines = collect();

        foreach ($cartQuantities as $productId => $quantity) {
            $product = $cartProducts->get($productId);

            if (! $product || $product->stock_quantity < 1) {
                unset($cartQuantities[$productId]);
                continue;
            }

            $quantity = min($quantity, $product->stock_quantity);
            $cartQuantities[$productId] = $quantity;
            $cartLines->push([
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => (float) $product->price * $quantity,
            ]);
        }

        return view('dashboard', [
            'products' => $products,
            'search' => $search,
            'category' => $category,
            'cartQuantities' => $cartQuantities,
            'cartLines' => $cartLines,
            'cartTotal' => $cartLines->sum('line_total'),
        ]);
    }
}
