@extends('layouts.website')

@section('nav_active', 'gallery')

@section('title', 'Match Gallery | Premier Tennis League')
@section('meta_description', 'Match photos from Premier Tennis League — all divisions, PTL Spring 2026.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    @php
        $galleryPhotos = [
            'public/frontend/images/portrait-beautiful-woman-playing-tennis-outdoor 1.png',
            'public/frontend/images/tennis-player-serving-hard 1.png',
            'public/frontend/images/young-man-tennis-player-court 1.png',
            'public/frontend/images/person-playing-tennis-game-winter-time 1.png',
            'public/frontend/images/man-focused-tennis-game 2.png',
            'public/frontend/images/front-view-couple-tennis-court 1.png',
        ];
        $galleryDateFilters = [
            ['key' => 'all', 'label' => 'All'],
            ['key' => 'today', 'label' => 'Today May 1'],
            ['key' => 'yesterday', 'label' => 'Yesterday Apr 30'],
            ['key' => 'apr29', 'label' => 'Apr 29'],
            ['key' => 'apr27', 'label' => 'Apr 27'],
            ['key' => 'apr25', 'label' => 'Apr 25'],
            ['key' => 'earlier', 'label' => 'Earlier'],
        ];
    @endphp

    <main>
        <section class="relative flex h-[685px] min-h-[685px] flex-col overflow-hidden">
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
                <header class="max-w-5xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#B4F000] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#B4F000]">Gallery</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(4.5rem,11vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">MATCH</span><span class="text-[#B4F000]"> GALLERY</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[18px] font-medium leading-relaxed text-white sm:mt-10">
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">All Divisions</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">Season: May – Aug 2026</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">48 Photos</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="bg-[#E8F5E9] py-10 font-sans antialiased sm:py-12 lg:py-14" aria-labelledby="gallery-explore-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="gallery-explore-heading" class="league-1 mb-6 text-left text-[clamp(2rem,5.5vw,4rem)] font-bold uppercase leading-[1.05] tracking-[0.06em] text-[#111827] sm:mb-8">
                    <span class="text-[#111827]">EXPLORE OUR </span><span class="text-[#5DA44E]">GALLERY</span>
                </h2>

                <div class="mb-8 flex flex-wrap gap-2.5 sm:mb-9 sm:gap-3" role="toolbar" aria-label="Filter photos by date">
                    @foreach ($galleryDateFilters as $fi => $filter)
                        <button
                            type="button"
                            data-gallery-filter="{{ $filter['key'] }}"
                            @class([
                                'rounded-md px-4 py-2 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px]',
                                'bg-[#5DA44E] text-white shadow-sm' => $fi === 0,
                                'border border-[#E0E0E0] bg-white text-[#424242] hover:bg-[#FAFAFA]' => $fi !== 0,
                            ])
                        >
                            {{ $filter['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-[18px] lg:grid-cols-4 lg:gap-5">
                    @for ($gi = 0; $gi < 16; $gi++)
                        @php $src = $galleryPhotos[$gi % count($galleryPhotos)]; @endphp
                        <div class="overflow-hidden rounded-[10px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04]">
                            <img
                                src="{{ asset($src) }}"
                                alt="Premier Tennis League match photo"
                                width="400"
                                height="300"
                                class="aspect-[4/3] h-full w-full object-cover"
                                loading="lazy"
                                decoding="async"
                            />
                        </div>
                    @endfor
                </div>

                <nav class="mt-10 flex justify-center sm:mt-12" aria-label="Gallery pagination">
                    <div class="inline-flex items-center gap-2 sm:gap-2.5">
                        <button
                            type="button"
                            data-gallery-page="prev"
                            class="gallery-page-btn flex h-9 w-9 items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[15px] font-semibold text-[#6B7280] transition-colors hover:bg-[#FAFAFA]"
                            aria-label="Previous page"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button
                            type="button"
                            data-gallery-page-num="1"
                            class="gallery-page-num flex h-9 min-w-[2.25rem] items-center justify-center rounded-md bg-[#5DA44E] px-3 text-[14px] font-semibold text-white shadow-sm sm:text-[15px]"
                            aria-current="page"
                        >
                            1
                        </button>
                        <button
                            type="button"
                            data-gallery-page-num="2"
                            class="gallery-page-num flex h-9 min-w-[2.25rem] items-center justify-center rounded-md border border-[#E0E0E0] bg-white px-3 text-[14px] font-semibold text-[#6B7280] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]"
                        >
                            2
                        </button>
                        <button
                            type="button"
                            data-gallery-page-num="3"
                            class="gallery-page-num flex h-9 min-w-[2.25rem] items-center justify-center rounded-md border border-[#E0E0E0] bg-white px-3 text-[14px] font-semibold text-[#6B7280] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]"
                        >
                            3
                        </button>
                        <button
                            type="button"
                            data-gallery-page="next"
                            class="gallery-page-btn flex h-9 w-9 items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[15px] font-semibold text-[#6B7280] transition-colors hover:bg-[#FAFAFA]"
                            aria-label="Next page"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </nav>
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            (function () {
                var activeFilterCls = 'rounded-md px-4 py-2 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px] bg-[#5DA44E] text-white shadow-sm';
                var inactiveFilterCls =
                    'rounded-md px-4 py-2 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px] border border-[#E0E0E0] bg-white text-[#424242] hover:bg-[#FAFAFA]';
                var activePageCls =
                    'gallery-page-num flex h-9 min-w-[2.25rem] items-center justify-center rounded-md bg-[#5DA44E] px-3 text-[14px] font-semibold text-white shadow-sm sm:text-[15px]';
                var inactivePageCls =
                    'gallery-page-num flex h-9 min-w-[2.25rem] items-center justify-center rounded-md border border-[#E0E0E0] bg-white px-3 text-[14px] font-semibold text-[#6B7280] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]';

                document.querySelectorAll('[data-gallery-filter]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        document.querySelectorAll('[data-gallery-filter]').forEach(function (b) {
                            b.className = inactiveFilterCls;
                            b.removeAttribute('aria-pressed');
                        });
                        btn.className = activeFilterCls;
                        btn.setAttribute('aria-pressed', 'true');
                    });
                });

                document.querySelectorAll('[data-gallery-page-num]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        document.querySelectorAll('[data-gallery-page-num]').forEach(function (b) {
                            b.className = inactivePageCls;
                            b.removeAttribute('aria-current');
                        });
                        btn.className = activePageCls;
                        btn.setAttribute('aria-current', 'page');
                    });
                });
            })();
        </script>
    @endpush
@endsection
