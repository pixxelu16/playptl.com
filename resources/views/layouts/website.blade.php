<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'playptl'))</title>
    <meta name="description" content="@yield('meta_description', 'Official website for '.config('app.name', 'playptl').'.')">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 7%;
            background: #ffffff;
            box-shadow: 0 1px 8px rgba(15, 23, 42, 0.06);
        }

        .brand {
            font-size: 22px;
            font-weight: 800;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 10px 16px;
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
        }

        .button.secondary {
            background: #111827;
        }

        .site-main {
            min-height: calc(100vh - 76px);
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ url('/') }}">{{ config('app.name', 'playptl') }}</a>
        <nav class="nav">
            <a href="{{ url('/') }}">Home</a>
            @auth
                <a class="button" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a class="button secondary" href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>

    <main class="site-main">
        @yield('content')
    </main>
</body>
</html>
