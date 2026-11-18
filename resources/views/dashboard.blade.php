@extends('layouts.pos')

@section('title', 'Home · CampusBite POS')

@section('content')
<div class="dashboard-grid">
    <section class="menu-area" aria-labelledby="menu-heading">
        <div class="section-heading">
            <div>
                <h1 id="menu-heading">Food counter</h1>
                <p>Browse today’s available menu and stock.</p>
            </div>
        </div>

        <form class="search-form" method="GET" action="{{ route('dashboard') }}" role="search">
            <label for="menu-search">Search menu</label>
            <div class="search-controls">
                <input
                    id="menu-search"
                    name="search"
                    type="search"
                    value="{{ $search }}"
                    placeholder="Search menu here…"
                    maxlength="100"
                >
                <button type="submit">Search</button>
            </div>
        </form>

        <div class="counter-stats" aria-label="Current counter information">
            <div><strong>{{ $productCount }}</strong><span>Menu items</span></div>
            <div><strong>{{ $stockCount }}</strong><span>Units in stock</span></div>
            <div><strong>{{ $todaySalesCount }}</strong><span>Sales today</span></div>
        </div>

        @if ($products->isEmpty())
            <div class="empty-state">
                <h2>No matching products</h2>
                <p>Try a different search term.</p>
                <a href="{{ route('dashboard') }}">Show all products</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    @php
                        $stockLabel = $product->stock_quantity === 0
                            ? 'Sold out'
                            : ($product->stock_quantity <= 5 ? 'Low stock' : 'Available');
                    @endphp

                    <article class="product-card">
                        <div class="product-image">
                            @if ($product->image)
                                <img
                                    src="{{ asset('storage/'.$product->image) }}"
                                    alt="{{ $product->name }}"
                                >
                            @else
                                <span class="image-placeholder" aria-hidden="true">
                                    Photo coming soon
                                </span>
                            @endif
                        </div>

                        <div class="product-card-content">
                            <h2>{{ $product->name }}</h2>

                            <div class="product-meta">
                                <strong>₱{{ number_format((float) $product->price, 2) }}</strong>

                                <span class="{{ $product->stock_quantity === 0 ? 'stock-out' : ($product->stock_quantity <= 5 ? 'stock-low' : '') }}">
                                    {{ $stockLabel }} · {{ $product->stock_quantity }}
                                </span>
                            </div>

                            <a
                                class="select-product"
                                href="{{ route('dashboard', ['product' => $product->id, 'search' => $search]) }}"
                                aria-label="Preview {{ $product->name }}"
                            >
                                Preview product
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <aside class="sale-preview" aria-labelledby="sale-heading">
        <div class="preview-heading">
            <div>
                <h2 id="sale-heading">Record sale</h2>
                <p>One product per sale</p>
            </div>

            <span class="preview-label">Preview</span>
        </div>

        @if ($selectedProduct)
            <div class="selected-product">
                <span>Selected product</span>
                <strong>{{ $selectedProduct->name }}</strong>
                <p>{{ $selectedProduct->stock_quantity }} available in stock</p>
            </div>

            <div class="preview-line">
                <span>Unit price</span>
                <strong>₱{{ number_format((float) $selectedProduct->price, 2) }}</strong>
            </div>
        @else
            <p class="preview-empty">
                Choose a product from the menu when products are available.
            </p>
        @endif

        <a class="select-product" href="{{ route('sales.create') }}">
            Open Record Sale
        </a>
    </aside>
</div>
@endsection