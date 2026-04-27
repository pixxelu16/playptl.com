@extends('layouts.organiser')

@section('title', 'Organiser Dashboard | '.config('app.name', 'playptl'))
@section('meta_description', 'Organiser dashboard for managing events, matches, and player participation.')

@section('content')
    <section class="card">
        <h1>Organiser Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}. You are logged in with the Organiser role.</p>
    </section>
@endsection
