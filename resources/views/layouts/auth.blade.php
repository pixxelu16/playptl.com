<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'playptl'))</title>
    <meta name="description" content="@yield('meta_description', 'Access your '.config('app.name', 'playptl').' account securely.')">
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
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 460px;
            padding: 32px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .wide-card {
            max-width: 720px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 32px;
        }

        .subtitle {
            margin: 0 0 24px;
            color: #6b7280;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 16px;
            background: #ffffff;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: #4b5563;
        }

        .button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 10px;
            padding: 12px 16px;
            background: #2563eb;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .button:hover {
            background: #1d4ed8;
            text-decoration: none;
        }

        .link-row {
            margin-top: 18px;
            text-align: center;
            color: #6b7280;
        }

        .errors,
        .status {
            margin-bottom: 18px;
            border-radius: 10px;
            padding: 12px 14px;
            line-height: 1.5;
        }

        .errors {
            background: #fef2f2;
            color: #991b1b;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        .status {
            background: #ecfdf5;
            color: #047857;
        }

        .nav {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="card @yield('card_class')">
            @if ($errors->any())
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </section>
    </main>
</body>
</html>
