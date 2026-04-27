@extends('layouts.auth')

@section('title', 'Register | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new '.config('app.name', 'playptl').' account as an admin, organiser, or player.')

@section('content')
    <h1>Create account</h1>
    <p class="subtitle">Register a new account to access the dashboard.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </div>

        <div class="field">
            <label for="role">Account role</label>
            <select id="role" name="role" required>
                <option value="player" @selected(old('role', 'player') === 'player')>Player</option>
                <option value="organiser" @selected(old('role') === 'organiser')>Organiser</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button class="button" type="submit">Register</button>
    </form>

    <div class="link-row">
        Already have an account? <a href="{{ route('login') }}">Login</a>
    </div>
@endsection
