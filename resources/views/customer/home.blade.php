@extends('layouts.customer')

@section('title', 'Menu · CampusBite')

@section('content')
<div class="dashboard-page">
    <section class="menu-area" aria-labelledby="menu-heading">
        <div class="section-heading dashboard-heading">
            <div>
                <h1 id="menu-heading">Today’s menu</h1>
            </div>
        </div>

        @if (session('status'))
            <p class="success-message" role="status">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <div class="form-alert" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="search-form menu-search" method="GET" action="{{ route('customer.home') }}" role="search">
            <label class="visually-hidden" for="menu-search">Search menu</label>
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
                    href="{{ route('customer.home', array_filter(['search' => $search, 'category' => $filterValue === 'all' ? null : $filterValue], fn ($value) => $value !== null && $value !== '')) }}"
                    class="{{ $category === $filterValue ? 'is-active' : '' }}"
                    @if ($category === $filterValue) aria-current="page" @endif
                >{{ $filterLabel }}</a>
            @endforeach
        </nav>

        @if ($products->isEmpty())
            <div class="empty-state">
                <h2>No matching products</h2>
                <p>Try another search or category, or check again later.</p>
                <a href="{{ route('customer.home') }}">Clear search</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    @php
                        $stockLabel = $product->stock_quantity <= 5 ? 'Low stock' : 'Available';
                        $quantityInCart = (int) session('customer_cart.'.$product->id, 0);
                    @endphp

                    <article class="product-card">
                        <div class="product-image">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                            @else
                                <span aria-hidden="true">CampusBite</span>
                            @endif
                        </div>

                        <div class="product-card-content">
                            <div class="product-card-heading">
                                <h2>{{ $product->name }}</h2>
                                <span class="product-stock {{ $product->stock_quantity <= 5 ? 'stock-low' : '' }}">
                                    {{ $stockLabel }}
                                </span>
                            </div>

                            <div class="product-meta">
                                <strong>₱{{ number_format((float) $product->price, 2) }}</strong>
                                <span>{{ $product->stock_quantity }} in stock</span>
                            </div>

                            <form
                                method="POST"
                                action="{{ route('customer.cart.add', $product) }}"
                            >
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button class="select-product-button {{ $quantityInCart >= $product->stock_quantity ? 'is-disabled' : '' }}" type="submit" aria-label="Add {{ $product->name }} to your order" @disabled($quantityInCart >= $product->stock_quantity)>
                                    {{ $quantityInCart >= $product->stock_quantity ? 'Stock limit' : 'Add item' }} <span aria-hidden="true">+</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <aside class="sale-preview cart-panel" aria-labelledby="cart-heading">
        <div class="preview-heading">
            <div>
                <h2 id="cart-heading">Current Order</h2>
            </div>
        </div>

        @if ($cartLines->isNotEmpty())
            <div class="cart-lines">
                @foreach ($cartLines as $line)
                    <div class="selected-product cart-line">
                        <div class="cart-line-thumb" aria-hidden="true">
                            @if ($line['product']->image)
                                <img src="{{ asset('storage/'.$line['product']->image) }}" alt="">
                            @endif
                        </div>
                        <div class="cart-line-content">
                            <strong>{{ $line['product']->name }}</strong>
                            <p>{{ $line['product']->stock_quantity }} available</p>
                            <form class="cart-quantity" method="POST" action="{{ route('customer.cart.update', $line['product']) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" name="quantity" value="{{ $line['quantity'] - 1 }}" aria-label="Decrease {{ $line['product']->name }} quantity">−</button>
                                <span aria-label="Quantity for {{ $line['product']->name }}">{{ $line['quantity'] }}</span>
                                <button class="{{ $line['quantity'] >= $line['product']->stock_quantity ? 'is-disabled' : '' }}" type="submit" name="quantity" value="{{ $line['quantity'] + 1 }}" aria-label="Increase {{ $line['product']->name }} quantity" @disabled($line['quantity'] >= $line['product']->stock_quantity)>+</button>
                                <button class="cart-remove" type="submit" name="quantity" value="0" aria-label="Remove {{ $line['product']->name }} from your order">Remove</button>
                            </form>
                        </div>
                        <strong class="cart-line-price">₱{{ number_format((float) $line['line_total'], 2) }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="cart-summary">
                <div><span>Items</span><strong>{{ $cartLines->sum('quantity') }}</strong></div>
                <div><span>Total</span><strong>₱{{ number_format((float) $cartTotal, 2) }}</strong></div>
            </div>
        @else
            <p class="preview-empty">Add an item from the menu to start an order.</p>
        @endif

        @if ($cartLines->isNotEmpty())
            <form class="cart-checkout" method="POST" action="{{ route('customer.checkout') }}">
                @csrf

                <label for="payment_method">Payment method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                </select>

                <div id="cash-fields">
                    <label for="amount_tendered">Cash amount</label>
                    <input
                        id="amount_tendered"
                        name="amount_tendered"
                        type="number"
                        min="{{ number_format((float) $cartTotal, 2, '.', '') }}"
                        step="0.01"
                    >
                    <p class="customer-change-preview">
                        Change: ₱<span id="change-preview">0.00</span>
                    </p>
                </div>

                <div id="gcash-fields" hidden>
                    <label for="payment_reference">GCash reference number</label>
                    <input
                        id="payment_reference"
                        name="payment_reference"
                        type="text"
                        maxlength="100"
                    >
                    <p class="customer-payment-note">Staff will verify the GCash payment.</p>
                </div>

                <button class="cart-action" type="submit">Place order</button>
            </form>
        @else
            <span class="cart-action is-disabled" aria-disabled="true">Place order</span>
        @endif
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const method = document.getElementById('payment_method');
    const cashFields = document.getElementById('cash-fields');
    const gcashFields = document.getElementById('gcash-fields');
    const cashInput = document.getElementById('amount_tendered');
    const referenceInput = document.getElementById('payment_reference');
    const changePreview = document.getElementById('change-preview');
    const total = Number(@json((float) $cartTotal));

    if (!method) {
        return;
    }

    function updatePaymentFields() {
        const isCash = method.value === 'cash';

        cashFields.hidden = !isCash;
        gcashFields.hidden = isCash;
        cashInput.required = isCash;
        referenceInput.required = !isCash;
    }

    function updateChange() {
        const cash = Number(cashInput.value || 0);
        changePreview.textContent = Math.max(cash - total, 0).toFixed(2);
    }

    method.addEventListener('change', updatePaymentFields);
    cashInput.addEventListener('input', updateChange);

    updatePaymentFields();
});
</script>
@endpush
