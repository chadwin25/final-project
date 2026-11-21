@extends('layouts.pos')

@section('title', 'Products · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endpush

@section('content')
<div class="management-heading">
    <div>
        <h1>Products</h1>
        <p>Manage food items, prices, stock, and photos.</p>
    </div>
    <a class="primary-button management-primary" href="{{ route('products.create') }}">Add product</a>
</div>

@if (session('status'))
    <div class="management-success" role="status">{{ session('status') }}</div>
@endif

<div class="product-tabs" aria-label="Product views">
    <a
        href="{{ route('products.index') }}"
        class="{{ $showArchived ? '' : 'is-current' }}"
        @if (! $showArchived) aria-current="page" @endif
    >Active products</a>
    <a
        href="{{ route('products.index', ['view' => 'archived']) }}"
        class="{{ $showArchived ? 'is-current' : '' }}"
        @if ($showArchived) aria-current="page" @endif
    >Archived products</a>
</div>

<form class="management-search" method="GET" action="{{ route('products.index') }}" role="search">
    @if ($showArchived)
        <input type="hidden" name="view" value="archived">
    @endif
    <label for="product-search">Search products</label>
    <div>
        <input
            id="product-search"
            name="search"
            type="search"
            value="{{ $search }}"
            maxlength="100"
            placeholder="Search by product name"
        >
        <button type="submit">Search</button>
    </div>
</form>

@if ($products->isEmpty())
    <div class="management-empty">
        <h2>{{ $showArchived ? 'No archived products' : 'No products found' }}</h2>
        <p>{{ $search !== '' ? 'Try another search term.' : ($showArchived ? 'Archived products will appear here.' : 'Add the first product to get started.') }}</p>
    </div>
@else
    <ul class="product-list">
        @foreach ($products as $product)
            <li class="product-row">
                <div class="product-thumb">
                    @if ($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                    @else
                        {{-- Product image appears here after upload. --}}
                        <span aria-hidden="true">No photo</span>
                    @endif
                </div>

                <div class="product-row-name">
                    <strong>{{ $product->name }}</strong>
                    <small>#{{ $product->id }}</small>
                </div>

                <div class="product-row-detail">
                    <span>Price</span>
                    <strong>₱{{ number_format((float) $product->price, 2) }}</strong>
                </div>

                <div class="product-row-detail">
                    <span>Stock</span>
                    <strong>{{ $product->stock_quantity }} units</strong>
                </div>

                <div class="product-row-actions">
                    @if ($showArchived)
                        <form method="POST" action="{{ route('products.restore', $product->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Restore</button>
                        </form>
                    @else
                        <a href="{{ route('products.edit', $product) }}">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Archive</button>
                        </form>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>

    @if ($products->hasPages())
        <nav class="management-pagination" aria-label="Product pages">
            @if ($products->onFirstPage())
                <span>Previous</span>
            @else
                <a href="{{ $products->previousPageUrl() }}">Previous</a>
            @endif

            <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>

            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}">Next</a>
            @else
                <span>Next</span>
            @endif
        </nav>
    @endif
@endif
@endsection
