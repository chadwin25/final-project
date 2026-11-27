<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Customer sign up · CampusBite</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/customer-auth.css') }}">
</head>
<body class="customer-auth">
    <header class="customer-auth-header">
        <a class="customer-auth-brand" href="{{ route('landing') }}">
            <img src="{{ asset('images/campusbite-mark.svg') }}" alt="">
            <span>CampusBite</span>
        </a>
        <a class="customer-auth-header-link" href="{{ route('customer.login') }}">Log in</a>
    </header>

    <main class="customer-auth-main">
        <section class="customer-auth-card" aria-labelledby="signup-heading">
            <h1 id="signup-heading">Create your account</h1>
            <p class="customer-auth-intro">Create a customer account to browse the menu and place orders. Staff accounts are created by the admin.</p>

            @if ($errors->any())
                <div class="customer-auth-alert" role="alert">
                    Please check the form and correct the highlighted fields.
                </div>
            @endif

            <form class="customer-auth-form" method="POST" action="{{ route('customer.signup.submit') }}">
                @csrf

                <label for="full_name">Full name</label>
                <input
                    id="full_name"
                    name="full_name"
                    type="text"
                    value="{{ old('full_name') }}"
                    autocomplete="name"
                    maxlength="100"
                    required
                >
                @error('full_name')
                    <p class="customer-auth-error">{{ $message }}</p>
                @enderror

                <label for="email">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    maxlength="100"
                    required
                >
                @error('email')
                    <p class="customer-auth-error">{{ $message }}</p>
                @enderror

                <label for="phone">Phone number <span>(optional)</span></label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    value="{{ old('phone') }}"
                    autocomplete="tel"
                    maxlength="20"
                >
                @error('phone')
                    <p class="customer-auth-error">{{ $message }}</p>
                @enderror

                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                @error('password')
                    <p class="customer-auth-error">{{ $message }}</p>
                @enderror

                <label for="password_confirmation">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >

                <button class="customer-auth-button" type="submit">Create account</button>
            </form>

            <p class="customer-auth-footer">
                Already have an account?
                <a href="{{ route('customer.login') }}">Log in</a>
            </p>
        </section>
    </main>
</body>
</html>
