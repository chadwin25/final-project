@extends('layouts.pos')

@section('title', ($product->exists ? 'Edit product' : 'Add product').' · CampusBite POS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/product-image-preview.js') }}" defer></script>
@endpush

@section('content')
<div class="management-heading">
    <div>
        <a class="back-link" href="{{ route('products.index') }}">← Back to products</a>
        <h1>{{ $product->exists ? 'Edit product' : 'Add product' }}</h1>
        <p>{{ $product->exists ? 'Update the item shown to staff.' : 'Create an item for the food counter.' }}</p>
    </div>
</div>

@if ($errors->any())
    <div class="management-error" role="alert">
        Please correct the highlighted fields and try again.
    </div>
@endif

<form
    class="product-form"
    method="POST"
    action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
    enctype="multipart/form-data"
>
    @csrf
    @if ($product->exists)
        @method('PUT')
    @endif

    <div class="product-fields">
        <div class="field">
            <label for="name">Product name</label>
            <input
                id="name"
                name="name"
                type="text"
                maxlength="100"
                value="{{ old('name', $product->name) }}"
                required
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
            >
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="product-field-pair">
            <div class="field">
                <label for="price">Price (₱)</label>
                <input
                    id="price"
                    name="price"
                    type="number"
                    min="0.01"
                    max="99999999.99"
                    step="0.01"
                    value="{{ old('price', $product->price) }}"
                    required
                    aria-invalid="{{ $errors->has('price') ? 'true' : 'false' }}"
                >
                @error('price')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="stock_quantity">Stock quantity</label>
                <input
                    id="stock_quantity"
                    name="stock_quantity"
                    type="number"
                    min="0"
                    max="1000000"
                    step="1"
                    value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                    required
                    aria-invalid="{{ $errors->has('stock_quantity') ? 'true' : 'false' }}"
                >
                @error('stock_quantity')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="field">
            <label for="image">Product image</label>
            <input
                id="image"
                name="image"
                type="file"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}"
            >
            <p class="field-help">JPG, PNG, or WebP. Maximum 2 MB and 2400 × 2400 pixels.</p>
            @error('image')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="product-image-panel">
        <h2>Image preview</h2>
        <div class="form-image-frame">
            <img
                id="image-preview"
                src="{{ $product->image ? asset('storage/'.$product->image) : '' }}"
                alt="{{ $product->image ? $product->name : 'Selected product image preview' }}"
                @if (! $product->image) hidden @endif
            >
            <span id="image-placeholder" @if ($product->image) hidden @endif>
                {{-- Product image appears here after you choose an upload. --}}
                No image selected
            </span>
        </div>
        <p>The saved image will also appear on the home screen.</p>
    </div>

    <div class="product-form-actions">
        <button class="primary-button" type="submit">
            {{ $product->exists ? 'Save changes' : 'Add product' }}
        </button>
        <a href="{{ route('products.index') }}">Cancel</a>
    </div>
</form>
@endsection