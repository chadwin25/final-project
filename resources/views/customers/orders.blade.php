<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My orders · CampusBite</title>
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
</head>
<body style="max-width:1000px;margin:auto;padding:24px">
    <nav>
        <a href="{{ route('customer.home') }}">Menu</a>
        <a href="{{ route('customer.orders') }}">My orders</a>
        <form method="POST" action="{{ route('customer.logout') }}" style="display:inline">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </nav>

    <h1>My orders</h1>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @forelse ($orders as $order)
        <article style="border:1px solid #ddd;border-radius:12px;padding:18px;margin:16px 0">
            <h2>{{ $order->order_code }}</h2>
            <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
            <p>Payment: {{ strtoupper($order->payment_method) }} · {{ ucfirst($order->payment_status) }}</p>

            @if ($order->payment_method === 'cash')
                <p>Cash: ₱{{ number_format((float) $order->amount_tendered, 2) }}</p>
                <p>Change: ₱{{ number_format((float) $order->change_due, 2) }}</p>
            @elseif ($order->payment_reference)
                <p>GCash reference: {{ $order->payment_reference }}</p>
            @endif

            <ul>
                @foreach ($order->items as $item)
                    <li>
                        {{ $item->product_name }} — {{ $item->quantity }} ×
                        ₱{{ number_format((float) $item->unit_price, 2) }}
                    </li>
                @endforeach
            </ul>

            <p><strong>Total: ₱{{ number_format((float) $order->total_price, 2) }}</strong></p>
        </article>
    @empty
        <p>Wala ka pang orders.</p>
    @endforelse

    {{ $orders->links() }}
</body>
</html>