<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Premier Tennis League')</title>
    <meta name="description" content="@yield('meta_description', 'Premier Tennis League official website.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    @php
        $pageBgRaw = trim((string) $__env->yieldContent('page_bg'));
        $pageBg = preg_match('/^#[0-9a-fA-F]{3,8}$/', $pageBgRaw) ? $pageBgRaw : '';
    @endphp
    @if ($pageBg !== '')
        <style>
            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                background-color: {{ $pageBg }} !important;
            }
            html {
                height: 100%;
            }
            body {
                min-height: 100%;
            }
        </style>
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: '#5cb85c', lime: '#c1e82c', mint: '#E4F7E7' },
                    borderRadius: { ui: '7px' },
                    fontFamily: {
                        sans: ['Inter', 'Montserrat', 'system-ui', 'sans-serif'],
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0)' },
                            '100%': { transform: 'translateX(-50%)' },
                        },
                    },
                    animation: {
                        marquee: 'marquee 50s linear infinite',
                        'marquee-gallery': 'marquee 65s linear infinite',
                    },
                },
            },
        };
    </script>
    @stack('styles')
</head>
<body class="@yield('body_class', 'min-h-screen overflow-x-hidden bg-[#0a0f18] font-sans text-white antialiased')">
    @php
        $headerLight = trim((string) $__env->yieldContent('header_theme')) === 'light';
        $navActive = trim((string) $__env->yieldContent('nav_active'));
        $defaultHeaderClass = $headerLight
            ? 'relative z-30 bg-[#E4F7E7] px-5 py-5 sm:px-8 lg:px-14 lg:py-6'
            : 'relative z-30 bg-[#0a0f18] px-5 py-5 sm:px-8 lg:px-14 lg:py-6';
        $headerLogoPath = trim((string) $__env->yieldContent('header_logo_path'));
        $defaultHeaderLogo = $navActive === 'home' ? 'frontend/images/logo.png' : 'frontend/images/logo-2.png';
        $headerLogoSrc = $headerLogoPath !== '' ? $headerLogoPath : $defaultHeaderLogo;
        $activeLeagueMenuItems = \App\Helpers\LeagueMenuHelper::activeLeagues();
    @endphp
    <header class="pointer-events-auto @yield('header_class', $defaultHeaderClass)">
        <div class="mx-auto flex max-w-[1400px] flex-wrap items-center justify-between gap-6">
            <a href="{{ url('/') }}" class="group flex items-center gap-3">
                <img src="{{ asset($headerLogoSrc) }}" alt="Premier Tennis League Logo" class="h-[92px] w-auto sm:h-[110px]">
            </a>

            <nav class="relative z-10 flex flex-wrap items-center justify-center gap-8 text-[16px] font-medium sm:gap-10" aria-label="Main">
                <a href="{{ url('/') }}" @class([
                    'transition-colors',
                    'text-[#c1e82c]' => ! $headerLight && $navActive === 'home',
                    'text-white/95 hover:text-white' => ! $headerLight && $navActive !== 'home',
                    'font-semibold text-[#c1e82c]' => $headerLight && $navActive === 'home',
                    'text-[#1a1a1a]/90 hover:text-[#1a1a1a]' => $headerLight && $navActive !== 'home',
                ])>Home</a>

                <div class="relative" data-dropdown>
                    <button type="button" @class([
                        'inline-flex items-center gap-1 rounded-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-lime/80 focus-visible:ring-offset-2',
                        'text-[#c1e82c]' => ! $headerLight && $navActive === 'league',
                        'text-white/95 hover:text-white focus-visible:ring-offset-[#0a0f18]' => ! $headerLight && $navActive !== 'league',
                        'font-semibold text-[#c1e82c]' => $headerLight && $navActive === 'league',
                        'text-[#1a1a1a]/90 hover:text-[#1a1a1a] focus-visible:ring-offset-[#E4F7E7]' => $headerLight && $navActive !== 'league',
                    ]) data-dropdown-trigger aria-expanded="false" aria-haspopup="true" aria-controls="nav-league-menu" id="nav-league-btn">
                        League
                        <svg data-dropdown-chevron class="h-3.5 w-3.5 opacity-80 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="nav-league-menu" role="menu" aria-labelledby="nav-league-btn" data-dropdown-panel class="invisible pointer-events-none absolute left-1/2 z-50 mt-3 min-w-[220px] -translate-x-1/2 translate-y-2 rounded-ui border border-white/10 bg-[rgba(10,15,24,0.96)] py-2 opacity-0 shadow-xl backdrop-blur-md transition-all duration-200 ease-out lg:left-0 lg:translate-x-0">
                        @forelse ($activeLeagueMenuItems as $leagueMenuItem)
                            <a href="{{ route('league.overview', ['slug' => $leagueMenuItem->slug]) }}" role="menuitem" class="block px-4 py-2.5 text-[14px] text-white/90 hover:bg-white/10 hover:text-white">{{ $leagueMenuItem->name }}</a>
                        @empty
                            <span class="block px-4 py-2.5 text-[14px] text-white/60">No active leagues</span>
                        @endforelse
                    </div>
                </div>

                <a href="{{ url('/gallery') }}" @class([
                    'transition-colors',
                    'text-[#B4F000]' => ! $headerLight && $navActive === 'gallery',
                    'text-white/95 hover:text-white' => ! $headerLight && $navActive !== 'gallery',
                    'font-semibold text-[#B4F000]' => $headerLight && $navActive === 'gallery',
                    'text-[#1a1a1a]/90 hover:text-[#1a1a1a]' => $headerLight && $navActive !== 'gallery',
                ])>Gallery</a>

                <a href="{{ url('/charity') }}" @class([
                    'transition-colors',
                    'text-[#C1D72E]' => ! $headerLight && $navActive === 'charity',
                    'text-white/95 hover:text-white' => ! $headerLight && $navActive !== 'charity',
                    'font-semibold text-[#C1D72E]' => $headerLight && $navActive === 'charity',
                    'text-[#1a1a1a]/90 hover:text-[#1a1a1a]' => $headerLight && $navActive !== 'charity',
                ])>Charity</a>
            </nav>

            <div class="flex w-full items-center justify-center gap-3 sm:w-auto sm:justify-end">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex min-w-[110px] items-center justify-center rounded-ui bg-brand px-5 py-2.5 text-[15px] font-bold text-white transition-opacity hover:opacity-95">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-w-[100px] items-center justify-center rounded-ui bg-[#4CAF50] px-5 py-2.5 text-[16px] font-bold text-white transition-opacity hover:opacity-95">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex min-w-[100px] items-center justify-center rounded-ui bg-[#C1D72E] px-5 py-2.5 text-[16px] font-bold text-[#1a1a1a] transition-opacity hover:opacity-95">Register</a>
                @endauth
            </div>
        </div>
    </header>

    @if ($errors->any())
        <div class="mx-auto max-w-[520px] px-5 pt-10 sm:px-8 lg:px-14">
            <div class="rounded-ui border border-red-500/35 bg-red-950/50 px-4 py-3 text-sm text-red-100">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="mx-auto max-w-[520px] px-5 pt-10 sm:px-8 lg:px-14">
            <div class="rounded-ui border border-emerald-500/35 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @yield('content')

    <footer class="bg-[#090E1A] font-sans text-[rgba(255,255,255,0.56)] antialiased" role="contentinfo">
        <div class="mx-auto max-w-[1400px] px-5 py-14 sm:px-8 lg:px-14 lg:py-16">
            <div class="grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-4 lg:gap-[130px]">
                <div class="max-w-sm lg:max-w-none">
                    <a href="{{ url('/') }}" class="inline-block">
                        <img src="{{ asset('frontend/images/logo.png') }}" alt="Premier Tennis League" width="152" height="120" class="h-[100px] w-auto object-contain object-left sm:h-[110px]" loading="lazy">
                    </a>
                    <p class="mt-6 text-[15px] leading-[1.65] text-[rgba(255,255,255,0.56)]">
                        The region's premier competitive tennis league. Forging champions, building community, raising funds for causes that matter.
                    </p>
                </div>

                <nav aria-label="League">
                    <h2 class="mb-5 text-[13px] font-bold uppercase tracking-[0.16em] text-white">League</h2>
                    <ul class="space-y-3 text-[15px]">
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Tournaments</a></li>
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Standings</a></li>
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Players</a></li>
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Match Results</a></li>
                    </ul>
                </nav>

                <nav aria-label="Community">
                    <h2 class="mb-5 text-[13px] font-bold uppercase tracking-[0.16em] text-white">Community</h2>
                    <ul class="space-y-3 text-[15px]">
                        <li><a href="{{ url('/charity') }}" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Charity Partners</a></li>
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Junior Program</a></li>
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Volunteer</a></li>
                        <li><a href="#" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">Sponsors</a></li>
                    </ul>
                </nav>

                <div>
                    <h2 class="mb-5 text-[13px] font-bold uppercase tracking-[0.16em] text-white">Contact us</h2>
                    <div class="space-y-5 text-[15px] leading-relaxed text-[rgba(255,255,255,0.56)]">
                        <div>
                            <p class="mb-1 text-[rgba(255,255,255,0.56)]">Call Us:</p>
                            <p><a href="tel:+919876543210" class="text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">+91 98765 43210</a></p>
                        </div>
                        <div>
                            <p class="mb-1 text-[rgba(255,255,255,0.56)]">Email:</p>
                            <p><a href="mailto:player.one@example.com" class="break-all text-[rgba(255,255,255,0.56)] transition-colors hover:text-white">player.one@example.com</a></p>
                        </div>
                        <div>
                            <p class="mb-1 text-[rgba(255,255,255,0.56)]">Address:</p>
                            <p>18 Sector 22, Chandigarh, India</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 border-t border-white/[0.08] pt-8">
                <p class="text-center text-[13px] leading-relaxed text-[rgba(255,255,255,0.56)] sm:text-sm">
                    &copy; 2026 Premier Tennis League. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>
