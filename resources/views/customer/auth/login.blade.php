<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Login · CampusBite</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/customer-auth.css') }}">
</head>
<body class="customer-auth">
    <header class="customer-auth-header">
        <a class="customer-auth-brand" href="{{ route('landing') }}">
            <img src="{{ asset('images/campusbite-mark.svg') }}" alt="">
            <span>CampusBite</span>
        </a>

        <a class="customer-auth-header-link" href="{{ route('login') }}">
            Back to staff login
        </a>
    </header>

    <main class="customer-auth-main">
        <section class="customer-auth-card" aria-labelledby="login-heading">
            <h1 id="login-heading">Login</h1>
            <p class="customer-auth-intro">Sign in to browse the menu and manage your orders.</p>

            @if ($errors->any())
                <div class="customer-auth-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="customer-auth-form" method="POST" action="{{ route('customer.login.submit') }}">
                @csrf

                <label for="email">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
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

                <button class="customer-auth-button" type="submit">Login</button>
            </form>

            <p class="customer-auth-footer">
                Don’t have an account?
                <a href="{{ route('customer.signup') }}">Create one</a>
            </p>
        </section>
    </main>
</body>
</html>
