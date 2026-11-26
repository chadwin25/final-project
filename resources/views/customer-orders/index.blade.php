@extends('layouts.pos')

@section('title', 'Customer orders · CampusBite')

@section('content')
<div class="management-heading">
    <div>
        <h1>Customer orders</h1>
        <p>I-review ang customer orders at i-update ang status.</p>
    </div>
</div>

@if (session('status'))
    <div class="management-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="form-alert" role="alert">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@forelse ($orders as $order)
    <article class="management-empty" style="margin-bottom:18px">
        <h2>{{ $order->order_code }}</h2>
        <p>Customer: {{ $order->customer?->full_name ?? 'Customer unavailable' }}</p>
        <p>Placed: {{ $order->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</p>
        <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
        <p>Payment: {{ strtoupper($order->payment_method) }} — {{ ucfirst($order->payment_status) }}</p>

        @if ($order->payment_method === 'cash')
            <p>Cash tendered: ₱{{ number_format((float) $order->amount_tendered, 2) }}</p>
            <p>Change: ₱{{ number_format((float) $order->change_due, 2) }}</p>
        @elseif ($order->payment_reference)
            <p>GCash reference: <strong>{{ $order->payment_reference }}</strong></p>
        @endif

        <ul>
            @foreach ($order->items as $item)
                <li>{{ $item->product_name }} — {{ $item->quantity }} × ₱{{ number_format((float) $item->unit_price, 2) }}</li>
            @endforeach
        </ul>

        <p><strong>Total: ₱{{ number_format((float) $order->total_price, 2) }}</strong></p>

        @if (! in_array($order->status, ['completed', 'cancelled'], true))
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                @if ($order->payment_status !== 'paid')
                    <form method="POST" action="{{ route('customer-orders.update', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="verify_payment">
                        <button type="submit">
                            {{ $order->payment_method === 'gcash' ? 'Verify GCash payment' : 'Confirm cash received' }}
                        </button>
                    </form>
                @endif

                @if ($order->status === 'pending' && $order->payment_status === 'paid')
                    <form method="POST" action="{{ route('customer-orders.update', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="preparing">
                        <button type="submit">Start preparing</button>
                    </form>
                @endif

                @if ($order->status === 'preparing')
                    <form method="POST" action="{{ route('customer-orders.update', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="ready">
                        <button type="submit">Mark ready</button>
                    </form>
                @endif

                @if ($order->status === 'ready' && $order->payment_status === 'paid')
                    <form method="POST" action="{{ route('customer-orders.update', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="complete">
                        <button type="submit">Complete order</button>
                    </form>
                @endif

                @if ($order->payment_status !== 'paid')
                    <form method="POST" action="{{ route('customer-orders.update', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit">Cancel and return stock</button>
                    </form>
                @endif
            </div>
        @endif
    </article>
@empty
    <div class="management-empty">
        <h2>No customer orders yet</h2>
        <p>Kapag nag-place ng order ang customer, lalabas ito rito.</p>
    </div>
@endforelse

{{ $orders->links() }}
@endsection