<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
        ]);

        $salesQuery = Sale::query()
            ->with(['product', 'customer', 'staff']);

        if (! empty($filters['start_date'])) {
            $salesQuery->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $salesQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        $sales = $salesQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'sales_count' => $sales->count(),
            'units_sold' => $sales->sum('quantity'),
            'total_sales' => $sales->sum(
                fn (Sale $sale) => (float) $sale->total_price
            ),
        ];

        return view('reports.sales', compact('sales', 'filters', 'summary'));
    }

    public function inventory(Request $request): View
    {
        $filters = $request->validate([
            'category' => [
                'nullable',
                Rule::in(['all', 'food', 'drinks', 'add_on']),
            ],
            'stock' => [
                'nullable',
                Rule::in(['all', 'low', 'out']),
            ],
        ]);

        $category = $filters['category'] ?? 'all';
        $stock = $filters['stock'] ?? 'all';

        $productsQuery = Product::query()
            ->when(
                $category !== 'all',
                fn ($query) => $query->where('category', $category)
            )
            ->when(
                $stock === 'low',
                fn ($query) => $query
                    ->where('stock_quantity', '>', 0)
                    ->where('stock_quantity', '<=', 5)
            )
            ->when(
                $stock === 'out',
                fn ($query) => $query->where('stock_quantity', 0)
            );

        $products = $productsQuery
            ->orderBy('name')
            ->get();

        $summary = [
            'product_count' => $products->count(),
            'units_in_stock' => $products->sum('stock_quantity'),
            'low_stock_count' => $products
                ->filter(
                    fn (Product $product) =>
                        $product->stock_quantity > 0
                        && $product->stock_quantity <= 5
                )
                ->count(),
            'out_of_stock_count' => $products
                ->where('stock_quantity', 0)
                ->count(),
            'inventory_value' => $products->sum(
                fn (Product $product) =>
                    (float) $product->price * $product->stock_quantity
            ),
        ];

        return view('reports.inventory', compact(
            'products',
            'filters',
            'category',
            'stock',
            'summary'
        ));
    }
}