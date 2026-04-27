@extends('layouts.website')

@section('title', 'Home | '.config('app.name', 'playptl'))
@section('meta_description', 'Welcome to '.config('app.name', 'playptl').', a role-based platform for admins, organisers, and players.')

@push('styles')
    <style>
        .page {
            min-height: calc(100vh - 76px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        .card {
            max-width: 640px;
            width: 100%;
            padding: 40px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 40px;
        }

        p {
            margin: 0;
            font-size: 18px;
            line-height: 1.6;
        }

        .links {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 24px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 12px 18px;
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
        }

        .button.secondary {
            background: #111827;
        }
    </style>
@endpush

@section('content')
    <main class="page">
        <section class="card">
            <h1>playptl Installed</h1>
            <p>This page is rendered from a normal Blade file: <strong>resources/views/home.blade.php</strong></p>
            <div class="links">
                @auth
                    <a class="button" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="button" href="{{ route('login') }}">Login</a>
                    <a class="button secondary" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </section>
    </main>
@endsection
