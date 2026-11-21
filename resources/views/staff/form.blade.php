@extends('layouts.pos')

@section('title', ($staff->exists ? 'Edit staff' : 'Add staff').' · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/staff-avatar-preview.js') }}" defer></script>
@endpush

@section('content')
<div class="staff-heading">
    <div>
        <a class="staff-back" href="{{ route('staff.index') }}">← Back to staff</a>
        <h1>{{ $staff->exists ? 'Edit staff' : 'Add staff' }}</h1>
        <p>{{ $staff->exists ? 'Update this staff account.' : 'Create a new account for food counter access.' }}</p>
    </div>
</div>

@if ($errors->any())
    <div class="staff-error" role="alert">
        Please correct the highlighted fields and try again.
    </div>
@endif

<form
    class="staff-form"
    method="POST"
    enctype="multipart/form-data"
    action="{{ $staff->exists ? route('staff.update', $staff) : route('staff.store') }}"
>
    @csrf
    @if ($staff->exists)
        @method('PUT')
    @endif

    <div class="staff-field">
        <label for="full_name">Full name</label>
        <input
            id="full_name"
            name="full_name"
            type="text"
            maxlength="100"
            value="{{ old('full_name', $staff->full_name) }}"
            autocomplete="name"
            required
            aria-invalid="{{ $errors->has('full_name') ? 'true' : 'false' }}"
        >
        @error('full_name')
            <p class="staff-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="staff-field">
        <label for="username">Username</label>
        <input
            id="username"
            name="username"
            type="text"
            maxlength="50"
            value="{{ old('username', $staff->username) }}"
            autocomplete="username"
            required
            aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
        >
        <p class="staff-help">Letters, numbers, dashes, and underscores only.</p>
        @error('username')
            <p class="staff-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="staff-field">
        <label for="password">
            Password
            @if ($staff->exists)<span>(leave blank to keep the current password)</span>@endif
        </label>
        <input
            id="password"
            name="password"
            type="password"
            minlength="12"
            autocomplete="new-password"
            @if (! $staff->exists) required @endif
            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
        >
        <p class="staff-help">Use at least 12 characters.</p>
        @error('password')
            <p class="staff-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="staff-field">
        <label for="password_confirmation">Confirm password</label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            minlength="12"
            autocomplete="new-password"
            @if (! $staff->exists) required @endif
        >
    </div>

    <div class="staff-field">
        <label for="avatar">Avatar <span>(optional)</span></label>
        <input
            id="avatar"
            name="avatar"
            type="file"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            aria-invalid="{{ $errors->has('avatar') ? 'true' : 'false' }}"
        >
        <p class="staff-help">JPG, PNG, or WebP; up to 2 MB. A new image replaces the current one.</p>
        @error('avatar')
            <p class="staff-field-error">{{ $message }}</p>
        @enderror

        <div class="staff-avatar-preview" id="staff-avatar-preview">
            @if ($staff->avatar)
                <img src="{{ asset('storage/'.$staff->avatar) }}" alt="Current avatar" width="88" height="88">
            @else
                <span>No avatar selected</span>
            @endif
        </div>
    </div>

    <div class="staff-form-actions">
        <button class="primary-button" type="submit">
            {{ $staff->exists ? 'Save changes' : 'Add staff' }}
        </button>
        <a href="{{ route('staff.index') }}">Cancel</a>
    </div>
</form>
@endsection
