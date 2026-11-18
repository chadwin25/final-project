<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $search = mb_substr($search, 0, 100);

        $products = Product::query()
            ->when(
                $search !== '',
                fn ($query) => $query->where('name', 'like', '%'.$search.'%')
            )
            ->orderBy('name')
            ->get();

        $selectedProduct = Product::find($request->integer('product'))
            ?? $products->first();

        return view('dashboard', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'search' => $search,
            'productCount' => Product::count(),
            'stockCount' => Product::sum('stock_quantity'),
            'todaySalesCount' => Sale::whereDate('created_at', today())->count(),
        ]);
    }
}