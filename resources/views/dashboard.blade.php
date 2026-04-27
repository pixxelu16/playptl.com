@extends('layouts.auth')

@section('title', 'Dashboard | '.config('app.name', 'playptl'))
@section('meta_description', 'Account dashboard for your '.config('app.name', 'playptl').' profile.')
@section('card_class', 'wide-card')

@section('content')
    <nav class="nav">
        <a href="{{ url('/') }}">Home</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="border: 0; background: transparent; color: #2563eb; cursor: pointer; font: inherit; padding: 0;">
                Logout
            </button>
        </form>
    </nav>

    <h1>Dashboard</h1>
    <p class="subtitle">
        You are logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}).
    </p>
@endsection
