@extends('layouts.website')

@section('nav_active', 'league')

@section('title', $pageTitle)
@section('meta_description', $metaDescription)

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="relative flex h-[685px] min-h-[685px] flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full min-h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
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
                        <a href="{{ route('league.overview', ['slug' => $leagueSlug ?? '']) }}" class="text-white transition-colors hover:text-white/90">{{ $breadcrumbLeagueLabel }}</a>
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

        <section class="bg-[#e8f5e9] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#2d4a2d] antialiased" aria-labelledby="league-detail-tabs-heading">
            <h2 id="league-detail-tabs-heading" class="sr-only">League group tabs</h2>
            <div class="mx-auto max-w-[1400px] px-5 py-10 sm:px-8 sm:py-12 lg:px-14 lg:py-16">
                <div id="league-tablist" class="mb-8 flex flex-wrap gap-2 sm:mb-10 sm:gap-3" role="tablist" aria-label="Group section">
                    <button type="button" id="tab-players" role="tab" aria-selected="true" aria-controls="panel-players" data-league-tab="players" class="league-tab-btn rounded-lg px-4 py-2.5 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px] bg-[#5E9E52] text-white shadow-sm">
                        Players / Teams
                    </button>
                    <button type="button" id="tab-schedules" role="tab" aria-selected="false" aria-controls="panel-schedules" data-league-tab="schedules" class="league-tab-btn rounded-lg border border-[#E0E0E0] bg-[#F1F8E9] px-4 py-2.5 text-[14px] font-semibold text-[#333333] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#e8f5e4]">
                        Schedules
                    </button>
                    <button type="button" id="tab-standings-1" role="tab" aria-selected="false" aria-controls="panel-standings-1" data-league-tab="standings-1" class="league-tab-btn rounded-lg border border-[#E0E0E0] bg-[#F1F8E9] px-4 py-2.5 text-[14px] font-semibold text-[#333333] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#e8f5e4]">
                        Standings
                    </button>
                    <button type="button" id="tab-playoffs" role="tab" aria-selected="false" aria-controls="panel-playoffs" data-league-tab="playoffs" class="league-tab-btn rounded-lg border border-[#E0E0E0] bg-[#F1F8E9] px-4 py-2.5 text-[14px] font-semibold text-[#333333] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#e8f5e4]">
                        Playoffs
                    </button>
                    <button type="button" id="tab-profile" role="tab" aria-selected="false" aria-controls="panel-profile" data-league-tab="profile" class="league-tab-btn rounded-lg border border-[#E0E0E0] bg-[#F1F8E9] px-4 py-2.5 text-[14px] font-semibold text-[#333333] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#e8f5e4]">
                        My Profile
                    </button>
                </div>

                <div id="panel-players" role="tabpanel" aria-labelledby="tab-players" data-league-panel="players" class="league-tab-panel">
                    <div id="league-teams-view">
                        @if (empty($playerGroups))
                            <div class="rounded-xl bg-white p-6 text-center text-[15px] font-semibold text-[#5a8f5a] shadow-[0_2px_12px_rgba(45,74,45,0.08)] ring-1 ring-[#e1f0e1]">
                                Players will appear here once they are assigned to groups.
                            </div>
                        @else
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-7">
                            @foreach ($playerGroups as $g)
                                <div class="overflow-hidden rounded-xl bg-white shadow-[0_2px_12px_rgba(45,74,45,0.08)] p-[5px] ring-1 ring-[#e1f0e1]">
                                    <div class="flex items-center justify-between rounded bg-[#e1f0e1] px-4 py-3.5 sm:px-5 sm:py-4">
                                        <h3 class="text-[18px] font-bold leading-tight text-[#2d4a2d]">{{ $g['label'] }}</h3>
                                        <span class="text-[13px] font-medium text-[#5a8f5a] sm:text-[14px]">{{ $g['playerCount'] }} players</span>
                                    </div>
                                    <ul class="divide-y divide-[#e8ebe8] px-1 py-0">
                                        @forelse ($g['players'] as $player)
                                            <li class="flex items-center gap-[5px] px-3 py-1.5 sm:gap-3 sm:px-4 sm:py-2">
                                                <span class="w-7 shrink-0 text-center text-[12px] font-medium tabular-nums text-[#6b7a6b] sm:text-[13px]">{{ $player['index'] }}</span>
                                                <img
                                                    src="{{ $player['avatarUrl'] ?? asset('upload/user-avatar/default-user-pic.png') }}"
                                                    alt=""
                                                    class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-white sm:h-11 sm:w-11"
                                                    width="44"
                                                    height="44"
                                                    loading="lazy"
                                                    decoding="async"
                                                />
                                                <span class="min-w-0 flex-1 text-[16px] font-semibold leading-snug text-[#2d4a2d]">{{ $player['name'] }}</span>
                                                <button type="button" class="js-open-player-profile shrink-0 rounded p-1 text-[#66a35a] transition-opacity hover:opacity-80" data-player-key="{{ $player['key'] }}" aria-label="View {{ $player['name'] }}">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </li>
                                        @empty
                                            <li class="px-4 py-4 text-[14px] font-semibold text-[#5a8f5a]">No players assigned yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div id="league-player-dashboard" class="hidden" aria-hidden="true">
                        <button type="button" id="league-player-back" class="mb-5 text-left text-[14px] font-semibold text-[#2d4a2d] underline decoration-[#2d4a2d] underline-offset-2 transition-opacity hover:opacity-80">
                            &larr; Back to teams
                        </button>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-12 lg:gap-5">
                            <div class="lg:col-span-3">
                                <div class="overflow-hidden rounded-[10px] bg-white shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-[#E8F5E9] p-[5px]">
                                    <div class="bg-[#E1F0E1] px-5 py-6 text-center">
                                        <img id="pd-avatar" src="" alt="" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-white" width="96" height="96" />
                                        <h3 id="pd-name" class="mt-4 text-lg font-bold leading-tight text-[#1a1a1a]"></h3>
                                        <p id="pd-subtitle" class="mt-1 text-[14px] text-[#6B7280]"></p>
                                    </div>
                                    <dl class="divide-y divide-[#E5E7EB] px-5 py-1">
                                        <div class="flex items-center justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Player ID</dt>
                                            <dd id="pd-player-id" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Division</dt>
                                            <dd id="pd-division" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Group</dt>
                                            <dd id="pd-group" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Seed</dt>
                                            <dd id="pd-seed" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Status</dt>
                                            <dd id="pd-status" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Joined</dt>
                                            <dd id="pd-joined" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div class="space-y-5 lg:col-span-5">
                                <div class="overflow-hidden rounded-[10px] bg-white p-5 shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-[#E8F5E9]">
                                    <div class="grid grid-cols-2 divide-x divide-[#E5E7EB] sm:grid-cols-4">
                                        <div class="px-2 py-1 text-center sm:px-3">
                                            <p class="text-[12px] font-medium text-[#6B7280]">Matches</p>
                                            <p id="pd-stat-matches" class="mt-1 text-[24px] font-bold tabular-nums text-[#111827]"></p>
                                        </div>
                                        <div class="px-2 py-1 text-center sm:px-3">
                                            <p class="text-[12px] font-medium text-[#6B7280]">Wins</p>
                                            <p id="pd-stat-wins" class="mt-1 text-[24px] font-bold tabular-nums text-[#111827]"></p>
                                        </div>
                                        <div class="px-2 py-1 text-center sm:px-3">
                                            <p class="text-[12px] font-medium text-[#6B7280]">Losses</p>
                                            <p id="pd-stat-losses" class="mt-1 text-[24px] font-bold tabular-nums text-[#111827]"></p>
                                        </div>
                                        <div class="px-2 py-1 text-center sm:px-3">
                                            <p class="text-[12px] font-medium text-[#6B7280]">Points</p>
                                            <p id="pd-stat-points" class="mt-1 text-[24px] font-bold tabular-nums text-[#111827]"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-[10px] bg-white p-5 shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-[#E8F5E9]">
                                    <h4 class="mb-4 text-[18px] font-bold text-[#1a1a1a]">Recent Matches</h4>
                                    <ul id="pd-recent-matches" class="divide-y divide-[#E5E7EB]"></ul>
                                </div>
                            </div>

                            <div class="space-y-5 lg:col-span-4">
                                <div class="overflow-hidden rounded-[10px] bg-white p-5 shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-[#E8F5E9]">
                                    <h4 class="mb-5 text-[18px] font-bold text-[#1a1a1a]">Performance</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="mb-1 flex justify-between text-[14px]">
                                                <span class="text-[#6B7280]">Win Rate</span>
                                                <span id="pd-pct-win" class="font-semibold text-[#2d4a2d]"></span>
                                            </div>
                                            <div class="h-2.5 overflow-hidden rounded-full bg-[#E5E7EB]">
                                                <div id="pd-bar-win" class="h-full rounded-full bg-[#4a8f4a]" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="mb-1 flex justify-between text-[14px]">
                                                <span class="text-[#6B7280]">Game %</span>
                                                <span id="pd-pct-game" class="font-semibold text-[#2d4a2d]"></span>
                                            </div>
                                            <div class="h-2.5 overflow-hidden rounded-full bg-[#E5E7EB]">
                                                <div id="pd-bar-game" class="h-full rounded-full bg-[#4a8f4a]" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="mb-1 flex justify-between text-[14px]">
                                                <span class="text-[#6B7280]">Set %</span>
                                                <span id="pd-pct-set" class="font-semibold text-[#2d4a2d]"></span>
                                            </div>
                                            <div class="h-2.5 overflow-hidden rounded-full bg-[#E5E7EB]">
                                                <div id="pd-bar-set" class="h-full rounded-full bg-[#4a8f4a]" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-[10px] bg-white p-5 shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-[#E8F5E9]">
                                    <h4 class="mb-4 text-[18px] font-bold text-[#1a1a1a]">Personal Information</h4>
                                    <dl class="divide-y divide-[#E5E7EB]">
                                        <div class="flex items-start justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Full Name</dt>
                                            <dd id="pd-full-name" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Phone Number</dt>
                                            <dd id="pd-phone" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Email</dt>
                                            <dd id="pd-email" class="break-all text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Location</dt>
                                            <dd id="pd-location" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">Date of birth</dt>
                                            <dd id="pd-dob" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-3 py-3">
                                            <dt class="text-[14px] text-[#6B7280]">NTRP Rating</dt>
                                            <dd id="pd-ntrp" class="text-right text-[14px] font-semibold text-[#1F2937]"></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="panel-schedules" role="tabpanel" aria-labelledby="tab-schedules" data-league-panel="schedules" class="league-tab-panel hidden">
                    @foreach ($scheduleDays as $day)
                        <h3 class="mb-4 mt-10 text-[15px] font-bold uppercase leading-tight tracking-[0.06em] text-[#212121] first:mt-0 sm:text-[16px]">{{ $day['dateLabel'] }}</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 lg:gap-6">
                            @foreach ($day['matches'] as $match)
                                @php $scheduleMenuId = 'schedule-menu-'.$loop->parent->iteration.'-'.$loop->iteration; @endphp
                                <article class="relative overflow-visible rounded-[10px] bg-white p-4 shadow-[0_1px_4px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04] sm:p-5">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-start sm:gap-3">
                                        <div class="min-w-0 text-left">
                                            <span class="mb-1 inline-block rounded bg-[#E8F5E9] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#2E7D32]">Home</span>
                                            <p class="text-[16px] font-bold leading-snug text-[#212121]">{{ $match['leftName'] }}</p>
                                            <p class="mt-0.5 text-[13px] leading-snug text-[#757575]">{{ $match['leftMeta'] }}</p>
                                        </div>
                                        <div class="flex flex-col items-center justify-start gap-2 py-1 sm:min-w-[100px]">
                                            <span class="text-[13px] font-bold uppercase tracking-wide text-[#212121]">VS</span>
                                            <div class="rounded-full bg-[#E8F5E9] px-3 py-1.5 text-center">
                                                @if (($match['finished'] ?? false) === true && ($match['score'] ?? '') !== '')
                                                    <span class="text-[13px] font-semibold text-[#2E7D32] sm:text-[14px]">{{ $match['score'] }}</span>
                                                @else
                                                    <span class="text-[13px] font-semibold text-[#2E7D32] sm:text-[14px]">Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="min-w-0 text-right">
                                            <span class="mb-1 inline-block rounded bg-[#E8F5E9] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#2E7D32]">Away</span>
                                            <p class="text-[16px] font-bold leading-snug text-[#212121]">{{ $match['rightName'] }}</p>
                                            <p class="mt-0.5 text-[13px] leading-snug text-[#757575]">{{ $match['rightMeta'] }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-[#E0E0E0] pt-4 text-[13px] text-[#757575] sm:gap-x-6">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 shrink-0 text-[#757575]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ $match['dateShort'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 shrink-0 text-[#757575]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $match['time'] }}
                                        </span>
                                        <span class="inline-flex min-w-0 flex-1 items-center gap-1.5 sm:flex-none">
                                            <svg class="h-4 w-4 shrink-0 text-[#757575]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="min-w-0">{{ $match['venue'] }}</span>
                                        </span>
                                        <div class="relative ml-auto shrink-0" data-schedule-menu>
                                            <button
                                                type="button"
                                                class="rounded p-1 text-[#5A6772] hover:bg-black/[0.04] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#66BB6A]/50"
                                                data-schedule-trigger
                                                aria-expanded="false"
                                                aria-haspopup="menu"
                                                aria-controls="{{ $scheduleMenuId }}"
                                                aria-label="More options"
                                            >
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                                </svg>
                                            </button>
                                            <div
                                                id="{{ $scheduleMenuId }}"
                                                data-schedule-dropdown
                                                role="menu"
                                                aria-hidden="true"
                                                class="pointer-events-none invisible absolute right-full top-1/2 z-[70] mr-0 min-w-[145px] -translate-y-1/2 translate-x-[14px] opacity-0 transition-[opacity,visibility] duration-150"
                                            >
                                                <div class="flex items-stretch">
                                                    <div class="rounded-lg border border-[#E0E0E0] bg-[#FFFFFF] py-1 shadow-[0_4px_18px_rgba(0,0,0,0.1)]">
                                                        <button type="button" role="menuitem" class="block w-full whitespace-nowrap px-4 py-3 text-left text-[14px] font-semibold leading-snug text-[#4B5563] transition-colors hover:bg-[#F9FAFB] focus-visible:bg-[#F9FAFB] focus-visible:outline-none">
                                                            Add Location
                                                        </button>
                                                        <button type="button" role="menuitem" class="block w-full whitespace-nowrap px-4 py-3 text-left text-[14px] font-semibold leading-snug text-[#4B5563] transition-colors hover:bg-[#F9FAFB] focus-visible:bg-[#F9FAFB] focus-visible:outline-none">
                                                            Upload Image
                                                        </button>
                                                    </div>
                                                    <svg class="-ml-px h-[16px] w-[6px] shrink-0 self-center text-[#E0E0E0]" viewBox="0 0 6 16" aria-hidden="true">
                                                        <path d="M0 0 L6 8 L0 16 Z" fill="#FFFFFF" stroke="#E0E0E0" stroke-width="1" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div id="panel-standings-1" role="tabpanel" aria-labelledby="tab-standings-1" data-league-panel="standings-1" class="league-tab-panel hidden">
                    <div class="mb-5 flex flex-col gap-4 sm:mb-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-[15px] font-bold leading-snug text-[#212121] sm:text-[16px]">
                            All {{ count($standingsRows) }} players in one table
                        </p>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span class="text-[13px] font-medium text-[#757575] sm:text-[14px]">Sorted by:</span>
                            <button type="button" class="rounded-full border border-[#E0E0E0] bg-white px-3.5 py-1.5 text-[13px] font-semibold text-[#424242] shadow-sm transition-colors hover:bg-[#FAFAFA] sm:text-[14px]">
                                1 · Points
                            </button>
                            <button type="button" class="rounded-full border border-[#E0E0E0] bg-white px-3.5 py-1.5 text-[13px] font-semibold text-[#424242] shadow-sm transition-colors hover:bg-[#FAFAFA] sm:text-[14px]">
                                2 · Game %
                            </button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[10px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.06]">
                        <div class="overflow-x-auto p-[5px]">
                            <table class="w-full min-w-[800px] border-collapse text-left">
                                <thead>
                                    <tr class="bg-[#e1f0e1] rounded">
                                        <th scope="col" class="px-4 py-3.5 text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Rank</th>
                                        <th scope="col" class="px-4 py-3.5 text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Player Name</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Matches</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Wins</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Losses</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Points</th>
                                        <th scope="col" class="px-4 py-3.5 text-right text-[13px] font-bold text-[#374151] sm:pl-5 sm:pr-6 sm:text-[14px]">Game%</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @foreach ($standingsRows as $row)
                                        <tr class="transition-colors hover:bg-[#FAFAFA]/80">
                                            <td class="whitespace-nowrap px-4 py-3.5 align-middle text-[14px] tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">
                                                {{ str_pad((string) $row['rank'], 2, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="px-4 py-3.5 align-middle sm:px-5">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <img
                                                        src="{{ $row['avatarUrl'] ?? asset('upload/user-avatar/default-user-pic.png') }}"
                                                        alt=""
                                                        class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-white sm:h-10 sm:w-10"
                                                        width="40"
                                                        height="40"
                                                        loading="lazy"
                                                        decoding="async"
                                                    />
                                                    <span class="truncate text-[14px] font-bold text-[#212121] sm:text-[17px]">{{ $row['name'] }}</span>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-center align-middle text-[14px] tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">{{ $row['matches'] }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-center align-middle text-[14px] tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">{{ $row['wins'] }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-center align-middle text-[14px] tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">{{ $row['losses'] }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-center align-middle text-[14px] font-semibold tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">{{ $row['points'] }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle text-[14px] font-semibold tabular-nums text-[#66BB6A] sm:pl-5 sm:pr-6 sm:text-[17px]">{{ $row['gamePct'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="panel-playoffs" role="tabpanel" aria-labelledby="tab-playoffs" data-league-panel="playoffs" class="league-tab-panel hidden">
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:gap-6 xl:gap-8">
                        @foreach ($playoffColumns as $col)
                            <div class="flex min-w-0 flex-col">
                                <h3 class="mb-4 text-center text-[13px] font-bold uppercase tracking-[0.12em] text-[#374151] sm:text-[14px]">{{ $col['title'] }}</h3>
                                <div class="flex flex-col gap-4">
                                    @foreach ($col['matches'] as $m)
                                        <article class="overflow-hidden rounded-[10px] bg-white shadow-[0_2px_10px_rgba(0,0,0,0.08)] ring-1 ring-black/[0.04] p-[5px]">
                                            <div class="flex items-center justify-between bg-[#E1F0E1] px-4 py-4 rounded">
                                                <span class="text-[15px] font-bold text-[#424242]">{{ $m['label'] }}</span>
                                                <span class="text-[12px] font-medium text-[#9CA3AF]">{{ $m['status'] }}</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-3 border-b border-[#E5E7EB] px-4 py-3">
                                                    <span class="flex h-9 min-w-[3rem] shrink-0 items-center justify-center rounded border border-[#C8E6C9] px-1 text-[13px] font-semibold leading-none text-[#2E7D32]">{{ $m['p1']['code'] }}</span>
                                                    <span class="min-w-0 flex-1 text-[15px] font-semibold leading-snug text-[#424242] sm:text-[17px]">{{ $m['p1']['name'] }}</span>
                                                </div>
                                                <div class="flex items-center gap-3 px-4 py-3">
                                                    <span class="flex h-9 min-w-[3rem] shrink-0 items-center justify-center rounded border border-[#C8E6C9] px-1 text-[13px] font-semibold leading-none text-[#2E7D32]">{{ $m['p2']['code'] }}</span>
                                                    <span class="min-w-0 flex-1 text-[15px] font-semibold leading-snug text-[#424242] sm:text-[17px]">{{ $m['p2']['name'] }}</span>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach

                                    @if (!empty($col['champion']))
                                        <div class="relative mt-1 overflow-hidden rounded-[10px] ">
                                            <img
                                                src="{{ asset('frontend/images/champion.png') }}"
                                                alt="Champion — Season 2026"
                                                class="block h-auto w-full max-w-full"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if (false)
                <div id="panel-profile" role="tabpanel" aria-labelledby="tab-profile" data-league-panel="profile" class="league-tab-panel hidden">
                    @php
                        $mp = $myProfile;
                        $profileInputClass =
                            'w-full rounded-[10px] border border-[#dddddd] bg-white px-3.5 py-2.5 text-[15px] text-[#333333] shadow-sm placeholder:text-[#9CA3AF] focus:border-[#5E9E52] focus:outline-none focus:ring-1 focus:ring-[#5E9E52] sm:text-[16px]';
                        $profileInputReadonlyClass =
                            'w-full cursor-not-allowed rounded-md border border-[#D1D5DB] bg-[#F9FAFB] px-3.5 py-2.5 text-[15px] text-[#6B7280] shadow-sm focus:border-[#D1D5DB] focus:ring-0 sm:text-[16px]';
                        $profileLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]';
                        $profileNavActive = 'w-full rounded-[10px] bg-[#5E9E52] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]';
                        $profileNavInactive =
                            'w-full rounded-[10px] border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#333333] transition-colors hover:bg-[#fafafa] sm:text-[15px]';
                        $profilePwLabelClass = 'mb-1.5 block text-[14px] font-bold text-[#333333]';
                        $profilePwInputClass =
                            'w-full rounded-[10px] border border-[#dddddd] bg-white py-2.5 pl-3.5 pr-11 text-[15px] text-[#333333] shadow-sm placeholder:text-[#9CA3AF] focus:border-[#5E9E52] focus:outline-none focus:ring-1 focus:ring-[#5E9E52] sm:text-[16px]';
                        $scheduleLabelClass = 'mb-1.5 block text-[14px] font-bold text-[#333333]';
                        $scheduleFieldBase =
                            'w-full rounded-[8px] border border-[#dddddd] bg-white text-[14px] font-normal leading-normal text-[#333333] shadow-sm placeholder:text-[#888888] focus:border-[#5E9E52] focus:outline-none focus:ring-1 focus:ring-[#5E9E52] min-h-[44px]';
                        $scheduleInputClass = $scheduleFieldBase.' px-3.5 py-2.5';
                        $scheduleInputIconClass = $scheduleFieldBase.' py-2.5 pl-10 pr-3.5';
                        $scheduleSelectClass = $scheduleFieldBase.' appearance-none px-3.5 py-2.5 pr-10 text-[#888888]';
                    @endphp
                    <div class="flex flex-col gap-6 overflow-x-auto pb-1 lg:flex-row lg:items-start lg:gap-6">
                        <aside class="w-full shrink-0 lg:w-[450px] lg:min-w-[450px] lg:max-w-[450px]">
                            <div class="overflow-hidden rounded-[12px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] p-[5px]">
                                <div class="bg-[#e1f0e4] px-5 py-6 text-center rounded-[8px]">
                                    <div class="relative mx-auto h-[100px] w-[100px]">
                                        <img
                                            src="{{ $mp['avatarUrl'] }}"
                                            alt=""
                                            class="h-full w-full rounded-full object-cover ring-2 ring-white"
                                            width="100"
                                            height="100"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                        <button
                                            type="button"
<<<<<<< HEAD
                                            data-profile-jump-upload
                                            class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#62A351] text-white shadow-md transition hover:bg-[#5a9449]"
=======
                                            class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#5E9E52] text-white shadow-md transition hover:bg-[#548948]"
>>>>>>> 22ad779e40b36f59080947a4e4129e31d6484422
                                            aria-label="Edit profile photo"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <h3 class="mt-4 text-[18px] font-bold leading-tight text-[#333333]">{{ $mp['name'] }}</h3>
                                    <p class="mt-1 text-[14px] font-medium text-[#757575]">{{ $mp['roleLine'] }}</p>
                                </div>
                                <nav id="profile-side-nav" class="space-y-2 p-4" aria-label="Profile sections">
                                    <button type="button" data-profile-section="personal" class="{{ $profileNavActive }}">Personal Information</button>
                                    <button type="button" data-profile-section="password" class="{{ $profileNavInactive }}">Password &amp; Security</button>
                                    <button type="button" data-profile-section="location" class="{{ $profileNavInactive }}">Add Location</button>
                                    <button type="button" data-profile-section="upload" class="{{ $profileNavInactive }}">Upload Image</button>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-lg border border-red-200 bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-red-600 transition-colors hover:bg-red-50 sm:text-[15px]">
                                            Logout
                                        </button>
                                    </form>
                                </nav>
                            </div>
                        </aside>

                        <div class="min-w-0 w-full space-y-6 lg:w-[810px] lg:min-w-[810px] lg:max-w-[810px] lg:shrink-0">
                            <div
                                id="profile-section-personal"
                                class="profile-section-panel overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#dddddd] sm:p-8"
                                data-profile-section="personal"
                            >
                                <h4 class="mb-6 text-[18px] font-bold leading-tight text-[#212121] sm:text-[20px]">Personal Information</h4>
                                <form class="space-y-5" action="{{ route('player.profile.update') }}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="league_id" value="{{ $leagueId }}">
                                    <input type="hidden" name="group_card_id" value="{{ $groupCardId }}">
                                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                        <div>
                                            <label for="mp-first" class="{{ $profileLabelClass }}">First Name</label>
                                            <input id="mp-first" name="first_name" type="text" value="{{ old('first_name', $mp['firstName']) }}" placeholder="Enter first name" class="{{ $profileInputClass }}" autocomplete="given-name" />
                                        </div>
                                        <div>
                                            <label for="mp-last" class="{{ $profileLabelClass }}">Last Name</label>
                                            <input id="mp-last" name="last_name" type="text" value="{{ old('last_name', $mp['lastName']) }}" placeholder="Enter last name" class="{{ $profileInputClass }}" autocomplete="family-name" />
                                        </div>
                                        <div>
                                            <label for="mp-dob" class="{{ $profileLabelClass }}">Date Of Birth</label>
                                            <input id="mp-dob" name="date_of_birth" type="date" value="{{ old('date_of_birth', $mp['dob']) }}" class="{{ $profileInputClass }}" />
                                        </div>
                                        <div>
                                            <label for="mp-ntrp" class="{{ $profileLabelClass }}">NTRP Rating</label>
                                            <div class="relative">
                                                <select id="mp-ntrp" name="ntrp" class="{{ $profileInputClass }} appearance-none pr-10">
                                                    <option value="" @selected(old('ntrp', $mp['ntrp']) === '')>Select rating</option>
                                                    @foreach (['2.5', '3.0', '3.5', '4.0', '4.5', '5.0'] as $r)
                                                        <option value="{{ $r }}" @selected(old('ntrp', $mp['ntrp']) === $r)>{{ $r }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#6B7280]">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="mp-email" class="{{ $profileLabelClass }}">Email Address</label>
                                            <input id="mp-email" name="email" type="email" value="{{ old('email', $mp['email']) }}" placeholder="Enter email address" class="{{ $profileInputClass }}" autocomplete="email" required />
                                        </div>
                                        <div>
                                            <label for="mp-phone" class="{{ $profileLabelClass }}">Phone Number</label>
                                            <input id="mp-phone" name="phone" type="tel" value="{{ old('phone', $mp['phone']) }}" placeholder="Enter phone number" class="{{ $profileInputClass }}" autocomplete="tel" />
                                        </div>
                                        <div>
                                            <label for="mp-city" class="{{ $profileLabelClass }}">City / Location</label>
                                            <input id="mp-city" name="city" type="text" value="{{ old('city', $mp['city']) }}" placeholder="Enter city" class="{{ $profileInputClass }}" />
                                        </div>
                                        <div>
                                            <label for="mp-division" class="{{ $profileLabelClass }}">Division</label>
                                            <input id="mp-division" name="division" type="text" value="{{ $mp['division'] }}" placeholder="Division" class="{{ $profileInputReadonlyClass }}" readonly />
                                        </div>
                                        <div>
                                            <label for="mp-group" class="{{ $profileLabelClass }}">Group</label>
                                            <input id="mp-group" name="group" type="text" value="{{ $mp['group'] }}" placeholder="Group" class="{{ $profileInputReadonlyClass }}" readonly />
                                        </div>
                                        <div>
                                            <label for="mp-court" class="{{ $profileLabelClass }}">Home Court</label>
                                            <input id="mp-court" name="home_court" type="text" value="{{ old('home_court', $mp['homeCourt']) }}" placeholder="Home court" class="{{ $profileInputClass }}" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="mp-hand" class="{{ $profileLabelClass }}">Dominant Hand</label>
                                        <div class="relative max-w-full sm:max-w-md">
                                            <select id="mp-hand" name="dominant_hand" class="{{ $profileInputClass }} appearance-none pr-10">
                                                @foreach (['Right', 'Left', 'Ambidextrous'] as $h)
                                                    <option value="{{ $h }}" @selected(old('dominant_hand', $mp['dominantHand']) === $h)>{{ $h }}</option>
                                                @endforeach
                                            </select>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#6B7280]">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-3 pt-2">
                                        <button type="button" class="rounded-[10px] bg-[#ececec] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#e0e0e0] sm:text-[15px]">
                                            Cancel
                                        </button>
                                        <button type="submit" class="rounded-[10px] bg-[#5E9E52] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#548948] sm:text-[15px]">
                                            Save Change
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div
                                id="profile-section-password"
                                class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#dddddd] sm:p-8 font-['Inter',ui-sans-serif,system-ui,sans-serif]"
                                data-profile-section="password"
                            >
                                <h4 class="mb-6 text-[18px] font-bold leading-tight text-[#333333]">Password &amp; Security</h4>
                                <form class="space-y-5" action="#" method="post" onsubmit="return false;">
                                    @csrf
                                    <div class="space-y-5">
                                        <div>
                                            <label for="mp-pw-current" class="{{ $profilePwLabelClass }}">Current Password</label>
                                            <div class="relative">
                                                <input
                                                    id="mp-pw-current"
                                                    name="current_password"
                                                    type="password"
                                                    autocomplete="current-password"
                                                    class="{{ $profilePwInputClass }}"
                                                />
                                                <button
                                                    type="button"
                                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-[#999999] transition hover:text-[#555555] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5E9E52] focus-visible:ring-offset-2 rounded-r-[10px]"
                                                    data-profile-pw-toggle="mp-pw-current"
                                                    aria-label="Show password"
                                                >
                                                    <svg class="h-5 w-5 icon-eye-off" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                    <svg class="hidden h-5 w-5 icon-eye" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="mp-pw-new" class="{{ $profilePwLabelClass }}">New Password</label>
                                            <div class="relative">
                                                <input
                                                    id="mp-pw-new"
                                                    name="password"
                                                    type="password"
                                                    autocomplete="new-password"
                                                    class="{{ $profilePwInputClass }}"
                                                />
                                                <button
                                                    type="button"
                                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-[#999999] transition hover:text-[#555555] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5E9E52] focus-visible:ring-offset-2 rounded-r-[10px]"
                                                    data-profile-pw-toggle="mp-pw-new"
                                                    aria-label="Show password"
                                                >
                                                    <svg class="h-5 w-5 icon-eye-off" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                    <svg class="hidden h-5 w-5 icon-eye" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="mp-pw-confirm" class="{{ $profilePwLabelClass }}">Confirm New Password</label>
                                            <div class="relative">
                                                <input
                                                    id="mp-pw-confirm"
                                                    name="password_confirmation"
                                                    type="password"
                                                    autocomplete="new-password"
                                                    class="{{ $profilePwInputClass }}"
                                                />
                                                <button
                                                    type="button"
                                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-[#999999] transition hover:text-[#555555] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5E9E52] focus-visible:ring-offset-2 rounded-r-[10px]"
                                                    data-profile-pw-toggle="mp-pw-confirm"
                                                    aria-label="Show password"
                                                >
                                                    <svg class="h-5 w-5 icon-eye-off" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                    <svg class="hidden h-5 w-5 icon-eye" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-3 pt-2">
                                        <button type="button" class="rounded-[10px] bg-[#ececec] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#e0e0e0]">
                                            Cancel
                                        </button>
                                        <button type="submit" class="rounded-[10px] bg-[#5E9E52] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#548948]">
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div
                                id="profile-section-location"
                                class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#dddddd] sm:p-8 font-['Inter',ui-sans-serif,system-ui,sans-serif]"
                                data-profile-section="location"
                            >
                                <h4 class="mb-6 text-[20px] font-bold leading-tight text-[#333333]">Players Schedule</h4>
                                <form class="space-y-5" action="#" method="post" onsubmit="return false;">
                                    @csrf
                                    <div>
                                        <label for="mp-schedule-match" class="{{ $scheduleLabelClass }}">Match Players</label>
                                        <div class="relative">
                                            <select id="mp-schedule-match" name="schedule_match" class="{{ $scheduleSelectClass }}">
                                                @foreach ($mp['scheduleMatchOptions'] as $opt)
                                                    <option value="{{ $opt }}" @selected($mp['scheduleMatch'] === $opt)>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#888888]">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                        <div>
                                            <label for="mp-schedule-date" class="{{ $scheduleLabelClass }}">
                                                Date <span class="font-bold text-red-600">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-[#888888]" aria-hidden="true">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </span>
                                                <input
                                                    id="mp-schedule-date"
                                                    name="schedule_date"
                                                    type="date"
                                                    value="{{ $mp['scheduleDate'] }}"
                                                    class="{{ $scheduleInputIconClass }}"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label for="mp-schedule-time" class="{{ $scheduleLabelClass }}">
                                                Time <span class="font-bold text-red-600">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-[#888888]" aria-hidden="true">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </span>
                                                <input
                                                    id="mp-schedule-time"
                                                    name="schedule_time"
                                                    type="time"
                                                    value="{{ $mp['scheduleTime'] }}"
                                                    class="{{ $scheduleInputIconClass }}"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="mp-schedule-venue" class="{{ $scheduleLabelClass }}">Venue / Club</label>
                                        <input
                                            id="mp-schedule-venue"
                                            name="schedule_venue"
                                            type="text"
                                            value="{{ $mp['scheduleVenue'] }}"
                                            placeholder=""
                                            autocomplete="off"
                                            class="{{ $scheduleInputClass }}"
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-3 pt-2">
                                        <button type="button" class="rounded-[8px] bg-[#eeeeee] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#e4e4e4]">
                                            Cancel
                                        </button>
                                        <button type="submit" class="rounded-[8px] bg-[#5E9E52] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#548948]">
                                            Save Change
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div
                                id="profile-section-upload"
                                class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8 font-['Inter',ui-sans-serif,system-ui,sans-serif]"
                                data-profile-section="upload"
                            >
<<<<<<< HEAD
                                <h4 class="mb-2 text-[18px] font-bold text-[#212121] sm:text-[20px]">Upload Image</h4>
                                <p class="mb-5 text-[14px] text-[#757575]">Upload a JPG, PNG, or WebP profile photo up to 2MB.</p>
                                <form class="space-y-4" action="{{ route('player.profile.update') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="league_id" value="{{ $leagueId }}">
                                    <input type="hidden" name="group_card_id" value="{{ $groupCardId }}">
                                    <div>
                                        <label for="mp-avatar" class="{{ $profileLabelClass }}">Profile Image</label>
                                        <input id="mp-avatar" name="avatar" type="file" accept="image/*" class="{{ $profileInputClass }}">
                                    </div>
                                    <button type="submit" class="rounded-lg bg-[#62A351] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#569649] sm:text-[15px]">
                                        Upload Image
                                    </button>
                                </form>
=======
                                <h4 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Upload Match Images</h4>
                                <div class="mb-6">
                                    <label for="mp-upload-match" class="mb-1.5 block text-[14px] font-bold text-[#333333]">Match Players</label>
                                    <div class="relative">
                                        <select
                                            id="mp-upload-match"
                                            name="upload_match_players"
                                            class="w-full appearance-none rounded-[8px] border border-[#E0E0E0] bg-white px-3.5 py-2.5 pr-10 text-[14px] font-normal text-[#333333] shadow-sm focus:border-[#5E9E52] focus:outline-none focus:ring-1 focus:ring-[#5E9E52] min-h-[44px]"
                                        >
                                            @foreach ($mp['scheduleMatchOptions'] as $opt)
                                                <option value="{{ $opt }}" @selected($mp['scheduleMatch'] === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#757575]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                {{-- Step 1: gallery grid + wide upload trigger (first image) --}}
                                <div id="upload-match-step-1" data-upload-flow-step="1">
                                    <div class="mb-6 grid grid-cols-3 gap-3 sm:gap-4">
                                        @foreach ($mp['uploadMatchGallery'] as $src)
                                            <img
                                                src="{{ $src }}"
                                                alt=""
                                                class="aspect-square w-full rounded-[8px] object-cover ring-1 ring-black/[0.04]"
                                                loading="lazy"
                                                decoding="async"
                                                width="400"
                                                height="400"
                                            />
                                        @endforeach
                                    </div>
                                    <button
                                        type="button"
                                        id="upload-match-open-uploader"
                                        class="flex w-full items-center justify-center gap-2 rounded-[10px] border border-dashed border-[#E0E0E0] bg-[#F5F5F5] px-4 py-3.5 text-[14px] font-semibold text-[#78909C] transition hover:bg-[#eeeeee]"
                                    >
                                        <svg class="h-5 w-5 shrink-0 text-[#78909C]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        Upload Image
                                    </button>
                                </div>

                                {{-- Step 2: drag-drop + notes + Cancel / Add (after clicking Upload Image) --}}
                                <div id="upload-match-step-2" class="hidden space-y-5" data-upload-flow-step="2">
                                    <form class="space-y-5" action="#" method="post" onsubmit="return false;">
                                        @csrf
                                        <div>
                                            <label
                                                for="upload-match-files"
                                                class="flex cursor-pointer flex-col items-center justify-center rounded-[8px] border-2 border-dashed border-[#E0E0E0] bg-[#F5F5F5] px-4 py-12 text-center transition hover:bg-[#f0f0f0]"
                                                id="upload-match-dropzone"
                                            >
                                                <input
                                                    id="upload-match-files"
                                                    type="file"
                                                    name="match_images[]"
                                                    class="sr-only"
                                                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                                    multiple
                                                />
                                                <svg class="mb-3 h-10 w-10 text-[#757575]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <span class="text-[15px] font-medium text-[#333333]">Drag &amp; drop images here, or browse file</span>
                                                <span class="mt-2 text-[13px] text-[#757575]">JPG, PNG, WEBP - max 10 MB each</span>
                                            </label>
                                        </div>
                                        <div>
                                            <label for="upload-match-notes" class="mb-1.5 block text-[14px] font-bold text-[#333333]">
                                                Match Notes <span class="font-normal text-[#757575]">(Optional)</span>
                                            </label>
                                            <textarea
                                                id="upload-match-notes"
                                                name="match_notes"
                                                rows="4"
                                                placeholder="Type here..."
                                                class="w-full resize-y rounded-[8px] border border-[#E0E0E0] bg-white px-3.5 py-2.5 text-[14px] text-[#333333] placeholder:text-[#757575] shadow-sm focus:border-[#5E9E52] focus:outline-none focus:ring-1 focus:ring-[#5E9E52]"
                                            ></textarea>
                                        </div>
                                        <div class="flex flex-wrap gap-3 pt-1">
                                            <button
                                                type="button"
                                                id="upload-match-step2-cancel"
                                                class="rounded-[8px] bg-[#eeeeee] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#e4e4e4]"
                                            >
                                                Cancel
                                            </button>
                                            <button type="submit" class="rounded-[8px] bg-[#5E9E52] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#548948]">
                                                Add
                                            </button>
                                        </div>
                                    </form>
                                </div>
>>>>>>> 22ad779e40b36f59080947a4e4129e31d6484422
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            window.PLAYER_PROFILES = @json($playerProfiles);
        </script>
        <script>
            (function () {
                var tabs = document.querySelectorAll('[data-league-tab]');
                var panels = document.querySelectorAll('[data-league-panel]');
                var teamsView = document.getElementById('league-teams-view');
                var playerDash = document.getElementById('league-player-dashboard');
                var profiles = window.PLAYER_PROFILES || {};
                var inactiveCls =
                    'league-tab-btn rounded-lg border border-[#E0E0E0] bg-[#F1F8E9] px-4 py-2.5 text-[14px] font-semibold text-[#333333] transition-colors sm:px-5 sm:text-[15px] hover:bg-[#e8f5e4]';
                var activeCls =
                    'league-tab-btn rounded-lg px-4 py-2.5 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px] bg-[#5E9E52] text-white shadow-sm';

                function setAllTabsInactive() {
                    tabs.forEach(function (btn) {
                        btn.setAttribute('aria-selected', 'false');
                        btn.className = inactiveCls;
                    });
                }

                function activate(name) {
                    tabs.forEach(function (btn) {
                        var on = btn.getAttribute('data-league-tab') === name;
                        btn.setAttribute('aria-selected', on ? 'true' : 'false');
                        btn.className = on ? activeCls : inactiveCls;
                    });
                    panels.forEach(function (panel) {
                        var show = panel.getAttribute('data-league-panel') === name;
                        panel.classList.toggle('hidden', !show);
                    });
                }

                function exitPlayerView() {
                    if (!teamsView || !playerDash) return;
                    playerDash.classList.add('hidden');
                    playerDash.setAttribute('aria-hidden', 'true');
                    teamsView.classList.remove('hidden');
                }

                function fillDashboard(p) {
                    document.getElementById('pd-avatar').src = p.avatarUrl || '';
                    document.getElementById('pd-name').textContent = p.name || '';
                    document.getElementById('pd-subtitle').textContent = p.subtitle || '';
                    document.getElementById('pd-player-id').textContent = p.playerId || '';
                    document.getElementById('pd-division').textContent = p.division || '';
                    document.getElementById('pd-group').textContent = p.group || '';
                    document.getElementById('pd-seed').textContent = p.seed || '';
                    document.getElementById('pd-status').textContent = p.status || '';
                    document.getElementById('pd-joined').textContent = p.joined || '';
                    document.getElementById('pd-stat-matches').textContent = p.matches != null ? String(p.matches) : '';
                    document.getElementById('pd-stat-wins').textContent = p.wins != null ? String(p.wins) : '';
                    document.getElementById('pd-stat-losses').textContent = p.losses != null ? String(p.losses) : '';
                    document.getElementById('pd-stat-points').textContent = p.points != null ? String(p.points) : '';
                    document.getElementById('pd-pct-win').textContent = (p.winRate != null ? p.winRate : 0) + '%';
                    document.getElementById('pd-pct-game').textContent = (p.gamePct != null ? p.gamePct : 0) + '%';
                    document.getElementById('pd-pct-set').textContent = (p.setPct != null ? p.setPct : 0) + '%';
                    document.getElementById('pd-bar-win').style.width = (p.winRate || 0) + '%';
                    document.getElementById('pd-bar-game').style.width = (p.gamePct || 0) + '%';
                    document.getElementById('pd-bar-set').style.width = (p.setPct || 0) + '%';
                    document.getElementById('pd-full-name').textContent = p.fullName || '';
                    document.getElementById('pd-phone').textContent = p.phone || '';
                    document.getElementById('pd-email').textContent = p.email || '';
                    document.getElementById('pd-location').textContent = p.location || '';
                    document.getElementById('pd-dob').textContent = p.dob || '';
                    document.getElementById('pd-ntrp').textContent = p.ntrp || '';

                    var ul = document.getElementById('pd-recent-matches');
                    ul.innerHTML = '';
                    (p.recentMatches || []).forEach(function (m) {
                        var li = document.createElement('li');
                        li.className =
                            'flex flex-wrap items-start justify-between gap-3 py-3 first:pt-0 last:pb-0';
                        var win = (m.result || '').toLowerCase() === 'win';
                        li.innerHTML =
                            '<div class="min-w-0 flex-1">' +
                            '<p class="text-[16px] font-semibold text-[#2d4a2d]">vs ' +
                            (m.opponent || '') +
                            '</p>' +
                            '<p class="mt-0.5 text-[13px] text-[#6B7280]">' +
                            (m.context || '') +
                            '</p></div>' +
                            '<div class="flex shrink-0 flex-col items-end gap-1 text-right">' +
                            '<span class="text-[14px] font-semibold text-[#1F2937]">' +
                            (m.score || '') +
                            '</span>' +
                            '<span class="inline-flex rounded-full px-2.5 py-0.5 text-[12px] font-semibold ' +
                            (win ? 'bg-[#E8F5E9] text-[#2d4a2d]' : 'bg-[#FEE2E2] text-[#991B1B]') +
                            '">' +
                            (m.result || '') +
                            '</span></div>';
                        ul.appendChild(li);
                    });
                }

                function openPlayerProfile(key) {
                    var p = profiles[key];
                    if (!p || !teamsView || !playerDash) return;
                    fillDashboard(p);
                    teamsView.classList.add('hidden');
                    playerDash.classList.remove('hidden');
                    playerDash.setAttribute('aria-hidden', 'false');
                    setAllTabsInactive();
                }

                document.querySelectorAll('.js-open-player-profile').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        openPlayerProfile(btn.getAttribute('data-player-key'));
                    });
                });

                var backBtn = document.getElementById('league-player-back');
                if (backBtn) {
                    backBtn.addEventListener('click', function () {
                        exitPlayerView();
                        activate('players');
                    });
                }

                tabs.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        exitPlayerView();
                        activate(btn.getAttribute('data-league-tab'));
                    });
                });

            })();
        </script>
        <script>
            (function () {
                var nav = document.getElementById('profile-side-nav');
                if (!nav) return;
                var activeNav =
                    'w-full rounded-[10px] bg-[#5E9E52] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]';
                var inactiveNav =
                    'w-full rounded-[10px] border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#333333] transition-colors hover:bg-[#fafafa] sm:text-[15px]';
                var buttons = nav.querySelectorAll('[data-profile-section]');
                var panels = document.querySelectorAll('.profile-section-panel');

                function resetUploadMatchFlow() {
                    var s1 = document.getElementById('upload-match-step-1');
                    var s2 = document.getElementById('upload-match-step-2');
                    if (s1) s1.classList.remove('hidden');
                    if (s2) s2.classList.add('hidden');
                    var fi = document.getElementById('upload-match-files');
                    if (fi) fi.value = '';
                    var notes = document.getElementById('upload-match-notes');
                    if (notes) notes.value = '';
                }

                function showSection(sectionId) {
                    panels.forEach(function (el) {
                        var match = el.getAttribute('data-profile-section') === sectionId;
                        el.classList.toggle('hidden', !match);
                    });
                    buttons.forEach(function (btn) {
                        var on = btn.getAttribute('data-profile-section') === sectionId;
                        btn.className = on ? activeNav : inactiveNav;
                    });
                    if (sectionId === 'upload') {
                        resetUploadMatchFlow();
                    }
                }

                buttons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        showSection(btn.getAttribute('data-profile-section'));
                    });
                });

                document.querySelectorAll('[data-profile-jump-upload]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        showSection('upload');
                        document.getElementById('mp-avatar')?.click();
                    });
                });
            })();
        </script>
        <script>
            (function () {
                var openBtn = document.getElementById('upload-match-open-uploader');
                var step1 = document.getElementById('upload-match-step-1');
                var step2 = document.getElementById('upload-match-step-2');
                var cancelBtn = document.getElementById('upload-match-step2-cancel');

                function showUploadStep2() {
                    if (!step1 || !step2) return;
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                }

                function showUploadStep1() {
                    if (!step1 || !step2) return;
                    step2.classList.add('hidden');
                    step1.classList.remove('hidden');
                    var fi = document.getElementById('upload-match-files');
                    if (fi) fi.value = '';
                    var notes = document.getElementById('upload-match-notes');
                    if (notes) notes.value = '';
                }

                if (openBtn) openBtn.addEventListener('click', showUploadStep2);
                if (cancelBtn) cancelBtn.addEventListener('click', showUploadStep1);

                var dz = document.getElementById('upload-match-dropzone');
                var fileInput = document.getElementById('upload-match-files');
                if (dz && fileInput) {
                    ;['dragenter', 'dragover'].forEach(function (ev) {
                        dz.addEventListener(ev, function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                        });
                    });
                    dz.addEventListener('drop', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var dt = e.dataTransfer;
                        if (!dt || !dt.files || !dt.files.length) return;
                        try {
                            fileInput.files = dt.files;
                        } catch (err) {}
                    });
                }
            })();
        </script>
        <script>
            (function () {
                document.querySelectorAll('[data-profile-pw-toggle]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var id = btn.getAttribute('data-profile-pw-toggle');
                        var input = document.getElementById(id);
                        if (!input) return;
                        input.type = input.type === 'password' ? 'text' : 'password';
                        var revealed = input.type === 'text';
                        btn.setAttribute('aria-label', revealed ? 'Hide password' : 'Show password');
                        var off = btn.querySelector('.icon-eye-off');
                        var on = btn.querySelector('.icon-eye');
                        if (off) off.classList.toggle('hidden', revealed);
                        if (on) on.classList.toggle('hidden', !revealed);
                    });
                });
            })();
        </script>
        <script>
            (function () {
                function closeScheduleMenu(wrap) {
                    if (!wrap) return;
                    wrap.classList.remove('is-open');
                    var trigger = wrap.querySelector('[data-schedule-trigger]');
                    var dropdown = wrap.querySelector('[data-schedule-dropdown]');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                    if (dropdown) {
                        dropdown.classList.add('pointer-events-none', 'invisible', 'opacity-0');
                        dropdown.classList.remove('pointer-events-auto', 'visible', 'opacity-100');
                        dropdown.setAttribute('aria-hidden', 'true');
                    }
                }

                function closeAllScheduleMenus() {
                    document.querySelectorAll('[data-schedule-menu]').forEach(function (wrap) {
                        closeScheduleMenu(wrap);
                    });
                }

                function openScheduleMenu(wrap) {
                    closeAllScheduleMenus();
                    wrap.classList.add('is-open');
                    var trigger = wrap.querySelector('[data-schedule-trigger]');
                    var dropdown = wrap.querySelector('[data-schedule-dropdown]');
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                    if (dropdown) {
                        dropdown.classList.remove('pointer-events-none', 'invisible', 'opacity-0');
                        dropdown.classList.add('pointer-events-auto', 'visible', 'opacity-100');
                        dropdown.setAttribute('aria-hidden', 'false');
                    }
                }

                document.querySelectorAll('[data-schedule-menu]').forEach(function (wrap) {
                    var trigger = wrap.querySelector('[data-schedule-trigger]');
                    var dropdown = wrap.querySelector('[data-schedule-dropdown]');
                    if (!trigger || !dropdown) {
                        return;
                    }

                    trigger.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (wrap.classList.contains('is-open')) {
                            closeScheduleMenu(wrap);
                        } else {
                            openScheduleMenu(wrap);
                        }
                    });

                    dropdown.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });

                    dropdown.querySelectorAll('[role="menuitem"]').forEach(function (item) {
                        item.addEventListener('click', function () {
                            closeScheduleMenu(wrap);
                        });
                    });
                });

                document.addEventListener('click', function () {
                    closeAllScheduleMenus();
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeAllScheduleMenus();
                    }
                });
            })();
        </script>
    @endpush
@endsection
