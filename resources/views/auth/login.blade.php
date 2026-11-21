<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Log in · CampusBite</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
</head>
<body class="login-page">
<main class="login-layout">
    <div class="login-header">
        <a class="login-brand" href="{{ route('landing') }}" aria-label="CampusBite home">
            <img class="brand-mark brand-mark-large" src="{{ asset('images/campusbite-mark.svg') }}" alt="">
            <span>CampusBite</span>
        </a>
        <a class="login-home-link" href="{{ route('landing') }}">← Back to home</a>
    </div>

    <section class="login-card" aria-labelledby="login-heading">
        <h1 id="login-heading">Log in</h1>
        <p class="login-intro">Use your staff account to open the counter.</p>

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

            <button class="primary-button" type="submit">Log in</button>
        </form>
    </section>

    <p class="login-footer">Authorized staff access only</p>
</main>
</body>
</html>
