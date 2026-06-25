@extends('layouts.website')

@section('nav_active', 'charity')

@section('title', 'Beyond the Baseline | Charity | Premier Tennis League')
@section('meta_description', 'Premier Tennis League charity partners — every match gives back. Season 2026.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="site-hero relative flex flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
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
                        @if ($charityCausesCount > 0)
                            <span class="text-[#C1D72E]">&#8226;</span>
                            <span class="mx-2">{{ $charityCausesCount }} {{ Str::plural('Charity Cause', $charityCausesCount) }}</span>
                        @endif
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">Season 2026</span>
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">Every match gives back</span>
                    </p>
                </header>
            </div>
        </section>

        @include('partials.charity-total-raised')

        @if ($charityCauses->isNotEmpty())
            <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-16" aria-labelledby="charity-causes-heading">
                <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 sm:mb-10">
                        <h2 id="charity-causes-heading" class="text-left text-[clamp(1.125rem,2.5vw,1.5rem)] font-bold uppercase tracking-[0.08em] text-[#1B3022]">
                            Charity Causes
                        </h2>
                        <span class="rounded-full bg-[#E8F5E9] px-3 py-1 text-[12px] font-semibold text-[#2E7D32] sm:text-[13px]">{{ $charityCausesCount }} active</span>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 lg:gap-6">
                        @foreach ($charityCauses as $cause)
                            <a
                                href="{{ route('charity.cause', $cause) }}"
                                class="group flex h-full flex-col overflow-hidden rounded-[14px] bg-white shadow-[0_2px_12px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] transition-transform hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)]"
                            >
                                <div class="aspect-[16/10] w-full overflow-hidden bg-[#F5F5F5]">
                                    <img
                                        src="{{ asset($cause->image_path) }}"
                                        alt="{{ $cause->title }}"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                        width="640"
                                        height="400"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </div>
                                <div class="flex flex-1 flex-col p-5 sm:p-6">
                                    <h3 class="text-[16px] font-bold leading-snug text-[#000000] sm:text-[17px]">{{ $cause->title }}</h3>
                                    <p class="mt-3 flex-1 text-[13px] leading-relaxed text-[#666666] sm:text-[14px]">{{ Str::limit($cause->description, 120) }}</p>
                                    <span class="mt-4 text-[13px] font-semibold text-[#60a04b]">Support this cause &rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
@endsection
