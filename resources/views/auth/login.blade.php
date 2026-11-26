<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Staff log in · CampusBite</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/customer-auth.css') }}">
</head>
<body class="customer-auth">
    <header class="customer-auth-header">
        <a class="customer-auth-brand" href="{{ route('landing') }}">
            <img src="{{ asset('images/campusbite-mark.svg') }}" alt="">
            <span>CampusBite</span>
        </a>

        <a class="customer-auth-header-link" href="{{ route('customer.login') }}">
            Customer log in
        </a>
    </header>

    <main class="customer-auth-main">
        <section class="customer-auth-card" aria-labelledby="login-heading">
            <h1 id="login-heading">Staff log in</h1>
            <p class="customer-auth-intro">Sign in to manage the counter, menu, and orders.</p>

            @if ($errors->any())
                <div class="customer-auth-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="customer-auth-alert" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form class="customer-auth-form" method="POST" action="{{ route('login.submit') }}">
                @csrf

                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    autocomplete="username"
                    required
                    autofocus
                >

                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                >

                <button class="customer-auth-button" type="submit">Staff log in</button>
            </form>

            <p class="customer-auth-footer">
                Ordering as a customer?
                <a href="{{ route('customer.login') }}">Customer log in</a>
            </p>
        </section>
    </main>
</body>
</html>