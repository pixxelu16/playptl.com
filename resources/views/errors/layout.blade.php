<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Something went wrong')</title>
    <meta name="description" content="@yield('meta_description', 'An unexpected error occurred.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 20% 20%, rgba(76, 175, 80, 0.22), transparent 45%),
                radial-gradient(circle at 80% 10%, rgba(193, 216, 46, 0.18), transparent 40%),
                #0a0f18;
            color: #f3f6fb;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            width: min(100%, 680px);
            background: rgba(9, 14, 26, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.35);
            padding: 34px 30px;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        .error-code {
            display: inline-block;
            margin: 0;
            font-size: clamp(52px, 12vw, 88px);
            line-height: 1;
            font-weight: 800;
            letter-spacing: 1px;
            color: #c1e82c;
        }
        .error-title {
            margin: 14px 0 10px;
            font-size: clamp(26px, 5vw, 36px);
            line-height: 1.2;
            font-weight: 700;
            color: #ffffff;
        }
        .error-message {
            margin: 0 auto;
            max-width: 520px;
            font-size: 16px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.82);
        }
        .error-actions {
            margin-top: 26px;
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 11px 18px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: transform .18s ease, opacity .18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 132px;
        }
        .btn-primary {
            background: #4caf50;
            color: #fff;
        }
        .btn-secondary {
            background: #c1d72e;
            color: #1a1a1a;
        }
        .btn:hover {
            transform: translateY(-1px);
            opacity: .95;
        }
    </style>
</head>
<body>
    <main class="error-card" role="main" aria-live="polite">
        <p class="error-code">@yield('code', '500')</p>
        <h1 class="error-title">@yield('heading', 'Something went wrong')</h1>
        <p class="error-message">@yield('message', 'Please try again after some time.')</p>
        <div class="error-actions">
            <a class="btn btn-primary" href="{{ url('/') }}">Back to Home</a>
            <a class="btn btn-secondary" href="javascript:history.back()">Go Back</a>
        </div>
    </main>
</body>
</html>
