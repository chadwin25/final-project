@extends('layouts.customer')

@section('title', 'Menu · CampusBite')

@section('content')
<div class="customer-dashboard">
    <section class="customer-menu-area" aria-labelledby="menu-heading">
        <div class="section-heading customer-heading">
            <div>
                <h1 id="menu-heading">Today’s menu</h1>
                <p>Browse the menu and add items to your order.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="customer-notice customer-notice-success" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="customer-notice customer-notice-error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="empty-state">
                <h2>No items available</h2>
                <p>Please check again later.</p>
            </div>
        @else
            <div class="customer-product-grid">
                @foreach ($products as $product)
                    @php
                        $stockLabel = $product->stock_quantity <= 5 ? 'Low stock' : 'Available';
                    @endphp

                    <article class="customer-product-card">
                        <div class="customer-product-image">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                            @else
                                <span aria-hidden="true">CampusBite</span>
                            @endif
                        </div>

                        <div class="customer-product-content">
                            <div class="customer-product-heading">
                                <h2>{{ $product->name }}</h2>
                                <span class="customer-stock {{ $product->stock_quantity <= 5 ? 'is-low' : '' }}">
                                    {{ $stockLabel }}
                                </span>
                            </div>

                            <div class="customer-product-meta">
                                <strong>₱{{ number_format((float) $product->price, 2) }}</strong>
                                <span>{{ $product->stock_quantity }} in stock</span>
                            </div>

                            <form
                                class="customer-add-form"
                                method="POST"
                                action="{{ route('customer.cart.add', $product) }}"
                            >
                                @csrf
                                <label for="quantity-{{ $product->id }}">Quantity</label>
                                <div class="customer-add-controls">
                                    <input
                                        id="quantity-{{ $product->id }}"
                                        name="quantity"
                                        type="number"
                                        min="1"
                                        max="{{ $product->stock_quantity }}"
                                        value="1"
                                        required
                                    >
                                    <button type="submit">Add to order</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <aside class="customer-cart-panel" aria-labelledby="cart-heading">
        <div class="customer-cart-heading">
            <div>
                <h2 id="cart-heading">Your order</h2>
                <p>Review your items before checkout.</p>
            </div>
        </div>

        @forelse ($cartLines as $line)
            <div class="customer-cart-line">
                <div class="customer-cart-item-info">
                    <strong>{{ $line['product']->name }}</strong>
                    <span>
                        {{ $line['quantity'] }} × ₱{{ number_format((float) $line['product']->price, 2) }}
                    </span>
                </div>

                <div class="customer-cart-item-actions">
                    <strong>₱{{ number_format((float) $line['line_total'], 2) }}</strong>

                    <form
                        method="POST"
                        action="{{ route('customer.cart.update', $line['product']) }}"
                    >
                        @csrf
                        @method('PUT')
                        <input
                            name="quantity"
                            type="number"
                            min="0"
                            max="{{ $line['product']->stock_quantity }}"
                            value="{{ $line['quantity'] }}"
                            aria-label="Quantity for {{ $line['product']->name }}"
                        >
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="customer-cart-empty">Your cart is empty.</p>
        @endforelse

        <div class="customer-cart-total">
            <span>Total</span>
            <strong>₱{{ number_format((float) $cartTotal, 2) }}</strong>
        </div>

        @if ($cartLines->isNotEmpty())
            <form class="customer-checkout-form" method="POST" action="{{ route('customer.checkout') }}">
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

                <button class="customer-place-order" type="submit">Place order</button>
            </form>
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