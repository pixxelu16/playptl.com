<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Player Dashboard')</title>
    <meta name="description" content="@yield('meta_description', 'Player dashboard for account activity and updates.')">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }

        .sidebar {
            padding: 28px;
            background: #1d4ed8;
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
            color: #dbeafe;
            font: inherit;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .sidebar a:hover,
        .logout:hover {
            background: #2563eb;
            color: #ffffff;
        }

        .content {
            padding: 36px;
        }

        .card {
            border-radius: 16px;
            padding: 28px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Player Panel</div>
            <a href="{{ route('player.dashboard') }}">Dashboard</a>
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
