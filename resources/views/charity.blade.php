@extends('layouts.website')

@section('nav_active', 'charity')

@section('title', 'Beyond the Baseline | Charity | Premier Tennis League')
@section('meta_description', 'Premier Tennis League charity partners — every match gives back. Season 2026.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    @php
        $charityImpactCards = [
            ['target' => 184000, 'format' => 'usdK', 'initial' => '$0', 'label' => 'Total Raised', 'sub' => 'Across All Seasons'],
            ['target' => 1240, 'format' => 'intComma', 'initial' => '0', 'label' => 'Youth Coached', 'sub' => 'Free Programs Funded'],
            ['target' => 62, 'format' => 'int', 'initial' => '0', 'label' => 'Scholarships', 'sub' => 'Given To Students'],
            ['target' => 18, 'format' => 'int', 'initial' => '0', 'label' => 'School Programs', 'sub' => 'Active This Season'],
        ];
    @endphp
    <main>
        <section class="relative flex h-[685px] min-h-[685px] flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('public/frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
            </video>

            <div class="pointer-events-none absolute inset-0 z-[1] bg-[rgba(0,0,0,0.6)]" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col items-start justify-center px-5 py-12 text-left sm:py-16">
                <header class="w-full max-w-5xl text-left">
                    <nav class="mb-6 flex flex-wrap items-center justify-start gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#C1D72E] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#C1D72E] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#C1D72E]">Charity</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(4.5rem,11vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">BEYOND THE </span><span class="text-[#C1D72E]">BASELINE</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[18px] font-medium leading-relaxed text-white sm:mt-10">
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">6 Charity Partners</span>
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">Season 2026</span>
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">Every match gives back</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-16" aria-labelledby="charity-impact-heading">
            <h2 id="charity-impact-heading" class="sr-only">Charity impact</h2>
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                    @foreach ($charityImpactCards as $card)
                        <article class="charity-impact-card relative overflow-hidden rounded-[14px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.05)] ring-1 ring-black/[0.04] sm:p-7 lg:p-8">
                            <div class="pointer-events-none absolute inset-y-0 right-0 z-0 w-[min(65%,200px)] opacity-90" aria-hidden="true">
                                <div class="absolute inset-0 bg-[radial-gradient(ellipse_115%_95%_at_100%_50%,rgba(88,148,66,0.2)_0%,rgba(88,148,66,0.08)_35%,transparent_60%)]"></div>
                                <div class="absolute inset-0 bg-[radial-gradient(ellipse_90%_75%_at_95%_45%,rgba(88,148,66,0.12)_0%,transparent_50%)]"></div>
                            </div>
                            <div class="relative z-[1] text-left">
                                <p
                                    class="text-[clamp(2rem,4.5vw,2.75rem)] font-bold tabular-nums leading-none tracking-tight text-[#589442]"
                                    data-counter
                                    data-target="{{ $card['target'] }}"
                                    data-format="{{ $card['format'] }}"
                                >
                                    {{ $card['initial'] }}
                                </p>
                                <p class="mt-3 text-[15px] font-semibold leading-snug text-[#589442]">{{ $card['label'] }}</p>
                                <p class="mt-1.5 text-[13px] font-normal leading-snug text-[#607D8B] sm:text-[14px]">{{ $card['sub'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-14" aria-labelledby="charity-goal-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="charity-goal-heading" class="sr-only">Season fundraising goal</h2>
                <div class="relative overflow-hidden rounded-[15px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8 lg:p-10">
                    <div class="pointer-events-none absolute inset-y-0 right-0 z-0 w-[min(42%,280px)] opacity-95" aria-hidden="true">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_115%_95%_at_100%_50%,rgba(96,160,75,0.18)_0%,rgba(96,160,75,0.06)_38%,transparent_62%)]"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_90%_78%_at_96%_48%,rgba(96,160,75,0.1)_0%,transparent_52%)]"></div>
                    </div>

                    <div class="relative z-[1] flex flex-col items-stretch gap-8 lg:flex-row lg:items-center lg:justify-between lg:gap-10">
                        <div class="min-w-0 shrink-0 text-left lg:max-w-[280px]">
                            <p class="text-[13px] font-medium leading-snug text-[#60a04b] sm:text-[14px]">Season 2026 Goal</p>
                            <p class="mt-1.5 text-[clamp(1.75rem,4vw,2.35rem)] font-bold tabular-nums leading-tight tracking-tight text-[#212121]">$66,200</p>
                            <p class="mt-2 text-[13px] font-normal leading-snug text-[#757575] sm:text-[14px]">Raised Of $100,000 Target</p>
                        </div>

                        <div class="min-w-0 flex-1 lg:mx-4 lg:max-w-none">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-[12px] font-normal leading-snug text-[#757575] sm:text-[13px]">
                                <span>$0</span>
                                <span class="font-medium">66% Reached</span>
                                <span>$100K</span>
                            </div>
                            <div
                                class="h-3 w-full overflow-hidden rounded-full bg-[#E8EDE8]"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="100000"
                                aria-valuenow="66200"
                                aria-label="Fundraising progress"
                            >
                                <div class="h-full w-[66%] rounded-full bg-[#60a04b] transition-[width] duration-500"></div>
                            </div>
                        </div>

                        <div class="flex shrink-0 justify-start lg:justify-end">
                            <a
                                href="#"
                                class="inline-flex min-h-[48px] items-center justify-center rounded-lg bg-[#60a04b] px-8 py-3 text-[15px] font-bold text-white shadow-sm transition-opacity hover:opacity-95 sm:min-h-[52px] sm:px-10 sm:text-[16px]"
                            >
                                Donate Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-14" aria-labelledby="charity-donate-cta-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="charity-donate-cta-heading" class="sr-only">Make a donation</h2>
                <div
                    id="charity-donate-widget"
                    class="flex flex-col gap-10 rounded-[14px] bg-[#2D5A27] px-6 py-10 shadow-[0_4px_24px_rgba(45,90,39,0.2)] sm:px-10 sm:py-12 lg:flex-row lg:items-center lg:justify-between lg:gap-12 lg:px-14 lg:py-12 xl:px-16"
                >
                    <div class="min-w-0 max-w-xl shrink-0 lg:flex-1">
                        <p class="text-[clamp(1.25rem,3.5vw,2rem)] font-bold leading-[1.25] text-white lg:text-[28px] xl:text-[32px]">
                            Every Match Plays For <span class="text-[#A4D433]">Something Bigger</span>
                        </p>
                        <p class="mt-4 max-w-xl text-[14px] font-normal leading-[1.5] text-white sm:text-[15px] lg:text-[16px]">
                            All Divisions Now Open. Voyagers, Challengers, Warriors And Mixed Doubles Brackets Accepting Entries Until May 15.
                        </p>
                    </div>

                    <div class="w-full min-w-0 shrink-0 lg:max-w-[520px] lg:flex-1">
                        <div class="mb-4 grid grid-cols-4 gap-2 sm:gap-3" role="group" aria-label="Choose amount">
                            <button
                                type="button"
                                data-donate-preset="10"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]"
                            >
                                $10
                            </button>
                            <button
                                type="button"
                                data-donate-preset="25"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white bg-white px-1 text-[13px] font-bold text-[#A4D433] sm:px-2 sm:text-[14px]"
                            >
                                $25
                            </button>
                            <button
                                type="button"
                                data-donate-preset="50"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]"
                            >
                                $50
                            </button>
                            <button
                                type="button"
                                data-donate-preset="100"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]"
                            >
                                $100
                            </button>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
                            <label class="sr-only" for="charity-donate-other">Other amount</label>
                            <input
                                id="charity-donate-other"
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                placeholder="$ Other amount"
                                class="min-h-[48px] w-full flex-1 rounded-md border border-white/20 bg-[#3D6B37]/90 px-4 py-3 text-[15px] text-white placeholder:text-[#B8D4B4] outline-none ring-0 transition-colors placeholder:font-normal focus:border-white/40 focus:bg-[#3D6B37]"
                            />
                            <button
                                id="charity-donate-submit"
                                type="button"
                                class="inline-flex min-h-[48px] shrink-0 items-center justify-center whitespace-nowrap rounded-lg bg-white px-6 py-3 text-[15px] font-bold text-[#A4D433] shadow-sm transition-opacity hover:opacity-95 sm:min-h-[48px] sm:px-8 sm:text-[16px]"
                            >
                                Donate $25
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @php
            $charityPartnerCards = [
                [
                    'cat' => 'Youth',
                    'catClass' => 'text-[#2E7D32]',
                    'iconBg' => 'bg-[#E8F5E9]',
                    'iconColor' => 'text-[#2E7D32]',
                    'title' => 'Junior Tennis Fund',
                    'desc' => 'Equipment, coaching stipends, and court time for junior players across partner clubs.',
                    'raised' => '$42,000 raised',
                ],
                [
                    'cat' => 'Education',
                    'catClass' => 'text-[#1565C0]',
                    'iconBg' => 'bg-[#E3F2FD]',
                    'iconColor' => 'text-[#1565C0]',
                    'title' => 'Scholarship Circle',
                    'desc' => 'Academic scholarships tied to sportsmanship and attendance targets.',
                    'raised' => '$28,500 raised',
                ],
                [
                    'cat' => 'Wellness',
                    'catClass' => 'text-[#6D4C41]',
                    'iconBg' => 'bg-[#EFEBE9]',
                    'iconColor' => 'text-[#6D4C41]',
                    'title' => 'Mental Wellness Courtside',
                    'desc' => 'Mindfulness sessions and counselor access on tournament weekends.',
                    'raised' => '$19,200 raised',
                ],
                [
                    'cat' => 'Community',
                    'catClass' => 'text-[#7B1FA2]',
                    'iconBg' => 'bg-[#F3E5F5]',
                    'iconColor' => 'text-[#7B1FA2]',
                    'title' => 'Active Communities',
                    'desc' => 'Neighbourhood courts opened for free drop-in play and outreach clinics.',
                    'raised' => '$33,750 raised',
                ],
            ];
            $charityPrograms = [
                ['title' => 'After-school tennis clinic', 'meta' => 'Mon, Wed, Fri • 4:00–6:00 PM • Highland CC', 'stat' => '240', 'statLabel' => 'youth enrolled', 'pct' => 78],
                ['title' => 'Scholarship grants', 'meta' => 'Rolling • Applications reviewed weekly', 'stat' => '62', 'statLabel' => 'awarded', 'pct' => 88],
                ['title' => 'Junior ladder league', 'meta' => 'Saturdays • 9:00 AM • Voyagers venue', 'stat' => '186', 'statLabel' => 'enrolled', 'pct' => 92],
                ['title' => 'Wellness workshops', 'meta' => 'Monthly • Virtual + in-person', 'stat' => '340', 'statLabel' => 'attended', 'pct' => 65],
                ['title' => 'Community outreach', 'meta' => 'Seasonal • Partner schools', 'stat' => '128', 'statLabel' => 'families', 'pct' => 71],
            ];
        @endphp

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-16" aria-labelledby="charity-partners-programs-heading">
            <h2 id="charity-partners-programs-heading" class="sr-only">Charity partners and active programs</h2>
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10 lg:items-start">
                    <div class="rounded-[12px] border border-[#E0E0E0] bg-white p-5 shadow-[0_1px_8px_rgba(0,0,0,0.04)] sm:p-6 lg:p-7">
                        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-[18px] font-bold leading-tight text-[#000000] sm:text-[19px]">Charity Partners</h3>
                            <span class="rounded-full bg-[#E8F5E9] px-3 py-1 text-[12px] font-semibold text-[#2E7D32] sm:text-[13px]">6 active</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ($charityPartnerCards as $pc)
                                <article class="flex flex-col rounded-[10px] border border-[#E5E7EB] bg-[#F9FAFB] p-4 sm:p-5">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $pc['iconBg'] }} {{ $pc['iconColor'] }}" aria-hidden="true">
                                            @if ($loop->iteration === 1)
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            @elseif ($loop->iteration === 2)
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
                                            @elseif ($loop->iteration === 3)
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-semibold uppercase tracking-wide {{ $pc['catClass'] }}">{{ $pc['cat'] }}</p>
                                            <p class="mt-1 text-[15px] font-bold leading-snug text-[#000000]">{{ $pc['title'] }}</p>
                                            <p class="mt-2 text-[13px] leading-relaxed text-[#666666] sm:text-[14px]">{{ $pc['desc'] }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 rounded-lg bg-[#E8F5E9] py-2.5 text-center text-[13px] font-semibold text-[#2E7D32] sm:text-[14px]">{{ $pc['raised'] }}</div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[12px] border border-[#E0E0E0] bg-white p-5 shadow-[0_1px_8px_rgba(0,0,0,0.04)] sm:p-6 lg:p-7">
                        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-[18px] font-bold leading-tight text-[#000000] sm:text-[19px]">Active Programs</h3>
                            <span class="rounded-full bg-[#E8F5E9] px-3 py-1 text-[12px] font-semibold text-[#2E7D32] sm:text-[13px]">18 programs</span>
                        </div>
                        <ul class="space-y-4" role="list">
                            @foreach ($charityPrograms as $prog)
                                <li class="rounded-[10px] border border-[#E5E7EB] bg-white p-4 sm:p-5">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
                                        <div class="flex min-w-0 flex-1 items-start gap-3 sm:items-center sm:gap-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-[#4CAF50]" aria-hidden="true">
                                                @if ($loop->iteration === 1)
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                @elseif ($loop->iteration === 2)
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z" /></svg>
                                                @elseif ($loop->iteration === 3)
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-3.197a1 1 0 00-1.414 0l-3.197 3.197M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                @elseif ($loop->iteration === 4)
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                                @else
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                @endif
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[15px] font-bold leading-snug text-[#000000]">{{ $prog['title'] }}</p>
                                                <p class="mt-1 text-[13px] leading-snug text-[#666666] sm:text-[14px]">{{ $prog['meta'] }}</p>
                                            </div>
                                        </div>
                                        <div class="flex w-full shrink-0 flex-col pl-[60px] sm:w-[120px] sm:pl-0 sm:items-end sm:text-right">
                                            <p class="text-[22px] font-bold tabular-nums leading-none text-[#000000]">{{ $prog['stat'] }}</p>
                                            <p class="mt-1 text-[12px] leading-tight text-[#666666]">{{ $prog['statLabel'] }}</p>
                                            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-[#EEEEEE] sm:ml-auto sm:max-w-[120px]">
                                                <div class="h-full rounded-full bg-[#4CAF50]" style="width: {{ $prog['pct'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        @php
            $charityUpcomingEvents = [
                [
                    'image' => 'public/frontend/images/front-view-couple-tennis-court 1.png',
                    'date' => 'Jul 20, 2026',
                    'title' => 'Charity doubles tournament',
                    'meta' => 'Highland Country Club · All day',
                    'target' => '$15,000',
                ],
                [
                    'image' => 'public/frontend/images/portrait-beautiful-woman-playing-tennis-outdoor 1.png',
                    'date' => 'Jul 20, 2026',
                    'title' => 'Junior scholarship gala dinner',
                    'meta' => 'Chandigarh Golf Club · 7:00 PM',
                    'target' => '$15,000',
                ],
                [
                    'image' => 'public/frontend/images/tennis-player-serving-hard 1.png',
                    'date' => 'Jul 20, 2026',
                    'title' => 'Season-end charity match',
                    'meta' => 'Westside Rec Center · 4:00 PM',
                    'target' => '$15,000',
                ],
            ];
        @endphp

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-14" aria-labelledby="charity-upcoming-events-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="charity-upcoming-events-heading" class="mb-8 text-left text-[clamp(1.125rem,2.5vw,1.5rem)] font-bold uppercase tracking-[0.08em] text-[#1B3022] sm:mb-10">
                    Upcoming charity events
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-6 lg:gap-8">
                    @foreach ($charityUpcomingEvents as $ev)
                        <article class="flex flex-col overflow-hidden rounded-[14px] bg-white shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05]">
                            <div class="aspect-[16/10] w-full overflow-hidden rounded-t-[14px] bg-[#F5F5F5]">
                                <img
                                    src="{{ asset($ev['image']) }}"
                                    alt=""
                                    class="h-full w-full object-cover"
                                    width="640"
                                    height="400"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                            <div class="flex flex-1 flex-col px-5 pb-5 pt-5 sm:px-6 sm:pb-6 sm:pt-6">
                                <p class="flex items-center gap-2 text-[13px] leading-none text-[#666666] sm:text-[14px]">
                                    <span class="inline-block h-1 w-1 shrink-0 rounded-full bg-[#666666]" aria-hidden="true"></span>
                                    <span>{{ $ev['date'] }}</span>
                                </p>
                                <h3 class="mt-3 text-[16px] font-bold leading-snug text-[#000000] sm:text-[17px]">{{ $ev['title'] }}</h3>
                                <p class="mt-2 text-[13px] leading-relaxed text-[#666666] sm:text-[14px]">{{ $ev['meta'] }}</p>
                                <p class="mt-5 inline-flex self-start rounded border border-[#4CAF50] bg-[#F1F8F1] px-3 py-1.5 text-[12px] font-semibold leading-none text-[#4CAF50] sm:mt-6 sm:px-3.5 sm:text-[13px]">
                                    Target: {{ $ev['target'] }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        @php
            $topVolunteers = [
                ['name' => 'Arjun Kumar', 'role' => 'Community', 'stats' => '48 hrs volunteered · 12 sessions'],
                ['name' => 'Devika Mehta', 'role' => 'Event coordinator', 'stats' => '36 hrs volunteered · 9 events'],
                ['name' => 'Neha Kapoor', 'role' => 'Scholarship mentor', 'stats' => '24 hrs volunteered · 6 sessions'],
                ['name' => 'Vikram Sharma', 'role' => 'Equipment drive lead', 'stats' => '30 hrs volunteered · 8 sessions'],
            ];
        @endphp

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-14" aria-labelledby="charity-top-volunteers-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4 sm:mb-10">
                    <h2 id="charity-top-volunteers-heading" class="text-left text-[clamp(1.125rem,2.5vw,1.375rem)] font-bold uppercase tracking-[0.06em] text-[#1B2B1B]">
                        Top volunteers
                    </h2>
                    <span class="rounded-md border border-[#689F38] bg-[#F1F8E9] px-3 py-1.5 text-[12px] font-semibold text-[#689F38] sm:text-[13px]">Season 2026</span>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                    @foreach ($topVolunteers as $vol)
                        <article class="rounded-[10px] bg-white px-5 py-6 text-center shadow-[0_1px_10px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04] sm:px-6 sm:py-7">
                            <img
                                src="https://ui-avatars.com/api/?name={{ rawurlencode($vol['name']) }}&size=128&background=E8F5E9&color=1B2B1B&bold=true"
                                alt=""
                                class="mx-auto h-20 w-20 rounded-full object-cover ring-2 ring-white sm:h-[88px] sm:w-[88px]"
                                width="88"
                                height="88"
                                loading="lazy"
                                decoding="async"
                            />
                            <p class="mt-4 text-[16px] font-bold leading-snug text-[#1B2B1B]">{{ $vol['name'] }}</p>
                            <p class="mt-2 text-[14px] font-normal leading-snug text-[#689F38]">{{ $vol['role'] }}</p>
                            <p class="mt-3 text-[13px] font-normal leading-relaxed text-[#757575]">{{ $vol['stats'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            (function () {
                var root = document.getElementById('charity-donate-widget');
                if (!root) return;
                var presets = root.querySelectorAll('[data-donate-preset]');
                var other = document.getElementById('charity-donate-other');
                var submit = document.getElementById('charity-donate-submit');
                var inactiveCls =
                    'donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]';
                var activeCls =
                    'donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white bg-white px-1 text-[13px] font-bold text-[#A4D433] sm:px-2 sm:text-[14px]';
                var selected = 25;

                function parseMoney(str) {
                    var n = parseFloat(String(str).replace(/[^0-9.]/g, ''), 10);
                    return isNaN(n) ? null : n;
                }

                function updateSubmitLabel(amount) {
                    if (!submit || amount == null || amount <= 0) return;
                    var rounded = Math.round(amount * 100) / 100;
                    submit.textContent = 'Donate $' + rounded;
                }

                function setPresetActive(amt) {
                    selected = amt;
                    presets.forEach(function (btn) {
                        var v = parseInt(btn.getAttribute('data-donate-preset'), 10);
                        btn.className = v === amt ? activeCls : inactiveCls;
                    });
                    updateSubmitLabel(amt);
                }

                presets.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (other) other.value = '';
                        setPresetActive(parseInt(btn.getAttribute('data-donate-preset'), 10));
                    });
                });

                if (other) {
                    other.addEventListener('input', function () {
                        var val = parseMoney(other.value);
                        if (val != null && val > 0) {
                            presets.forEach(function (btn) {
                                btn.className = inactiveCls;
                            });
                            updateSubmitLabel(val);
                        } else if (!other.value.trim()) {
                            setPresetActive(selected);
                        }
                    });
                    other.addEventListener('focus', function () {
                        presets.forEach(function (btn) {
                            btn.className = inactiveCls;
                        });
                    });
                    other.addEventListener('blur', function () {
                        if (!other.value.trim()) {
                            setPresetActive(selected);
                        }
                    });
                }

                setPresetActive(25);
            })();
        </script>
    @endpush
@endsection
