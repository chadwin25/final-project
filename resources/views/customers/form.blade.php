@extends('layouts.pos')

@section('title', ($customer->exists ? 'Edit customer' : 'Add customer').' · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customers.css') }}">
@endpush

@section('content')
<div class="customers-heading">
    <div>
        <a class="customers-back" href="{{ route('customers.index') }}">← Back to customers</a>
        <h1>{{ $customer->exists ? 'Edit customer' : 'Add customer' }}</h1>
        <p>{{ $customer->exists ? 'Update this customer’s contact details.' : 'Add a customer for future orders.' }}</p>
    </div>
</div>

@if ($errors->any())
    <div class="customers-error" role="alert">
        Please correct the highlighted fields and try again.
    </div>
@endif

<form
    class="customer-form"
    method="POST"
    action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}"
>
    @csrf
    @if ($customer->exists)
        @method('PUT')
    @endif

    <div class="customer-field">
        <label for="full_name">Full name</label>
        <input
            id="full_name"
            name="full_name"
            type="text"
            maxlength="100"
            value="{{ old('full_name', $customer->full_name) }}"
            autocomplete="name"
            required
            aria-invalid="{{ $errors->has('full_name') ? 'true' : 'false' }}"
        >
        @error('full_name')
            <p class="customer-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="customer-field">
        <label for="email">Email address</label>
        <input
            id="email"
            name="email"
            type="email"
            maxlength="100"
            value="{{ old('email', $customer->email) }}"
            autocomplete="email"
            required
            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
        >
        @error('email')
            <p class="customer-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="customer-field">
        <label for="phone">Phone number <span>(optional)</span></label>
        <input
            id="phone"
            name="phone"
            type="tel"
            maxlength="20"
            value="{{ old('phone', $customer->phone) }}"
            autocomplete="tel"
        >
        @error('phone')
            <p class="customer-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="customer-form-actions">
        <button class="primary-button" type="submit">
            {{ $customer->exists ? 'Save changes' : 'Add customer' }}
        </button>
        <a href="{{ route('customers.index') }}">Cancel</a>
    </div>
</form>
@endsection
