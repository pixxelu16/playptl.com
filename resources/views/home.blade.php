@extends('layouts.website')

@section('nav_active', 'home')

@section('title', 'Premier Tennis League')
@section('meta_description', 'Premier Tennis League is a competitive tennis platform for tournaments, teams, players, fixtures, galleries, and community events.')
@section('header_class', 'absolute inset-x-0 top-0 z-[100] px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="relative flex min-h-screen flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('public/frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
            </video>

            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

            <div class="relative z-10 flex flex-1 items-center px-5 pb-16 pt-40 sm:px-8 lg:px-14 lg:pb-20">
                <div class="mx-auto grid w-full max-w-[1400px] grid-cols-1 items-center gap-12 lg:grid-cols-[minmax(0,1fr)_280px] lg:gap-16 xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="max-w-3xl">
                        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-white/90 sm:mb-5 sm:text-sm">
                            Season 2026 - Now Open
                        </p>
                        <h1 class="text-[72px] font-bold uppercase leading-[1.05] tracking-[3px] sm:text-[96px] lg:text-[120px]">
                            <span class="champions-1 block text-white">Where Champions</span>
                            <span class="league-1 block text-lime">Are Forged.</span>
                        </h1>
                    </div>

                    <aside class="border-t border-white/15 pt-8 lg:border-0 lg:pt-0 lg:text-right">
                        <ul class="divide-y divide-white/15">
                            <li class="py-6 first:pt-0">
                                <p class="text-4xl font-extrabold tabular-nums tracking-tight sm:text-5xl xl:text-[3.25rem]" data-counter data-target="24" data-format="int">0</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80 sm:text-xs">Tournaments</p>
                            </li>
                            <li class="py-6">
                                <p class="text-4xl font-extrabold tabular-nums tracking-tight sm:text-5xl xl:text-[3.25rem]" data-counter data-target="186" data-format="int">0</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80 sm:text-xs">Active Teams</p>
                            </li>
                            <li class="py-6">
                                <p class="text-4xl font-extrabold tabular-nums tracking-tight sm:text-5xl xl:text-[3.25rem]" data-counter data-target="1400" data-format="compactK">0</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80 sm:text-xs">Players</p>
                            </li>
                            <li class="py-6 last:pb-0">
                                <p class="text-4xl font-extrabold tabular-nums tracking-tight sm:text-5xl xl:text-[3.25rem]" data-counter data-target="280000" data-format="usdK">0</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80 sm:text-xs">Prize Pool</p>
                            </li>
                        </ul>
                    </aside>
                </div>
            </div>
        </section>

        <section class="bg-[#e8f5e9] px-5 py-12 font-sans text-[#1a1a1a] antialiased sm:px-8 sm:py-16 lg:px-14 lg:py-20">
            <div class="mx-auto max-w-[1200px]">
                <h2 class="league-1 mb-8 text-center text-[42px] font-bold uppercase leading-tight tracking-[0.18em] sm:mb-10 sm:text-[66px]">
                    <span class="text-[#1a1a1a]">Live Form</span><span class="text-[#55A64E]"> The League</span>
                </h2>

                <div class="mb-4 hidden items-center lg:grid lg:grid-cols-2 lg:gap-8">
                    <div class="flex min-w-0 items-center justify-between gap-4">
                        <p class="m-0 shrink-0 text-[13px] font-bold uppercase tracking-[0.12em] text-[#1a1a1a] sm:text-sm">Announcements</p>
                        <a href="#" class="whitespace-nowrap text-[14px] font-semibold text-[#55A64E] underline decoration-[#55A64E] underline-offset-[3px] hover:opacity-90">View All</a>
                    </div>
                    <div class="min-w-0 text-center">
                        <p class="m-0 text-[13px] font-bold uppercase tracking-[0.12em] text-[#1a1a1a] sm:text-sm">Schedule</p>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <div>
                        <div class="mb-4 flex items-center justify-between gap-4 lg:hidden">
                            <p class="m-0 text-[13px] font-bold uppercase tracking-[0.12em] text-[#1a1a1a] sm:text-sm">Announcements</p>
                            <a href="#" class="text-[13px] font-semibold text-[#55A64E] underline decoration-[#55A64E] underline-offset-[3px] hover:opacity-90 sm:text-[14px]">View All</a>
                        </div>

                        <div class="mb-4 rounded-xl border border-[#efefef] bg-white p-5 shadow-[0_2px_16px_rgba(15,40,20,0.07)] sm:p-6">
                            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                                <span class="inline-flex items-center rounded border border-[#55A64E] bg-[#E4F7E7] px-3 py-1.5 text-sm font-semibold uppercase tracking-[0.08em] text-[#55A64E]">Featured - News</span>
                                <time class="shrink-0 text-xs font-medium text-[#757575]" datetime="2026-04-28">Apr 28, 2026</time>
                            </div>
                            <h3 class="mb-2 mt-[17px] text-lg font-bold leading-snug text-[#333333]">PTL Spring 2026 Registration Open</h3>
                            <p class="m-0 text-[16px] leading-relaxed text-[#757575]">
                                All divisions now open. Voyagers, Challengers, Warriors and Mixed Doubles brackets accepting entries until May 15.
                            </p>
                        </div>

                        <div class="space-y-3">
                            @foreach ([
                                ['day' => '25', 'month' => 'Apr', 'title' => 'Match Deadlines Reminder', 'text' => 'Please complete all pending Group A matches by this Sunday to remain eligible for playoffs.', 'tag' => 'Notice', 'tagClass' => 'border-[rgba(198,40,40,0.2)] bg-[rgba(211,47,47,0.12)] text-[#c62828]'],
                                ['day' => '22', 'month' => 'Apr', 'title' => 'Court Maintenance - Block B', 'text' => 'Courts 3-4 will be resurfaced Apr 29-30. Matches scheduled there are moved to Venue B.', 'tag' => 'Update', 'tagClass' => 'border-[rgba(21,101,192,0.2)] bg-[rgba(25,118,210,0.12)] text-[#1565c0]'],
                                ['day' => '18', 'month' => 'Apr', 'title' => 'Charity Exhibition Night', 'text' => 'Join us for the charity exhibition, themed games, raffles, and juniors showcase from 6 PM.', 'tag' => 'Event', 'tagClass' => 'border-[rgba(106,27,154,0.18)] bg-[rgba(106,27,154,0.1)] text-[#6a1b9a]'],
                            ] as $announcement)
                                <article class="flex items-stretch rounded-[10px] bg-white py-4 pl-[1.125rem] pr-4 shadow-[0_2px_16px_rgba(15,40,20,0.07)]">
                                    <div class="flex w-[2.625rem] shrink-0 flex-col items-center justify-center text-center" aria-hidden="true">
                                        <span class="text-[22px] font-extrabold leading-none text-[#55A64E]">{{ $announcement['day'] }}</span>
                                        <span class="mt-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#757575]">{{ $announcement['month'] }}</span>
                                    </div>
                                    <div class="ml-3 mr-3.5 min-h-12 w-px shrink-0 self-stretch bg-[#e0e0e0]" aria-hidden="true"></div>
                                    <div class="min-w-0 flex-1 self-center pr-2.5">
                                        <h4 class="m-0 mb-1 text-[0.9375rem] font-bold leading-snug text-[#333333] sm:text-[18px]">{{ $announcement['title'] }}</h4>
                                        <p class="m-0 text-base leading-[1.45] text-[#757575]">{{ $announcement['text'] }}</p>
                                    </div>
                                    <span class="{{ $announcement['tagClass'] }} shrink-0 self-center whitespace-nowrap rounded-md border px-2.5 py-1.5 text-[12px] font-bold uppercase leading-tight tracking-[0.07em]">{{ $announcement['tag'] }}</span>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="mb-4 lg:hidden">
                            <p class="m-0 text-[13px] font-bold uppercase tracking-[0.12em] text-[#1a1a1a] sm:text-sm">Schedule</p>
                        </div>

                        <div class="rounded-xl border border-[#e8e8e8] bg-white p-5 shadow-[0_2px_16px_rgba(15,40,20,0.07)] sm:p-6">
                            @foreach ([
                                ['date' => 'Tue - Apr 28', 'matches' => [['time' => '5:30 PM', 'court' => 'Court 1', 'players' => 'R. Sharma Vs M. Patel'], ['time' => '7:00 PM', 'court' => 'Court 2', 'players' => 'A. Khan Vs S. Verma']]],
                                ['date' => 'Wed - Apr 29', 'matches' => [['time' => '6:30 PM', 'court' => 'Court 1', 'players' => 'R. Sharma Vs M. Patel'], ['time' => '8:00 PM', 'court' => 'Court 1', 'players' => 'R. Sharma Vs M. Patel']]],
                            ] as $day)
                                <div class="@if (! $loop->first) mt-[30px] @endif">
                                    <div class="mb-2.5 flex items-baseline justify-between gap-3">
                                        <span class="m-0 text-base font-bold leading-tight text-[#1a1a1a]">Today</span>
                                        <span class="text-xs font-medium leading-tight text-[#666666] sm:text-[13px]">{{ $day['date'] }}</span>
                                    </div>
                                    <div class="divide-y divide-gray-200 overflow-hidden rounded-ui border border-gray-200 bg-[#F8F8F8]">
                                        @foreach ($day['matches'] as $match)
                                            <div class="px-4 py-3.5">
                                                <div class="mb-2 flex items-center justify-between gap-3">
                                                    <span class="text-[14px] font-medium text-[#666666]">{{ $match['time'] }}</span>
                                                    <span class="inline-flex items-center gap-1 text-[13px] font-medium text-[#666666]">
                                                        <svg class="h-4 w-4 shrink-0 text-[#9ca3af]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        {{ $match['court'] }}
                                                    </span>
                                                </div>
                                                <p class="m-0 text-sm font-bold leading-snug text-[#1a1a1a] sm:text-[18px]">{{ $match['players'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <a href="#" class="mt-6 flex w-full items-center justify-center rounded-xl bg-[#55A64E] py-3 text-[14px] font-bold text-white shadow-sm transition-opacity hover:opacity-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#55A64E] sm:py-3.5 sm:text-[15px]">
                                View Full Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden bg-[#f5f9f6] py-12 font-sans text-[#1a1a1a] antialiased sm:py-16" aria-labelledby="partners-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <div class="mb-10 flex items-center justify-center gap-3 sm:gap-5">
                    <span class="h-[2px] w-8 shrink-0 bg-[#55A64E] sm:w-[8.5rem]" aria-hidden="true"></span>
                    <h2 id="partners-heading" class="text-center text-[11px] font-semibold uppercase tracking-[0.22em] text-[#4b5563] sm:text-[20px] sm:tracking-[0.26em]">Official partners of the league</h2>
                    <span class="h-[2px] w-8 shrink-0 bg-[#55A64E] sm:w-[8.5rem]" aria-hidden="true"></span>
                </div>
            </div>

            <div class="relative w-full min-w-0 overflow-hidden">
                <div class="flex w-max select-none will-change-transform motion-reduce:animate-none animate-marquee">
                    <ul class="flex shrink-0 items-center gap-10 pr-10 sm:gap-14 sm:pr-14 md:gap-16 md:pr-16" role="list">
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-1.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-2.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-3.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-4.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-5.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-6.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-7.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-8.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-9.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                    </ul>
                    <ul class="flex shrink-0 items-center gap-10 pr-10 sm:gap-14 sm:pr-14 md:gap-16 md:pr-16" role="presentation" aria-hidden="true">
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-1.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-2.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-3.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-4.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-5.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-6.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-7.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-8.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                        <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ asset('public/frontend/images/partner-9.png') }}" alt="Partner logo" class="max-h-16 max-w-32 object-contain" loading="lazy">
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="gallery" class="relative overflow-x-hidden bg-[#e9f5e9] py-12 font-sans antialiased sm:py-16 lg:py-20" aria-labelledby="gallery-heading">
            <div class="mx-auto max-w-[1600px] px-5 sm:px-8 lg:px-14">
                <h2 id="gallery-heading" class="league-1 mb-8 text-center text-xl font-bold uppercase tracking-[0.12em] text-[#111827] sm:mb-10 sm:text-2xl md:text-[66px] md:tracking-[0.14em]">
                    <span class="text-[#111827]">Explore our </span><span class="text-[#619B4B]">gallery</span>
                </h2>

                <div class="mb-10 flex justify-center">
                    <div class="inline-flex rounded-[10px] border border-[#d1e7d3] bg-white p-1 shadow-[0_2px_14px_rgba(97,155,75,0.12)]" role="tablist" aria-label="Gallery by day">
                        <button type="button" id="gallery-tab-today" role="tab" aria-controls="gallery-panel-today" aria-selected="false" data-gallery-tab="today" class="rounded-[10px] bg-white px-7 py-2.5 text-sm font-semibold text-[#374151] transition-colors duration-200 aria-selected:bg-[#619B4B] aria-selected:text-white sm:px-12 sm:py-3 sm:text-[15px]">Today</button>
                        <button type="button" id="gallery-tab-yesterday" role="tab" aria-controls="gallery-panel-yesterday" aria-selected="true" data-gallery-tab="yesterday" class="rounded-[10px] bg-white px-7 py-2.5 text-sm font-semibold text-[#374151] transition-colors duration-200 aria-selected:bg-[#619B4B] aria-selected:text-white sm:px-12 sm:py-3 sm:text-[15px]">Yesterday</button>
                    </div>
                </div>
            </div>

            @foreach (['today' => true, 'yesterday' => false] as $panel => $hidden)
                <div id="gallery-panel-{{ $panel }}" role="tabpanel" aria-labelledby="gallery-tab-{{ $panel }}" data-gallery-panel="{{ $panel }}" class="{{ $hidden ? 'hidden ' : '' }}w-full min-w-0 focus:outline-none" tabindex="0" @if ($hidden) hidden @endif>
                    <div class="relative w-full min-w-0 overflow-x-hidden">
                        <div class="flex w-max select-none will-change-transform motion-reduce:animate-none animate-marquee-gallery">
                            <ul class="flex shrink-0 items-center gap-5 pr-5 sm:gap-6 sm:pr-6 md:gap-8 md:pr-8" role="list">
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/man-focused-tennis-game 2.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/person-playing-tennis-game-winter-time 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/portrait-beautiful-woman-playing-tennis-outdoor 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/tennis-player-serving-hard 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/young-man-tennis-player-court 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                            </ul>
                            <ul class="flex shrink-0 items-center gap-5 pr-5 sm:gap-6 sm:pr-6 md:gap-8 md:pr-8" role="presentation" aria-hidden="true">
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/man-focused-tennis-game 2.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/person-playing-tennis-game-winter-time 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/portrait-beautiful-woman-playing-tennis-outdoor 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/tennis-player-serving-hard 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                                <li class="shrink-0">
                                    <img src="{{ asset('public/frontend/images/young-man-tennis-player-court 1.png') }}" alt="Gallery photo" width="360" height="540" class="h-[540px] w-[360px] shrink-0 rounded-2xl object-cover shadow-md" loading="lazy" decoding="async">
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    </main>
@endsection
