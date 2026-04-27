@extends('layouts.admin')

@section('title', 'Admin Dashboard | '.config('app.name', 'playptl'))
@section('meta_description', 'Admin dashboard for managing users, organisers, players, and platform settings.')

@section('content')
    <section class="card">
        <h1>Admin Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}. You are logged in with the Admin role.</p>
    </section>
@endsection
