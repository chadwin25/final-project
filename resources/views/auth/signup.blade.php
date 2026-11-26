<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer sign up · CampusBite</title>
    <link rel="stylesheet" href="{{ asset('css/campusbite.css') }}">
</head>
<body>
<main style="max-width:520px;margin:50px auto;padding:24px;">
    <h1>Create customer account</h1>
    <p>Mag-sign up para makapag-order online.</p>

    <form method="POST" action="{{ route('customer.signup.submit') }}">
        @csrf

        <label for="full_name">Full name</label>
        <input id="full_name" name="full_name" value="{{ old('full_name') }}" required maxlength="100">
        @error('full_name') <p role="alert">{{ $message }}</p> @enderror

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="100">
        @error('email') <p role="alert">{{ $message }}</p> @enderror

        <label for="phone">Phone (optional)</label>
        <input id="phone" name="phone" value="{{ old('phone') }}" maxlength="20">
        @error('phone') <p role="alert">{{ $message }}</p> @enderror

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required minlength="8">
        @error('password') <p role="alert">{{ $message }}</p> @enderror

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">

        <button type="submit">Create account</button>
    </form>

    <p>May account ka na? <a href="{{ route('customer.login') }}">Log in</a></p>
</main>
</body>
</html>