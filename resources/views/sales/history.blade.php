@extends('layouts.pos')

@section('title', 'Orders · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sales-history.css') }}">
@endpush

@section('content')
<div class="management-heading">
    <div>
        <h1>Orders</h1>
        <p>Review completed orders, newest first.</p>
    </div>
</div>

@if ($sales->isEmpty())
    <div class="management-empty">
        <h2>No orders yet</h2>
        <p>Completed orders will appear here.</p>
        <a href="{{ route('sales.create') }}">Open Quick Order</a>
    </div>
@else
    <div class="sales-history-table-wrap">
        <table class="sales-history-table">
            <thead>
                <tr>
                    <th scope="col">Date and time</th>
                    <th scope="col">Product</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Staff member</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    <tr>
                        <td data-label="Date and time">
                            @php($saleDate = $sale->created_at->copy()->timezone('Asia/Manila'))
                            <time datetime="{{ $saleDate->toIso8601String() }}">
                                {{ $saleDate->format('M j, Y · g:i A') }}
                            </time>
                        </td>
                        <td data-label="Product">
                            <strong>{{ $sale->product?->name ?? 'Product unavailable' }}</strong>
                            @if ($sale->product?->trashed())
                                <span class="sales-history-note">Archived</span>
                            @endif
                        </td>
                        <td data-label="Customer">{{ $sale->customer?->full_name ?? 'No customer linked' }}</td>
                        <td data-label="Staff member">
                            <strong>{{ $sale->staff?->full_name ?? 'Staff unavailable' }}</strong>
                            @if ($sale->staff?->trashed())
                                <span class="sales-history-note">Archived</span>
                            @endif
                        </td>
                        <td data-label="Quantity">{{ $sale->quantity }}</td>
                        <td class="sales-history-total" data-label="Total">₱{{ number_format((float) $sale->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($sales->hasPages())
        <nav class="management-pagination" aria-label="Order history pages">
            @if ($sales->onFirstPage())
                <span>Previous</span>
            @else
                <a href="{{ $sales->previousPageUrl() }}">Previous</a>
            @endif

            <span>Page {{ $sales->currentPage() }} of {{ $sales->lastPage() }}</span>

            @if ($sales->hasMorePages())
                <a href="{{ $sales->nextPageUrl() }}">Next</a>
            @else
                <span>Next</span>
            @endif
        </nav>
    @endif
@endif
@endsection
