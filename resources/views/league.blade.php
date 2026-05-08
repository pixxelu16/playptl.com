@extends('layouts.website')

@section('nav_active', 'league')

@section('title', $pageTitle ?? 'Premier Tennis League | League')
@section('meta_description', $pageMetaDescription ?? 'League season overview — divisions, schedule window, and player registration for Premier Tennis League.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="relative flex h-[685px] min-h-[685px] flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
            </video>

            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

            <div class="pointer-events-none absolute right-0 top-0 z-[2] h-[min(45vh,420px)] w-[min(55vw,520px)] opacity-[0.14]" aria-hidden="true">
                <div class="absolute right-[8%] top-[12%] h-24 w-24 rounded-full bg-[#e8d94a] blur-2xl"></div>
                <div class="absolute right-[22%] top-[28%] h-16 w-16 rounded-full bg-[#c9b832] blur-xl"></div>
                <div class="absolute right-[5%] top-[38%] h-10 w-10 rounded-full bg-[#f5e85a] blur-lg"></div>
            </div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-24 pt-36 sm:px-8 sm:pb-28 sm:pt-40 lg:px-14 lg:pb-32 lg:pt-44">
                <header class="max-w-4xl">
                    <nav class="mb-6 text-[11px] font-semibold uppercase tracking-[0.28em] text-white sm:mb-8 sm:text-xs md:text-[13px]" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-white transition-colors hover:text-white/90">Home</a>
                        <span class="mx-2 inline text-[#c1e82c] sm:mx-3">&gt;&gt;</span>
                        <span class="text-[#c1e82c]">{{ $breadcrumbCurrent }}</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(3rem,10vw,7.5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">{{ $heroTitleLight }}</span><span class="text-[#c1e82c]"> {{ $heroTitleAccent }}</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[13px] font-medium leading-relaxed text-white sm:mt-10 sm:text-[15px] md:text-base">
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $statDivisions }} Divisions Active</span>
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $statSeasonLabel }} {{ $statSeasonRange }}</span>
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ number_format($statPlayers) }} Players Registered</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="bg-[#E4F7E7] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] antialiased" aria-labelledby="league-groups-heading">
            <div class="mx-auto max-w-[1400px] px-5 py-14 sm:py-16 lg:px-14 lg:py-24">
                <h2 id="league-groups-heading" class="mb-11 text-center text-[22px] font-bold uppercase leading-[1.2] tracking-[0.08em] sm:mb-14 sm:text-[26px] md:text-[30px] lg:text-[34px] lg:tracking-[0.1em]">
                    <span class="text-[#333333]">{{ $breadcrumbCurrent }}</span><span class="text-[#4CAF50]"> {{ $groupsHeadingGreen }}</span>
                </h2>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 md:gap-x-10 md:gap-y-8 lg:gap-x-12 lg:gap-y-10">
                    @forelse ($groupCards as $card)
                        <article class="relative overflow-hidden rounded-xl bg-white shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-[#E8F5E9]">
                            <div class="relative z-[1] flex min-h-[128px] items-center gap-3 px-5 py-6 pr-[7rem] sm:min-h-[140px] sm:gap-5 sm:px-7 sm:py-7">
                                <div class="min-w-0 flex-1">
                                    <span class="inline-block rounded-md bg-[#E8F5E9] px-2.5 py-1.5 text-[14px] font-bold uppercase leading-none tracking-[0.12em] text-[#4CAF50] sm:tracking-[0.14em]">{{ $card['tag'] }}</span>
                                    <h3 class="mt-3.5 text-[17px] font-bold leading-snug text-[#333333] sm:text-[18px]">{{ $card['title'] }}</h3>
                                    <p class="mt-2 text-[16px] font-normal leading-relaxed text-[#777777]">{{ $card['meta'] }}</p>
                                </div>
                                <a href="{{ route('league.group', ['leagueSlug' => $currentLeagueSlug, 'groupCardSlug' => $card['slug']]) }}" class="shrink-0 self-center whitespace-nowrap text-[18px] font-semibold leading-tight text-[#4CAF50] underline decoration-[#4CAF50] decoration-1 underline-offset-[3px] transition-opacity hover:opacity-85">View More</a>
                            </div>

                            <div class="pointer-events-none absolute inset-y-0 right-0 z-0 flex h-full w-[6.5rem] items-stretch justify-end sm:w-[30%]" aria-hidden="true">
                                <img
                                    src="{{ asset('frontend/images/league-ring.png') }}"
                                    alt=""
                                    class="h-full w-full min-h-full object-cover object-right opacity-95"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-2">
                            <div class="rounded-xl border border-[#d7ead9] bg-white px-6 py-10 text-center shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                                <p class="text-[22px] font-bold uppercase tracking-[0.08em] text-[#333333]">No List Found</p>
                                <p class="mt-3 text-[16px] text-[#6f7f72]">No group cards are available for this league right now.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
@endsection
