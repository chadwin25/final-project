@extends('layouts.pos')

@section('title', 'Register · CampusBite')

@section('content')
<div class="dashboard-page">
    <section class="menu-area" aria-labelledby="menu-heading">
        <div class="section-heading dashboard-heading">
            <div>
                <h1 id="menu-heading">Today’s menu</h1>
            </div>
        </div>

        <form class="search-form menu-search" method="GET" action="{{ route('dashboard') }}" role="search">
            <label class="visually-hidden" for="menu-search">Search menu</label>
            <div class="search-controls">
                <span class="search-icon" aria-hidden="true">⌕</span>
                <input id="menu-search" name="search" type="search" value="{{ $search }}" placeholder="Search the menu" maxlength="100">
                <button type="submit">Search</button>
            </div>
        </form>

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
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                            @endif
                        </div>

                        <div class="product-card-content">
                            <div class="product-card-heading">
                                <h2>{{ $product->name }}</h2>
                                <span class="product-stock {{ $product->stock_quantity === 0 ? 'stock-out' : ($product->stock_quantity <= 5 ? 'stock-low' : '') }}">{{ $stockLabel }}</span>
                            </div>
                            <div class="product-meta">
                                <strong>₱{{ number_format((float) $product->price, 2) }}</strong>
                                <span>{{ $product->stock_quantity }} in stock</span>
                            </div>
                            <a class="select-product select-product-button" href="{{ route('dashboard', ['product' => $product->id, 'search' => $search]) }}" aria-label="Select {{ $product->name }} for preview">
                                Select item <span aria-hidden="true">+</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <aside class="sale-preview cart-panel" aria-labelledby="sale-heading">
        <div class="preview-heading">
            <div>
                <h2 id="sale-heading">Current Order</h2>
            </div>
        </div>

        @if ($selectedProduct)
            <div class="selected-product cart-line">
                <div class="cart-line-thumb" aria-hidden="true">
                    @if ($selectedProduct->image)
                        <img src="{{ asset('storage/'.$selectedProduct->image) }}" alt="">
                    @endif
                </div>
                <div>
                    <span>Selected item</span>
                    <strong>{{ $selectedProduct->name }}</strong>
                    <p>{{ $selectedProduct->stock_quantity }} available</p>
                </div>
                <strong class="cart-line-price">₱{{ number_format((float) $selectedProduct->price, 2) }}</strong>
            </div>

            <div class="cart-summary">
                <div><span>Unit price</span><strong>₱{{ number_format((float) $selectedProduct->price, 2) }}</strong></div>
                <div><span>Total</span><strong>₱{{ number_format((float) $selectedProduct->price, 2) }}</strong></div>
            </div>
        @else
            <p class="preview-empty">Choose a product from the menu to start an order.</p>
        @endif

        @if ($selectedProduct)
            <a class="cart-action" href="{{ route('sales.create', ['product_id' => $selectedProduct->id]) }}">Charge ₱{{ number_format((float) $selectedProduct->price, 2) }}</a>
        @else
            <span class="cart-action is-disabled" aria-disabled="true">Complete Order</span>
        @endif
    </aside>
</div>
@endsection
