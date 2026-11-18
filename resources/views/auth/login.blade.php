<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Staff sign in · CampusBite POS</title>
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
</head>
<body class="login-page">
<main class="login-layout">
    <div class="login-brand">
        {{-- Replace this brand mark with your logo when provided. --}}
        <span class="brand-mark brand-mark-large" aria-hidden="true">CB</span>
        <span>CampusBite <strong>POS</strong></span>
    </div>

    <section class="login-card" aria-labelledby="login-heading">
        <h1 id="login-heading">Staff sign in</h1>
        <p class="login-intro">Enter your staff account to open the food counter.</p>

        @if ($errors->any())
            <div class="form-alert" role="alert">
                Please check your sign-in details and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="login-form">
            @csrf

            <div class="field">
                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    autocomplete="username"
                    maxlength="50"
                    required
                    autofocus
                    aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                >
                @error('username')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                >
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="primary-button" type="submit">Sign in</button>
        </form>
    </section>

    <p class="login-footer">CampusBite POS · Staff access only</p>
</main>
</body>
</html>