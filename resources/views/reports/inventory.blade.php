@extends('layouts.pos')

@section('title', 'Inventory report · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@endpush

@section('content')
<div class="report-page">
    <div class="report-heading">
        <div>
            <p class="report-kicker">Reports</p>
            <h1>Inventory report</h1>
            <p>Review active products, available stock, and estimated stock value.</p>
        </div>

        <div class="report-actions">
            <button type="button" onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>

    <form class="report-filters" method="GET" action="{{ route('reports.inventory') }}">
        <div class="report-filter-field">
            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="all" @selected($category === 'all')>All categories</option>
                <option value="food" @selected($category === 'food')>Food</option>
                <option value="drinks" @selected($category === 'drinks')>Drinks</option>
                <option value="add_on" @selected($category === 'add_on')>Add-on</option>
            </select>
        </div>

        <div class="report-filter-field">
            <label for="stock">Stock status</label>
            <select id="stock" name="stock">
                <option value="all" @selected($stock === 'all')>All stock</option>
                <option value="low" @selected($stock === 'low')>Low stock (1–5)</option>
                <option value="out" @selected($stock === 'out')>Out of stock</option>
            </select>
        </div>

        @error('category')
            <p class="report-error">{{ $message }}</p>
        @enderror
        @error('stock')
            <p class="report-error">{{ $message }}</p>
        @enderror

        <button class="report-filter-button" type="submit">Apply filters</button>
        <a class="report-clear-button" href="{{ route('reports.inventory') }}">Clear</a>
    </form>

    <div class="report-print-heading">
        <h2>CampusBite POS — Inventory report</h2>
        <p>
            Category: {{ $category === 'all' ? 'All categories' : ucfirst(str_replace('_', '-', $category)) }}
            · Stock: {{ $stock === 'all' ? 'All stock' : ($stock === 'low' ? 'Low stock (1–5)' : 'Out of stock') }}
        </p>
        <p>Generated {{ now('Asia/Manila')->format('M j, Y · g:i A') }}</p>
    </div>

    <div class="report-summary-grid report-summary-grid-inventory">
        <article class="report-summary-card">
            <span>Products</span>
            <strong>{{ number_format($summary['product_count']) }}</strong>
        </article>

        <article class="report-summary-card">
            <span>Units in stock</span>
            <strong>{{ number_format($summary['units_in_stock']) }}</strong>
        </article>

        <article class="report-summary-card">
            <span>Low stock</span>
            <strong>{{ number_format($summary['low_stock_count']) }}</strong>
        </article>

        <article class="report-summary-card">
            <span>Out of stock</span>
            <strong>{{ number_format($summary['out_of_stock_count']) }}</strong>
        </article>

        <article class="report-summary-card">
            <span>Estimated stock value</span>
            <strong>₱{{ number_format($summary['inventory_value'], 2) }}</strong>
        </article>
    </div>

    @if ($products->isEmpty())
        <div class="report-empty">
            <h2>No products found</h2>
            <p>There are no active products matching the selected filters.</p>
        </div>
    @else
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Category</th>
                        <th scope="col">Unit price</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Status</th>
                        <th scope="col">Stock value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @php
                            $stockLabel = $product->stock_quantity === 0
                                ? 'Out of stock'
                                : ($product->stock_quantity <= 5 ? 'Low stock' : 'Available');

                            $stockValue = (float) $product->price * $product->stock_quantity;
                        @endphp
                        <tr>
                            <td data-label="Product">{{ $product->name }}</td>
                            <td data-label="Category">
                                {{ ucfirst(str_replace('_', '-', $product->category ?? 'food')) }}
                            </td>
                            <td class="report-money" data-label="Unit price">
                                ₱{{ number_format((float) $product->price, 2) }}
                            </td>
                            <td data-label="Stock">{{ number_format($product->stock_quantity) }}</td>
                            <td data-label="Status">
                                <span class="report-stock-status {{ $product->stock_quantity === 0 ? 'is-out' : ($product->stock_quantity <= 5 ? 'is-low' : 'is-available') }}">
                                    {{ $stockLabel }}
                                </span>
                            </td>
                            <td class="report-money" data-label="Stock value">
                                ₱{{ number_format($stockValue, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Report total</th>
                        <th>{{ number_format($summary['units_in_stock']) }} units</th>
                        <th></th>
                        <th class="report-money">₱{{ number_format($summary['inventory_value'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection