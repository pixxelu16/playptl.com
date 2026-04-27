@extends('layouts.player')

@section('title', 'Player Dashboard | '.config('app.name', 'playptl'))
@section('meta_description', 'Player dashboard for viewing account details, activity, and platform updates.')

@section('content')
    <section class="card">
        <h1>Player Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}. You are logged in with the Player role.</p>
    </section>
@endsection
