@extends('layouts.auth')

@section('title', 'Login | '.config('app.name', 'playptl'))
@section('meta_description', 'Login to your '.config('app.name', 'playptl').' account and access your role-based dashboard.')

@section('content')
    <h1>Login</h1>
    <p class="subtitle">Welcome back. Login to continue.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>

        <label class="checkbox">
            <input type="checkbox" name="remember" value="1">
            <span>Remember me</span>
        </label>

        <button class="button" type="submit">Login</button>
    </form>

    <div class="link-row">
        <a href="{{ route('password.request') }}">Forgot password?</a>
    </div>

    <div class="link-row">
        Don't have an account? <a href="{{ route('register') }}">Create account</a>
    </div>
@endsection
