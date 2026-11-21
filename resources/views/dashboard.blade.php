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

        @if (session('status'))
            <p class="success-message" role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="form-alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form class="search-form menu-search" method="GET" action="{{ route('dashboard') }}" role="search">
            <label class="visually-hidden" for="menu-search">Search menu</label>
            @foreach ($cartQuantities as $cartProductId => $cartQuantity)
                <input type="hidden" name="cart[{{ $cartProductId }}]" value="{{ $cartQuantity }}">
            @endforeach
            @if ($category !== 'all')
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="search-controls">
                <span class="search-icon" aria-hidden="true">⌕</span>
                <input id="menu-search" name="search" type="search" value="{{ $search }}" placeholder="Search the menu" maxlength="100">
                <button type="submit">Search</button>
            </div>
        </form>

        <nav class="menu-filters" aria-label="Menu categories">
            @foreach (['all' => 'All', 'food' => 'Food', 'drinks' => 'Drinks', 'add_on' => 'Add-on'] as $filterValue => $filterLabel)
                <a
                    href="{{ route('dashboard', array_filter(['search' => $search, 'category' => $filterValue === 'all' ? null : $filterValue, 'cart' => $cartQuantities], fn ($value) => $value !== null && $value !== '')) }}"
                    class="{{ $category === $filterValue ? 'is-active' : '' }}"
                    @if ($category === $filterValue) aria-current="page" @endif
                >{{ $filterLabel }}</a>
            @endforeach
        </nav>

        @if ($products->isEmpty())
            <div class="empty-state">
                <h2>No matching products</h2>
                <p>Try a different search term.</p>
                <a href="{{ route('dashboard', ['category' => $category === 'all' ? null : $category, 'cart' => $cartQuantities]) }}">Clear search</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    @php
                        $stockLabel = $product->stock_quantity === 0
                            ? 'Sold out'
                            : ($product->stock_quantity <= 5 ? 'Low stock' : 'Available');
                        $quantityInCart = $cartQuantities[$product->id] ?? 0;
                        $addCart = $cartQuantities;
                        $addCart[$product->id] = $quantityInCart + 1;
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
                            @if ($product->stock_quantity > $quantityInCart)
                                <a class="select-product select-product-button" href="{{ route('dashboard', ['search' => $search, 'category' => $category === 'all' ? null : $category, 'cart' => $addCart]) }}" aria-label="Add {{ $product->name }} to the current order">
                                    Add item <span aria-hidden="true">+</span>
                                </a>
                            @else
                                <span class="select-product select-product-button is-disabled">Stock limit</span>
                            @endif
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

        @if ($cartLines->isNotEmpty())
            <div class="cart-lines">
                @foreach ($cartLines as $line)
                    @php
                        $cartProduct = $line['product'];
                        $cartQuantity = $line['quantity'];
                        $decrementCart = $cartQuantities;
                        $incrementCart = $cartQuantities;
                        $removeCart = $cartQuantities;

                        if ($cartQuantity > 1) {
                            $decrementCart[$cartProduct->id] = $cartQuantity - 1;
                        } else {
                            unset($decrementCart[$cartProduct->id]);
                        }

                        $incrementCart[$cartProduct->id] = $cartQuantity + 1;
                        unset($removeCart[$cartProduct->id]);
                    @endphp
                    <div class="selected-product cart-line">
                        <div class="cart-line-thumb" aria-hidden="true">
                            @if ($cartProduct->image)
                                <img src="{{ asset('storage/'.$cartProduct->image) }}" alt="">
                            @endif
                        </div>
                        <div class="cart-line-content">
                            <strong>{{ $cartProduct->name }}</strong>
                            <p>{{ $cartProduct->stock_quantity }} available</p>
                            <div class="cart-quantity" aria-label="Quantity for {{ $cartProduct->name }}">
                                <a href="{{ route('dashboard', ['search' => $search, 'category' => $category === 'all' ? null : $category, 'cart' => $decrementCart]) }}" aria-label="Decrease {{ $cartProduct->name }} quantity">−</a>
                                <span>{{ $cartQuantity }}</span>
                                @if ($cartQuantity < $cartProduct->stock_quantity)
                                    <a href="{{ route('dashboard', ['search' => $search, 'category' => $category === 'all' ? null : $category, 'cart' => $incrementCart]) }}" aria-label="Increase {{ $cartProduct->name }} quantity">+</a>
                                @else
                                    <span class="is-disabled" aria-label="Stock limit reached">+</span>
                                @endif
                                <a class="cart-remove" href="{{ route('dashboard', ['search' => $search, 'category' => $category === 'all' ? null : $category, 'cart' => $removeCart]) }}">Remove</a>
                            </div>
                        </div>
                        <strong class="cart-line-price">₱{{ number_format($line['line_total'], 2) }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="cart-summary">
                <div><span>Items</span><strong>{{ $cartLines->sum('quantity') }}</strong></div>
                <div><span>Total</span><strong>₱{{ number_format($cartTotal, 2) }}</strong></div>
            </div>
        @else
            <p class="preview-empty">Add an item from the menu to start an order.</p>
        @endif

        @if ($cartLines->isNotEmpty())
            <form class="cart-checkout" method="POST" action="{{ route('sales.store') }}">
                @csrf
                @foreach ($cartLines as $index => $line)
                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $line['product']->id }}">
                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] }}">
                @endforeach
                <button class="cart-action" type="submit">Charge ₱{{ number_format($cartTotal, 2) }}</button>
            </form>
        @else
            <span class="cart-action is-disabled" aria-disabled="true">Complete Order</span>
        @endif
    </aside>
</div>
@endsection
