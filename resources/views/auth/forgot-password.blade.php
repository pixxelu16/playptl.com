@extends('layouts.auth')

@section('title', 'Forgot Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Request a secure password reset link for your '.config('app.name', 'playptl').' account.')

@section('content')
    <h1>Forgot password</h1>
    <p class="subtitle">Enter your email and we will send a password reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <button class="button" type="submit">Send reset link</button>
    </form>

    <div class="link-row">
        Remember password? <a href="{{ route('login') }}">Back to login</a>
    </div>
@endsection
