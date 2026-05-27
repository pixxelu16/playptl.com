@extends('layouts.website')

@section('nav_active', 'gallery')

@section('title', 'Match Gallery | Premier Tennis League')
@section('meta_description', 'Match photos from Premier Tennis League — all divisions, PTL Spring 2026.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@push('styles')
    @include('partials.gallery-photo-styles')
@endpush

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
                        <span class="mx-2">Player uploads</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">{{ number_format($galleryPhotoCount ?? 0) }} {{ ($galleryPhotoCount ?? 0) === 1 ? 'Photo' : 'Photos' }}</span>
                    </p>
                </header>
            </div>
        </section>

        <section
            class="bg-[#E8F5E9] py-10 font-sans antialiased sm:py-12 lg:py-14"
            aria-labelledby="gallery-explore-heading"
            data-gallery-root
        >
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="gallery-explore-heading" class="league-1 mb-6 text-left text-[clamp(2rem,5.5vw,4rem)] font-bold uppercase leading-[1.05] tracking-[0.06em] text-[#111827] sm:mb-8">
                    <span class="text-[#111827]">EXPLORE OUR </span><span class="text-[#5DA44E]">GALLERY</span>
                </h2>

                @php
                    $activeGalleryTab = $galleryActiveTab ?? 'all';
                @endphp

                <div class="mb-8 flex flex-wrap gap-2.5 sm:mb-9 sm:gap-3" role="toolbar" aria-label="Filter photos by date">
                    @foreach ($galleryTabs ?? [['key' => 'all', 'label' => 'All']] as $filter)
                        @php
                            $tabHref = $filter['key'] === 'all'
                                ? route('gallery')
                                : route('gallery', ['tab' => $filter['key']]);
                            $tabIsActive = $activeGalleryTab === $filter['key'];
                        @endphp
                        <a
                            href="{{ $tabHref }}"
                            @class([
                                'inline-flex items-center justify-center rounded-md px-4 py-2 text-[14px] font-semibold transition-colors sm:px-5 sm:text-[15px]',
                                'bg-[#5DA44E] text-white shadow-sm' => $tabIsActive,
                                'border border-[#E0E0E0] bg-white text-[#424242] hover:bg-[#FAFAFA]' => ! $tabIsActive,
                            ])
                            @if ($tabIsActive) aria-current="page" @endif
                        >
                            {{ $filter['label'] }}
                        </a>
                    @endforeach
                </div>

                <div id="gallery-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-[18px] lg:grid-cols-4 lg:gap-5">
                    @forelse ($galleryItems ?? [] as $item)
                        <figure
                            class="gallery-item overflow-hidden rounded-[10px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04]"
                        >
                            <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#111827]">
                                <img
                                    src="{{ $item['url'] }}"
                                    alt="{{ $item['alt'] ?? 'Match photo' }}"
                                    width="400"
                                    height="300"
                                    class="absolute inset-0 h-full w-full object-cover"
                                    loading="lazy"
                                    decoding="async"
                                />
                                @include('partials.gallery-photo-meta', ['item' => $item, 'overlay' => true])
                            </div>
                            @if (! empty($item['notes']))
                                <figcaption class="border-t border-[#EEEEEE] px-3 py-2 text-[12px] leading-snug text-[#4B5563] line-clamp-2">
                                    {{ $item['notes'] }}
                                </figcaption>
                            @endif
                        </figure>
                    @empty
                        @if (($galleryPhotoCount ?? 0) > 0)
                            <div
                                class="col-span-full rounded-lg border border-[#E0E0E0] bg-white px-5 py-10 text-center text-[15px] font-medium text-[#6B7280]"
                                role="status"
                            >
                                @if ($activeGalleryTab === 'earlier')
                                    There are no images for older dates.
                                @else
                                    There are no images for this date.
                                @endif
                            </div>
                        @else
                            <div
                                id="gallery-global-empty"
                                class="col-span-full rounded-lg border border-[#E0E0E0] bg-white px-5 py-12 text-center text-[15px] font-medium text-[#6B7280]"
                            >
                                Abhi gallery mein koi match photo upload nahi hui. Players apni profile se upload kar sakte hain.
                            </div>
                        @endif
                    @endforelse
                </div>

                @if (isset($galleryItems) && $galleryItems->hasPages())
                    @php
                        $gLast = $galleryItems->lastPage();
                        $gCur = $galleryItems->currentPage();
                        $galleryPageNumbers = [];
                        if ($gLast <= 12) {
                            for ($gi = 1; $gi <= $gLast; $gi++) {
                                $galleryPageNumbers[] = $gi;
                            }
                        } else {
                            $galleryPageNumbers[] = 1;
                            $winLo = max(2, $gCur - 1);
                            $winHi = min($gLast - 1, $gCur + 1);
                            if ($winLo > 2) {
                                $galleryPageNumbers[] = null;
                            }
                            for ($gi = $winLo; $gi <= $winHi; $gi++) {
                                $galleryPageNumbers[] = $gi;
                            }
                            if ($winHi < $gLast - 1) {
                                $galleryPageNumbers[] = null;
                            }
                            if ($gLast > 1) {
                                $galleryPageNumbers[] = $gLast;
                            }
                        }
                    @endphp
                    <nav class="mt-8 flex justify-center sm:mt-10" aria-label="Gallery pagination">
                        <div class="inline-flex flex-wrap items-center justify-center gap-2">
                            @if ($galleryItems->onFirstPage())
                                <span
                                    class="inline-flex h-10 w-10 select-none items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#C4C4C4]"
                                    aria-disabled="true"
                                >&lsaquo;</span>
                            @else
                                <a
                                    href="{{ $galleryItems->previousPageUrl() }}"
                                    rel="prev"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#424242] transition hover:bg-[#FAFAFA]"
                                    aria-label="Previous page"
                                >&lsaquo;</a>
                            @endif

                            @foreach ($galleryPageNumbers as $gPage)
                                @if ($gPage === null)
                                    <span class="inline-flex h-10 min-w-[2.5rem] select-none items-center justify-center px-1 text-[15px] font-semibold text-[#9CA3AF]" aria-hidden="true">&hellip;</span>
                                @elseif ($gPage === $gCur)
                                    <span
                                        class="inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-md bg-[#5DA44E] px-3 text-[15px] font-semibold text-white shadow-sm"
                                        aria-current="page"
                                    >{{ $gPage }}</span>
                                @else
                                    <a
                                        href="{{ $galleryItems->url($gPage) }}"
                                        class="inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-md border border-[#E0E0E0] bg-white px-3 text-[15px] font-semibold text-[#424242] transition hover:bg-[#FAFAFA]"
                                    >{{ $gPage }}</a>
                                @endif
                            @endforeach

                            @if ($galleryItems->hasMorePages())
                                <a
                                    href="{{ $galleryItems->nextPageUrl() }}"
                                    rel="next"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#424242] transition hover:bg-[#FAFAFA]"
                                    aria-label="Next page"
                                >&rsaquo;</a>
                            @else
                                <span
                                    class="inline-flex h-10 w-10 select-none items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#C4C4C4]"
                                    aria-disabled="true"
                                >&rsaquo;</span>
                            @endif
                        </div>
                    </nav>
                @endif
            </div>
        </section>
    </main>
@endsection
