@extends('layouts.auth')

@section('title', 'Reset Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Set a new secure password for your '.config('app.name', 'playptl').' account.')

@section('content')
    <h1>Reset password</h1>
    <p class="subtitle">Choose a new password for your account.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button class="button" type="submit">Reset password</button>
    </form>
@endsection
