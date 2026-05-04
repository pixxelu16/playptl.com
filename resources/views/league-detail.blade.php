@extends('layouts.website')

@section('nav_active', 'league')

@section('title', $pageTitle)
@section('meta_description', $metaDescription)

@section('header_class', 'absolute inset-x-0 top-0 z-30 bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="relative flex min-h-screen min-h-[100dvh] flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full min-h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('public/frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
            </video>

            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

            <div class="pointer-events-none absolute right-0 top-0 z-[2] h-[min(45vh,420px)] w-[min(55vw,520px)] opacity-[0.14]" aria-hidden="true">
                <div class="absolute right-[8%] top-[12%] h-24 w-24 rounded-full bg-[#e8d94a] blur-2xl"></div>
                <div class="absolute right-[22%] top-[28%] h-16 w-16 rounded-full bg-[#c9b832] blur-xl"></div>
                <div class="absolute right-[5%] top-[38%] h-10 w-10 rounded-full bg-[#f5e85a] blur-lg"></div>
            </div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-24 pt-36 sm:pb-28 sm:pt-40 lg:pb-32 lg:pt-44">
                <header class="max-w-4xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[11px] font-semibold uppercase tracking-[0.28em] sm:mb-8 sm:text-xs md:text-[13px]" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-white transition-colors hover:text-white/90">Home</a>
                        <span class="mx-1 inline text-[#c1e82c] sm:mx-2">&gt;&gt;</span>
                        <a href="{{ route('league') }}" class="text-white transition-colors hover:text-white/90">{{ $breadcrumbLeagueLabel }}</a>
                        <span class="mx-1 inline text-[#c1e82c] sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#c1e82c]">{{ $breadcrumbGroup }}</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(3rem,10vw,7.5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">{{ $heroTitleLight }}</span><span class="text-[#c1e82c]"> {{ $heroTitleAccent }}</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[13px] font-medium leading-relaxed text-white sm:mt-10 sm:text-[15px] md:text-base">
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $statPlayers }} Players</span>
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $statGroups }} Groups</span>
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $statSeasonLabel }} {{ $statSeasonRange }}</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="bg-[#f0f9f0] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#2d4a2d] antialiased" aria-labelledby="league-detail-tabs-heading">
            <h2 id="league-detail-tabs-heading" class="sr-only">League group tabs</h2>
            <div class="mx-auto max-w-[1400px] px-5 py-10 sm:px-8 sm:py-12 lg:px-14 lg:py-16">
                <div class="mb-8 flex flex-wrap gap-2 sm:mb-10 sm:gap-3" role="tablist" aria-label="Group section">
                    <button type="button" id="tab-players" role="tab" aria-selected="true" aria-controls="panel-players" data-league-tab="players" class="league-tab-btn rounded-lg px-4 py-2.5 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px] bg-[#66a35a] text-white shadow-sm">
                        Players / Teams
                    </button>
                    <button type="button" id="tab-schedules" role="tab" aria-selected="false" aria-controls="panel-schedules" data-league-tab="schedules" class="league-tab-btn rounded-lg border border-[#66a35a] bg-[#E8F5E9] px-4 py-2.5 text-[14px] font-semibold text-[#2d4a2d] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#dcf2dc]">
                        Schedules
                    </button>
                    <button type="button" id="tab-standings-1" role="tab" aria-selected="false" aria-controls="panel-standings-1" data-league-tab="standings-1" class="league-tab-btn rounded-lg border border-[#66a35a] bg-[#E8F5E9] px-4 py-2.5 text-[14px] font-semibold text-[#2d4a2d] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#dcf2dc]">
                        Standings
                    </button>
                    <button type="button" id="tab-standings-2" role="tab" aria-selected="false" aria-controls="panel-standings-2" data-league-tab="standings-2" class="league-tab-btn rounded-lg border border-[#66a35a] bg-[#E8F5E9] px-4 py-2.5 text-[14px] font-semibold text-[#2d4a2d] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#dcf2dc]">
                        Standings
                    </button>
                    <button type="button" id="tab-profile" role="tab" aria-selected="false" aria-controls="panel-profile" data-league-tab="profile" class="league-tab-btn rounded-lg border border-[#66a35a] bg-[#E8F5E9] px-4 py-2.5 text-[14px] font-semibold text-[#2d4a2d] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#dcf2dc]">
                        My Profile
                    </button>
                </div>

                <div id="panel-players" role="tabpanel" aria-labelledby="tab-players" data-league-panel="players" class="league-tab-panel">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-7">
                        @foreach ($playerGroups as $g)
                            <div class="overflow-hidden rounded-xl bg-white shadow-[0_2px_12px_rgba(45,74,45,0.08)] ring-1 ring-[#e1f0e1]">
                                <div class="flex items-center justify-between rounded-t-xl bg-[#e1f0e1] px-4 py-3.5 sm:px-5 sm:py-4">
                                    <h3 class="text-[18px] font-bold leading-tight text-[#2d4a2d]">{{ $g['label'] }}</h3>
                                    <span class="text-[13px] font-medium text-[#5a8f5a] sm:text-[14px]">{{ $g['playerCount'] }} players</span>
                                </div>
                                <ul class="divide-y divide-[#e8ebe8] px-1 py-1">
                                    @foreach ($g['players'] as $player)
                                        <li class="flex items-center gap-3 px-3 py-3 sm:gap-4 sm:px-4 sm:py-3.5">
                                            <span class="w-7 shrink-0 text-center text-[12px] font-medium tabular-nums text-[#6b7a6b] sm:text-[13px]">{{ $player['index'] }}</span>
                                            <img
                                                src="https://ui-avatars.com/api/?name={!! rawurlencode($player['name']) !!}&size=80&background=e1f0e1&color=2d4a2d&bold=true"
                                                alt=""
                                                class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-white sm:h-11 sm:w-11"
                                                width="44"
                                                height="44"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                            <span class="min-w-0 flex-1 text-[16px] font-semibold leading-snug text-[#2d4a2d]">{{ $player['name'] }}</span>
                                            <button type="button" class="shrink-0 rounded p-1 text-[#66a35a] transition-opacity hover:opacity-80" aria-label="View {{ $player['name'] }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="panel-schedules" role="tabpanel" aria-labelledby="tab-schedules" data-league-panel="schedules" class="league-tab-panel hidden rounded-xl border border-dashed border-[#66a35a]/40 bg-white/60 px-6 py-16 text-center text-[15px] font-medium text-[#2d4a2d]/80">
                    Schedules coming soon.
                </div>
                <div id="panel-standings-1" role="tabpanel" aria-labelledby="tab-standings-1" data-league-panel="standings-1" class="league-tab-panel hidden rounded-xl border border-dashed border-[#66a35a]/40 bg-white/60 px-6 py-16 text-center text-[15px] font-medium text-[#2d4a2d]/80">
                    Standings coming soon.
                </div>
                <div id="panel-standings-2" role="tabpanel" aria-labelledby="tab-standings-2" data-league-panel="standings-2" class="league-tab-panel hidden rounded-xl border border-dashed border-[#66a35a]/40 bg-white/60 px-6 py-16 text-center text-[15px] font-medium text-[#2d4a2d]/80">
                    Standings coming soon.
                </div>
                <div id="panel-profile" role="tabpanel" aria-labelledby="tab-profile" data-league-panel="profile" class="league-tab-panel hidden rounded-xl border border-dashed border-[#66a35a]/40 bg-white/60 px-6 py-16 text-center text-[15px] font-medium text-[#2d4a2d]/80">
                    My Profile coming soon.
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            (function () {
                var tabs = document.querySelectorAll('[data-league-tab]');
                var panels = document.querySelectorAll('[data-league-panel]');

                function activate(name) {
                    tabs.forEach(function (btn) {
                        var on = btn.getAttribute('data-league-tab') === name;
                        btn.setAttribute('aria-selected', on ? 'true' : 'false');
                        if (on) {
                            btn.className =
                                'league-tab-btn rounded-lg px-4 py-2.5 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px] bg-[#66a35a] text-white shadow-sm';
                        } else {
                            btn.className =
                                'league-tab-btn rounded-lg border border-[#66a35a] bg-[#E8F5E9] px-4 py-2.5 text-[14px] font-semibold text-[#2d4a2d] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#dcf2dc]';
                        }
                    });
                    panels.forEach(function (panel) {
                        var show = panel.getAttribute('data-league-panel') === name;
                        panel.classList.toggle('hidden', !show);
                    });
                }

                tabs.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        activate(btn.getAttribute('data-league-tab'));
                    });
                });
            })();
        </script>
    @endpush
@endsection
