<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'CampusBite POS')</title>
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff-avatar.css') }}">
    @stack('styles')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="CampusBite POS home">
            {{-- Replace this brand mark with your logo when provided. --}}
            <span class="brand-mark" aria-hidden="true">CB</span>
            <span class="brand-name">CampusBite <small>POS</small></span>
        </a>

        <nav class="sidebar-nav" aria-label="Main navigation">
            <a
                class="nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                href="{{ route('dashboard') }}"
                @if (request()->routeIs('dashboard')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/>
                    <path d="M9 21v-7h6v7"/>
                </svg>
                <span>Home</span>
            </a>
            <a
    class="nav-item {{ request()->routeIs('sales.create') ? 'is-active' : '' }}"
    href="{{ route('sales.create') }}"
    @if (request()->routeIs('sales.create')) aria-current="page" @endif
>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path d="M3 4h2l2.2 11h10.9l2-8H6"/>
        <circle cx="9" cy="19" r="1"/>
        <circle cx="17" cy="19" r="1"/>
        <path d="M12 9v5M9.5 11.5h5"/>
    </svg>
    <span>Record sale</span>
</a>

            <a
                class="nav-item {{ request()->routeIs('products.*') ? 'is-active' : '' }}"
                href="{{ route('products.index') }}"
                @if (request()->routeIs('products.*')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 5h16M4 12h16M4 19h16"/>
                    <path d="M7 5v14"/>
                </svg>
                <span>Products</span>
            </a>

            <a
                class="nav-item {{ request()->routeIs('customers.*') ? 'is-active' : '' }}"
                href="{{ route('customers.index') }}"
                @if (request()->routeIs('customers.*')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M17 14a6 6 0 0 1 4 6"/>
                </svg>
                <span>Customers</span>
            </a>

            <a
                class="nav-item {{ request()->routeIs('staff.*') ? 'is-active' : '' }}"
                href="{{ route('staff.index') }}"
                @if (request()->routeIs('staff.*')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M8 5V3M16 5V3M3 10h18"/>
                </svg>
                <span>Staff</span>
            </a>

            <a
                class="nav-item {{ request()->routeIs('sales.history') ? 'is-active' : '' }}"
                href="{{ route('sales.history') }}"
                @if (request()->routeIs('sales.history')) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/>
                    <path d="M8 8h8M8 12h8"/>
                </svg>
                <span>Sales history</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <p class="sidebar-note">Built for the school food counter.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="signout-button" type="submit">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="app-workspace">
        <header class="topbar">
            <div>
                <p class="topbar-name">CampusBite POS</p>
                <p class="topbar-date">{{ now('Asia/Manila')->format('F j, Y') }}</p>
            </div>
            <div class="cashier">
                @if (auth()->user()->avatar)
                    <img
                        class="cashier-avatar"
                        src="{{ asset('storage/'.auth()->user()->avatar) }}"
                        alt=""
                        width="36"
                        height="36"
                    >
                @else
                    <span class="cashier-avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr(auth()->user()->full_name, 0, 1)) }}
                    </span>
                @endif
                <span>
                    <strong>{{ auth()->user()->full_name }}</strong>
                    <small>Signed-in staff</small>
                </span>
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