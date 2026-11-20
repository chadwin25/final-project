@extends('layouts.pos')

@section('title', 'Record Sale · CampusBite POS')

@section('content')
<div class="dashboard-grid">
    <section class="menu-area" aria-labelledby="sale-heading">
        <div class="section-heading">
            <div>
                <h1 id="sale-heading">Record sale</h1>
                <p>Choose one product and enter the quantity.</p>
            </div>
        </div>

        @if (session('status'))
            <p class="success-message" role="status">{{ session('status') }}</p>
        @endif

        @if ($products->isEmpty())
            <div class="empty-state">
                <h2>No products in stock</h2>
                <p>Add stock to a product before recording a sale.</p>
                <a href="{{ route('products.index') }}">Go to Products</a>
            </div>
        @else
            <form method="POST" action="{{ route('sales.store') }}" class="search-form">
                @csrf

                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Choose a product</option>
                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock_quantity }}"
                            @selected((string) old('product_id') === (string) $product->id)
                        >
                            {{ $product->name }} — ₱{{ number_format((float) $product->price, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                <label for="customer_id">Customer <span>(optional)</span></label>
                <select id="customer_id" name="customer_id">
                    <option value="">Walk-in customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>
                            {{ $customer->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                <label for="quantity">Quantity</label>
                <input
                    id="quantity"
                    name="quantity"
                    type="number"
                    min="1"
                    step="1"
                    value="{{ old('quantity', 1) }}"
                    required
                    disabled
                >
                <p id="stock-info">Choose a product to see available stock.</p>
                @error('quantity')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                <div class="counter-stats">
                    <div><span>Unit price</span><strong id="unit-price">₱0.00</strong></div>
                    <div><span>Total preview</span><strong id="total-price">₱0.00</strong></div>
                </div>

                <button id="submit-sale" type="submit" disabled>Record sale</button>
            </form>
        @endif
    </section>

    <aside class="sale-preview">
        <div class="preview-heading">
            <div>
                <h2>Sale summary</h2>
                <p>One product per sale</p>
            </div>
        </div>
        <div class="selected-product">
            <span>Selected product</span>
            <strong id="selected-product">None selected</strong>
            <p id="summary-stock">Available stock: —</p>
        </div>
        <p class="preview-message">
            The server will verify the price and stock again when the sale is submitted.
        </p>
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const product = document.getElementById('product_id');
    const quantity = document.getElementById('quantity');
    const stockInfo = document.getElementById('stock-info');
    const unitPrice = document.getElementById('unit-price');
    const totalPrice = document.getElementById('total-price');
    const selectedProduct = document.getElementById('selected-product');
    const summaryStock = document.getElementById('summary-stock');
    const submit = document.getElementById('submit-sale');

    if (!product || !quantity) return;

    const peso = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    });

    function updatePreview() {
        const option = product.selectedOptions[0];
        const hasProduct = Boolean(option && option.value);
        const price = hasProduct ? Number(option.dataset.price) : 0;
        const stock = hasProduct ? Number(option.dataset.stock) : 0;
        const count = Number(quantity.value);

        quantity.disabled = !hasProduct;
        quantity.max = hasProduct ? String(stock) : '';
        unitPrice.textContent = peso.format(price);
        totalPrice.textContent = peso.format(price * (Number.isFinite(count) ? count : 0));

        selectedProduct.textContent = hasProduct ? option.dataset.name : 'None selected';
        summaryStock.textContent = hasProduct ? `Available stock: ${stock}` : 'Available stock: —';
        stockInfo.textContent = hasProduct ? `${stock} available in stock.` : 'Choose a product to see available stock.';

        submit.disabled = !(
            hasProduct &&
            Number.isInteger(count) &&
            count > 0 &&
            count <= stock
        );
    }

    product.addEventListener('change', updatePreview);
    quantity.addEventListener('input', updatePreview);
    product.form.addEventListener('submit', () => {
        submit.disabled = true;
        submit.textContent = 'Recording...';
    });
    updatePreview();
});
</script>
@endpush