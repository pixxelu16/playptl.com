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

        .organiser-main {
            min-width: 0;
        }

        .organiser-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            background: #065f46;
            color: #fff;
        }

        .organiser-menu-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        .organiser-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 90;
            border: 0;
            padding: 0;
            background: rgba(6, 78, 59, 0.45);
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .shell {
                grid-template-columns: 1fr;
            }

            .organiser-topbar {
                display: flex;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 100;
                width: min(280px, 86vw);
                height: 100vh;
                height: 100dvh;
                transform: translateX(-105%);
                transition: transform 0.25s ease;
                box-shadow: 0 12px 40px rgba(6, 78, 59, 0.25);
            }

            .shell.is-mobile-nav-open .sidebar {
                transform: translateX(0);
            }

            .organiser-backdrop {
                display: block;
            }

            .organiser-backdrop[hidden] {
                display: none;
            }

            body.organiser-mobile-nav-open {
                overflow: hidden;
            }

            .content {
                padding: 20px 16px 28px;
            }

            .card {
                padding: 20px 16px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell" data-organiser-shell>
        <button type="button" class="organiser-backdrop" data-organiser-nav-backdrop hidden aria-label="Close navigation"></button>

        <aside class="sidebar">
            <div class="brand">Organiser Panel</div>
            <a href="{{ route('organiser.dashboard') }}">Dashboard</a>
            <a href="{{ url('/') }}">Website</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Logout</button>
            </form>
        </aside>

        <div class="organiser-main">
            <div class="organiser-topbar">
                <strong>Organiser Panel</strong>
                <button type="button" class="organiser-menu-toggle" data-organiser-nav-toggle aria-expanded="false" aria-label="Open menu">&#9776;</button>
            </div>

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        (function () {
            var shell = document.querySelector('[data-organiser-shell]');
            var toggle = document.querySelector('[data-organiser-nav-toggle]');
            var backdrop = document.querySelector('[data-organiser-nav-backdrop]');
            var mobileQuery = window.matchMedia('(max-width: 900px)');

            function closeNav() {
                shell?.classList.remove('is-mobile-nav-open');
                if (backdrop) backdrop.hidden = true;
                toggle?.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('organiser-mobile-nav-open');
            }

            function openNav() {
                shell?.classList.add('is-mobile-nav-open');
                if (backdrop) backdrop.hidden = false;
                toggle?.setAttribute('aria-expanded', 'true');
                document.body.classList.add('organiser-mobile-nav-open');
            }

            toggle?.addEventListener('click', function () {
                if (shell?.classList.contains('is-mobile-nav-open')) {
                    closeNav();
                } else {
                    openNav();
                }
            });

            backdrop?.addEventListener('click', closeNav);

            document.querySelectorAll('.sidebar a, .sidebar .logout').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (mobileQuery.matches) closeNav();
                });
            });

            mobileQuery.addEventListener('change', function () {
                if (! mobileQuery.matches) closeNav();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeNav();
            });
        })();
    </script>
</body>
</html>
