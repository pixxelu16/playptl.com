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
                        <span class="mx-1.5 sm:mx-2">{{ $statGroups }} Subgroups</span>
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $statSeasonLabel }} {{ $statSeasonRange }}</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="bg-[#e8f5e9] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#2d4a2d] antialiased" aria-labelledby="league-detail-tabs-heading">
            <h2 id="league-detail-tabs-heading" class="sr-only">League section tabs</h2>
            <div class="mx-auto max-w-[1400px] px-5 py-10 sm:px-8 sm:py-12 lg:px-14 lg:py-16">
                <div id="league-tablist" class="mb-8 flex flex-wrap gap-2 sm:mb-10 sm:gap-3" role="tablist" aria-label="League section">
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
                </div>

                <div id="panel-players" role="tabpanel" aria-labelledby="tab-players" data-league-panel="players" class="league-tab-panel">
                    <div id="league-teams-view">
                        @if (empty($playerGroups))
                            <div class="rounded-xl bg-white p-6 text-center text-[15px] font-semibold text-[#5a8f5a] shadow-[0_2px_12px_rgba(45,74,45,0.08)] ring-1 ring-[#e1f0e1]">
                                Players will appear here once they are assigned to subgroups.
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
                                            <dt class="text-[14px] text-[#6B7280]">Subgroup</dt>
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
                    @once
                        @push('styles')
                            @include('partials.match-scoreboard-styles')
                        @endpush
                    @endonce
                    @if (empty($scheduleDays))
                        <p class="mt-2 rounded-[10px] bg-white px-5 py-10 text-center text-[15px] font-medium leading-relaxed text-[#757575] shadow-[0_1px_4px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04] sm:px-8 sm:text-[16px]">
                            No matches are scheduled for this division yet. Check back later.
                        </p>
                    @endif
                    @foreach ($scheduleDays as $day)
                        <h3 class="mb-4 mt-10 text-[15px] font-bold uppercase leading-tight tracking-[0.06em] text-[#212121] first:mt-0 sm:text-[16px]">{{ $day['dateLabel'] }}</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 lg:gap-6">
                            @foreach ($day['matches'] as $match)
                                @php
                                    $scheduleParticipantIds = $match['participantUserIds'] ?? [];
                                    $scheduleViewerId = auth()->id();
                                    $showScheduleMatchMenu = $scheduleViewerId !== null
                                        && $scheduleParticipantIds !== []
                                        && in_array((int) $scheduleViewerId, array_map('intval', $scheduleParticipantIds), true);
                                    $scheduleMenuId = 'schedule-menu-'.$loop->parent->iteration.'-'.$loop->iteration;
                                @endphp
                                <article class="relative overflow-visible rounded-[10px] bg-white p-4 shadow-[0_1px_4px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04] sm:p-5">
                                    @if (($match['finished'] ?? false) === true)
                                        @include('partials.match-scoreboard', [
                                            'score' => $match['score'] ?? '',
                                            'homeName' => $match['leftName'],
                                            'awayName' => $match['rightName'],
                                            'homeMeta' => $match['leftMeta'],
                                            'awayMeta' => $match['rightMeta'],
                                            'homeSideWon' => $match['homeSideWon'] ?? null,
                                        ])
                                    @else
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-start sm:gap-3">
                                            <div class="min-w-0 text-left">
                                                <span class="mb-1 inline-block rounded bg-[#E8F5E9] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#2E7D32]">Home</span>
                                                <p class="text-[16px] font-bold leading-snug text-[#212121]">{{ $match['leftName'] }}</p>
                                                <p class="mt-0.5 text-[13px] leading-snug text-[#757575]">{{ $match['leftMeta'] }}</p>
                                            </div>
                                            <div class="flex flex-col items-center justify-start gap-1.5 py-1 sm:min-w-[100px]">
                                                <span class="text-[13px] font-bold uppercase tracking-wide text-[#212121]">VS</span>
                                                <div class="rounded-full bg-[#E8F5E9] px-3 py-1.5 text-center">
                                                    <span class="text-[13px] font-semibold text-[#2E7D32] sm:text-[14px]">Pending</span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 text-right">
                                                <span class="mb-1 inline-block rounded bg-[#E8F5E9] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#2E7D32]">Away</span>
                                                <p class="text-[16px] font-bold leading-snug text-[#212121]">{{ $match['rightName'] }}</p>
                                                <p class="mt-0.5 text-[13px] leading-snug text-[#757575]">{{ $match['rightMeta'] }}</p>
                                            </div>
                                        </div>
                                    @endif
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
                                        @if ($showScheduleMatchMenu)
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
                                                            <a href="{{ (! empty($match['groupMatchId'] ?? null)) ? route('player.profile.location', ['match' => $match['groupMatchId']]) : route('player.profile.location') }}" role="menuitem" class="block w-full whitespace-nowrap px-4 py-3 text-left text-[14px] font-semibold leading-snug text-[#4B5563] no-underline transition-colors hover:bg-[#F9FAFB] focus-visible:bg-[#F9FAFB] focus-visible:outline-none">
                                                                Add Location
                                                            </a>
                                                            <a href="{{ (! empty($match['groupMatchId'] ?? null)) ? route('player.profile.upload', ['match' => $match['groupMatchId']]) : route('player.profile.upload') }}" role="menuitem" class="block w-full whitespace-nowrap px-4 py-3 text-left text-[14px] font-semibold leading-snug text-[#4B5563] no-underline transition-colors hover:bg-[#F9FAFB] focus-visible:bg-[#F9FAFB] focus-visible:outline-none">
                                                                Upload Image
                                                            </a>
                                                        </div>
                                                        <svg class="-ml-px h-[16px] w-[6px] shrink-0 self-center text-[#E0E0E0]" viewBox="0 0 6 16" aria-hidden="true">
                                                            <path d="M0 0 L6 8 L0 16 Z" fill="#FFFFFF" stroke="#E0E0E0" stroke-width="1" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
                                1 · PF
                            </button>
                            <button type="button" class="rounded-full border border-[#E0E0E0] bg-white px-3.5 py-1.5 text-[13px] font-semibold text-[#424242] shadow-sm transition-colors hover:bg-[#FAFAFA] sm:text-[14px]">
                                2 · PA
                            </button>
                            <button type="button" class="rounded-full border border-[#E0E0E0] bg-white px-3.5 py-1.5 text-[13px] font-semibold text-[#424242] shadow-sm transition-colors hover:bg-[#FAFAFA] sm:text-[14px]">
                                3 · Game %
                            </button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[10px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.06]">
                        <div class="overflow-x-auto p-[5px]">
                            <table class="w-full min-w-[920px] border-collapse text-left">
                                <thead>
                                    <tr class="bg-[#e1f0e1] rounded">
                                        <th scope="col" class="px-4 py-3.5 text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Rank</th>
                                        <th scope="col" class="px-4 py-3.5 text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Player Name</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Matches</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Wins</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]">Losses</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]" title="Points For">PF</th>
                                        <th scope="col" class="px-4 py-3.5 text-center text-[13px] font-bold text-[#374151] sm:px-5 sm:text-[14px]" title="Points Against">PA</th>
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
                                            <td class="whitespace-nowrap px-4 py-3.5 text-center align-middle text-[14px] font-semibold tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">{{ $row['points'] ?? ($row['pointsFor'] ?? 0) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-center align-middle text-[14px] tabular-nums text-[#424242] sm:px-5 sm:text-[17px]">{{ $row['pointsAgainst'] ?? 0 }}</td>
                                            <td class="whitespace-nowrap px-4 py-3.5 text-right align-middle text-[14px] font-semibold tabular-nums text-[#66BB6A] sm:pl-5 sm:pr-6 sm:text-[17px]">{{ $row['gamePct'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="panel-playoffs" role="tabpanel" aria-labelledby="tab-playoffs" data-league-panel="playoffs" class="league-tab-panel hidden">
                    @once
                        @push('styles')
                            @include('partials.playoffs-bracket-styles')
                        @endpush
                    @endonce
                    @include('partials.playoffs-bracket-public')
                </div>
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
