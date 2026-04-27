<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="description" content="@yield('meta_description', 'Admin dashboard for managing '.config('app.name', 'playptl').'.')">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #111827;
        }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }

        .sidebar {
            padding: 28px;
            background: #111827;
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
            color: #d1d5db;
            font: inherit;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .sidebar a:hover,
        .logout:hover {
            background: #1f2937;
            color: #ffffff;
        }

        .content {
            padding: 36px;
        }

        .card {
            border-radius: 16px;
            padding: 28px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Admin Panel</div>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
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
