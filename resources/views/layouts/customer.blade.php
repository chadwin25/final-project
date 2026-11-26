<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'CampusBite')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pos-refresh.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-portal.css') }}">
    @stack('styles')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('customer.home') }}" aria-label="CampusBite home">
            <img class="brand-mark" src="{{ asset('images/campusbite-mark.svg') }}" alt="">
            <span class="brand-name">CampusBite</span>
        </a>

        <nav class="sidebar-nav" aria-label="Customer navigation">
            <a
                class="nav-item {{ request()->routeIs('customer.home') ? 'is-active' : '' }}"
                href="{{ route('customer.home') }}"
                @if (request()->routeIs('customer.home')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M3 4h18v16H3z"/>
                    <path d="M7 8h10M7 12h10M7 16h6"/>
                </svg>
                <span>Menu</span>
            </a>

            <a
                class="nav-item {{ request()->routeIs('customer.orders') ? 'is-active' : '' }}"
                href="{{ route('customer.orders') }}"
                @if (request()->routeIs('customer.orders')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/>
                    <path d="M8 8h8M8 12h8"/>
                </svg>
                <span>My orders</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button class="signout-button" type="submit">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="app-workspace">
        <header class="topbar">
            <div>
                <p class="topbar-date">{{ now('Asia/Manila')->format('l, F j, Y') }}</p>
            </div>

            <div class="topbar-actions">
                <div class="cashier">
                    <span class="cashier-avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr(auth('customer')->user()->full_name, 0, 1)) }}
                    </span>
                    <span>
                        <strong>{{ auth('customer')->user()->full_name }}</strong>
                        <small>Customer account</small>
                    </span>
                </div>
            </div>
        </header>

        <main id="main-content">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>