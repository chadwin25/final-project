<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Contracts\View\View;

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
}