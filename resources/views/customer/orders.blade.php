@extends('layouts.customer')

@section('title', 'My orders · CampusBite')

@section('content')
<section class="customer-orders-page">
    <div class="section-heading customer-heading">
        <div>
            <h1>My orders</h1>
            <p>Review your order history and current order status.</p>
        </div>
        <a class="customer-back-to-menu" href="{{ route('customer.home') }}">Browse menu</a>
    </div>

    @if (session('status'))
        <div class="customer-notice customer-notice-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    @forelse ($orders as $order)
        <article class="customer-order-card">
            <div class="customer-order-header">
                <div>
                    <span class="customer-order-label">Order</span>
                    <h2>{{ $order->order_code }}</h2>
                    <time datetime="{{ $order->created_at->toIso8601String() }}">
                        {{ $order->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}
                    </time>
                </div>

                <span class="customer-order-status status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div class="customer-order-items">
                @foreach ($order->items as $item)
                    <div class="customer-order-item">
                        <span>
                            {{ $item->product_name }}
                            <small>{{ $item->quantity }} × ₱{{ number_format((float) $item->unit_price, 2) }}</small>
                        </span>
                        <strong>₱{{ number_format((float) $item->line_total, 2) }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="customer-order-payment">
                <span>
                    {{ strtoupper($order->payment_method) }} · {{ ucfirst($order->payment_status) }}
                </span>

                @if ($order->payment_method === 'cash')
                    <span>Cash: ₱{{ number_format((float) $order->amount_tendered, 2) }}</span>
                    <span>Change: ₱{{ number_format((float) $order->change_due, 2) }}</span>
                @elseif ($order->payment_reference)
                    <span>Reference: {{ $order->payment_reference }}</span>
                @endif

                <strong>Total: ₱{{ number_format((float) $order->total_price, 2) }}</strong>
            </div>
        </article>
    @empty
        <div class="empty-state">
            <h2>No orders yet</h2>
            <p>Your orders will appear here after checkout.</p>
            <a href="{{ route('customer.home') }}">Browse the menu</a>
        </div>
    @endforelse

    @if ($orders->hasPages())
        <nav class="management-pagination" aria-label="Order history pages">
            @if ($orders->onFirstPage())
                <span>Previous</span>
            @else
                <a href="{{ $orders->previousPageUrl() }}">Previous</a>
            @endif

            <span>Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>

            @if ($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}">Next</a>
            @else
                <span>Next</span>
            @endif
        </nav>
    @endif
</section>
@endsection