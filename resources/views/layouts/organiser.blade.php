<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>@yield('title', 'Organiser Dashboard')</title>
    <meta name="description" content="@yield('meta_description', 'Organiser dashboard for managing events and player activity.')">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0fdf4;
            color: #064e3b;
        }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }

        .sidebar {
            padding: 28px;
            background: #065f46;
            color: #ffffff;
        }

        .brand {
            margin-bottom: 28px;
            font-size: 24px;
            font-weight: 800;
        }

        .sidebar a,
        .logout {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            border: 0;
            border-radius: 10px;
            padding: 12px;
            background: transparent;
            color: #d1fae5;
            font: inherit;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .sidebar a:hover,
        .logout:hover {
            background: #047857;
            color: #ffffff;
        }

        .content {
            padding: 36px;
        }

        .card {
            border-radius: 16px;
            padding: 28px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(6, 78, 59, 0.08);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Organiser Panel</div>
            <a href="{{ route('organiser.dashboard') }}">Dashboard</a>
            <a href="{{ url('/') }}">Website</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Logout</button>
            </form>
        </aside>

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
