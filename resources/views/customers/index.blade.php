@extends('layouts.pos')

@section('title', 'Customers · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customers.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/confirm-customer-delete.js') }}" defer></script>
@endpush

@section('content')
<div class="customers-heading">
    <div>
        <h1>Customers</h1>
        <p>Keep contact details ready for customer orders.</p>
    </div>
    <a class="primary-button customers-primary" href="{{ route('customers.create') }}">Add customer</a>
</div>

@if (session('status'))
    <div class="customers-success" role="status">{{ session('status') }}</div>
@endif

<form class="customers-search" method="GET" action="{{ route('customers.index') }}" role="search">
    <label for="customer-search">Search customers</label>
    <div>
        <input
            id="customer-search"
            name="search"
            type="search"
            value="{{ $search }}"
            maxlength="100"
            placeholder="Search name or email"
        >
        <button type="submit">Search</button>
    </div>
</form>

@if ($customers->isEmpty())
    <div class="customers-empty">
        <h2>No customers found</h2>
        <p>{{ $search !== '' ? 'Try another search term.' : 'Add a customer when you need to keep their contact details.' }}</p>
    </div>
@else
    <ul class="customers-list">
        @foreach ($customers as $customer)
            <li class="customer-row">
                <span class="customer-initial" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($customer->full_name, 0, 1)) }}
                </span>

                <div class="customer-identity">
                    <strong>{{ $customer->full_name }}</strong>
                    <small>Customer #{{ $customer->id }}</small>
                </div>

                <div class="customer-detail">
                    <span>Email</span>
                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                </div>

                <div class="customer-detail">
                    <span>Phone</span>
                    <strong>{{ $customer->phone ?: 'Not provided' }}</strong>
                </div>

                <div class="customer-actions">
                    <a href="{{ route('customers.edit', $customer) }}">Edit</a>
                    <form
                        method="POST"
                        action="{{ route('customers.destroy', $customer) }}"
                        data-confirm-message="Delete this customer? Past orders will remain, but they will no longer show this customer."
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>

    @if ($customers->hasPages())
        <nav class="customers-pagination" aria-label="Customer pages">
            @if ($customers->onFirstPage())
                <span>Previous</span>
            @else
                <a href="{{ $customers->previousPageUrl() }}">Previous</a>
            @endif

            <span>Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}</span>

            @if ($customers->hasMorePages())
                <a href="{{ $customers->nextPageUrl() }}">Next</a>
            @else
                <span>Next</span>
            @endif
        </nav>
    @endif
@endif
@endsection
