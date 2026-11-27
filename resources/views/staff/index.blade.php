@extends('layouts.pos')

@section('title', 'Staff · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endpush

@section('content')
<div class="staff-heading">
    <div>
        <h1>Staff</h1>
        <p>Manage the accounts that can access the food counter.</p>
    </div>
    <a class="primary-button staff-primary" href="{{ route('staff.create') }}">Add staff</a>
</div>

@if (session('status'))
    <div class="staff-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->has('staff'))
    <div class="staff-error" role="alert">{{ $errors->first('staff') }}</div>
@endif

<nav class="staff-tabs" aria-label="Staff status">
    <a
        href="{{ route('staff.index') }}"
        class="{{ $showArchived ? '' : 'is-active' }}"
        @if (! $showArchived) aria-current="page" @endif
    >Active staff</a>
    <a
        href="{{ route('staff.index', ['view' => 'archived']) }}"
        class="{{ $showArchived ? 'is-active' : '' }}"
        @if ($showArchived) aria-current="page" @endif
    >Archived staff</a>
</nav>

<form class="staff-search" method="GET" action="{{ route('staff.index') }}" role="search">
    @if ($showArchived)
        <input type="hidden" name="view" value="archived">
    @endif
    <label for="staff-search">Search staff</label>
    <div>
        <input
            id="staff-search"
            name="search"
            type="search"
            value="{{ $search }}"
            maxlength="100"
            placeholder="Search name or email"
        >
        <button type="submit">Search</button>
    </div>
</form>

@if ($staffMembers->isEmpty())
    <div class="staff-empty">
        <h2>No staff found</h2>
        <p>
            {{ $search !== ''
                ? 'Try another search term.'
                : ($showArchived ? 'There are no archived staff accounts.' : 'Add a staff member to create another sign-in.') }}
        </p>
    </div>
@else
    <ul class="staff-list">
        @foreach ($staffMembers as $member)
            <li class="staff-row">
                @if ($member->avatar)
                    <img
                        class="staff-photo"
                        src="{{ asset('storage/'.$member->avatar) }}"
                        alt=""
                        width="48"
                        height="48"
                    >
                @else
                    <span class="staff-initial" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}
                    </span>
                @endif

                <div class="staff-identity">
                    <strong>{{ $member->full_name }}</strong>
                    <small>{{ ucfirst($member->role) }} #{{ $member->id }}</small>
                </div>

                <div class="staff-username">
                    <span>Email</span>
                    <strong>{{ $member->email }}</strong>
                </div>

                <div class="staff-actions">
                    @if ($showArchived)
                        <form method="POST" action="{{ route('staff.restore', $member->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Restore</button>
                        </form>
                    @else
                        <a href="{{ route('staff.edit', $member) }}">Edit</a>
                        @if ($member->is(auth()->user()))
                            <span class="staff-self">Your account</span>
                        @else
                            <form method="POST" action="{{ route('staff.destroy', $member) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Archive</button>
                            </form>
                        @endif
                    @endif
                </div>
            </li>
        @endforeach
    </ul>

    @if ($staffMembers->hasPages())
        <nav class="staff-pagination" aria-label="Staff pages">
            @if ($staffMembers->onFirstPage())
                <span>Previous</span>
            @else
                <a href="{{ $staffMembers->previousPageUrl() }}">Previous</a>
            @endif

            <span>Page {{ $staffMembers->currentPage() }} of {{ $staffMembers->lastPage() }}</span>

            @if ($staffMembers->hasMorePages())
                <a href="{{ $staffMembers->nextPageUrl() }}">Next</a>
            @else
                <span>Next</span>
            @endif
        </nav>
    @endif
@endif
@endsection
