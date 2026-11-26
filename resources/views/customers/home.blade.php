<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu · CampusBite</title>
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
    <style>
        body { max-width: 1200px; margin: auto; padding: 24px; }
        header, .columns, .product, .cart-line { display: flex; gap: 20px; }
        header { justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 16px; }
        .columns { align-items: flex-start; }
        .menu { flex: 2; }
        .cart { flex: 1; border: 1px solid #ddd; border-radius: 12px; padding: 20px; }
        .products { display: grid; grid-template-columns: repeat(auto-fit,minmax(230px,1fr)); gap: 16px; }
        .product { border: 1px solid #ddd; border-radius: 12px; padding: 12px; flex-direction: column; }
        .product img { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; }
        .cart-line { justify-content: space-between; align-items: center; padding: 8px 0; }
        label { display:block; margin-top:12px; }
        input, select, button { padding: 10px; margin-top: 6px; }
        .error { color: #a00; }
        @media (max-width: 760px) { .columns { display:block; } .cart { margin-top:20px; } }
    </style>
</head>
<body>
<header>
    <div>
        <strong>CampusBite</strong>
        <span>Hi, {{ auth('customer')->user()->full_name }}</span>
    </div>
    <nav>
        <a href="{{ route('customer.home') }}">Menu</a>
        <a href="{{ route('customer.orders') }}">My orders</a>
        <form method="POST" action="{{ route('customer.logout') }}" style="display:inline">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </nav>
</header>

@if (session('status'))
    <p role="status">{{ session('status') }}</p>
@endif

@if ($errors->any())
    <div class="error" role="alert">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="columns">
    <section class="menu">
        <h1>Menu</h1>

        @if ($products->isEmpty())
            <p>Walang available na produkto ngayon.</p>
        @else
            <div class="products">
                @foreach ($products as $product)
                    <article class="product">
                        @if ($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                        @endif
                        <h2>{{ $product->name }}</h2>
                        <p>₱{{ number_format((float) $product->price, 2) }}</p>
                        <p>{{ $product->stock_quantity }} available</p>

                        <form method="POST" action="{{ route('customer.cart.add', $product) }}">
                            @csrf
                            <label for="quantity-{{ $product->id }}">Quantity</label>
                            <input id="quantity-{{ $product->id }}" name="quantity" type="number" min="1" max="{{ $product->stock_quantity }}" value="1" required>
                            <button type="submit">Add to cart</button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <aside class="cart">
        <h2>Your cart</h2>

        @forelse ($cartLines as $line)
            <div class="cart-line">
                <div>
                    <strong>{{ $line['product']->name }}</strong>
                    <p>{{ $line['quantity'] }} × ₱{{ number_format((float) $line['product']->price, 2) }}</p>
                </div>
                <form method="POST" action="{{ route('customer.cart.update', $line['product']) }}">
                    @csrf
                    @method('PUT')
                    <input name="quantity" type="number" min="0" max="{{ $line['product']->stock_quantity }}" value="{{ $line['quantity'] }}" aria-label="Quantity">
                    <button type="submit">Update</button>
                </form>
            </div>
        @empty
            <p>Wala pang laman ang cart mo.</p>
        @endforelse

        <hr>
        <p><strong>Total: ₱{{ number_format((float) $cartTotal, 2) }}</strong></p>

        @if ($cartLines->isNotEmpty())
            <form method="POST" action="{{ route('customer.checkout') }}" id="checkout-form">
                @csrf

                <label for="payment_method">Mode of payment</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                </select>

                <div id="cash-fields">
                    <label for="amount_tendered">Cash amount</label>
                    <input id="amount_tendered" name="amount_tendered" type="number" min="{{ $cartTotal }}" step="0.01">
                    <p>Change: ₱<span id="change-preview">0.00</span></p>
                </div>

                <div id="gcash-fields" hidden>
                    <label for="payment_reference">GCash reference number</label>
                    <input id="payment_reference" name="payment_reference" maxlength="100">
                    <p>Manual na ive-verify ng staff ang GCash payment.</p>
                </div>

                <button type="submit">Place order</button>
            </form>
        @endif
    </aside>
</div>

<script>
const method = document.getElementById('payment_method');
const cashFields = document.getElementById('cash-fields');
const gcashFields = document.getElementById('gcash-fields');
const cashInput = document.getElementById('amount_tendered');
const referenceInput = document.getElementById('payment_reference');
const changePreview = document.getElementById('change-preview');
const orderTotal = Number(@json((float) $cartTotal));

function updatePaymentFields() {
    const isCash = method.value === 'cash';
    cashFields.hidden = !isCash;
    gcashFields.hidden = isCash;
    cashInput.required = isCash;
    referenceInput.required = !isCash;
}

function updateChange() {
    const amount = Number(cashInput.value || 0);
    changePreview.textContent = Math.max(amount - orderTotal, 0).toFixed(2);
}

method.addEventListener('change', updatePaymentFields);
cashInput.addEventListener('input', updateChange);
updatePaymentFields();
</script>
</body>
</html>