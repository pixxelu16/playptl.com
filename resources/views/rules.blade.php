@extends('layouts.website')

@section('title', 'Premier Tennis League Rules & Regulations')
@section('meta_description', 'Official PTL rules, match format, scoring system, scheduling policies, walkovers, injury rules, umpiring guidelines, and tournament regulations.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main class="rules-page font-['Montserrat',ui-sans-serif,system-ui,sans-serif]">
        @include('partials.site-page-hero', [
            'heroBannerPath' => \App\Models\SiteSetting::banners()['privacy_banner_path'] ?? 'frontend/images/league-banner.jpg',
            'heroBreadcrumb' => 'Rules & Regulations',
            'heroTitleLight' => 'RULES &',
            'heroTitleAccent' => 'REGULATIONS',
            'heroMetaItems' => [
                'Version ' . ($currentVersion->version_number ?? '2.3'),
                'Last Updated: ' . ($currentVersion->last_updated ?? 'August 1, 2026'),
            ],
        ])

        <section class="bg-[#E4F7E7] pb-12 pt-10 antialiased sm:pb-16 sm:pt-12">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                
                <!-- Main Container Card (Matches Privacy Policy Layout) -->
                <div class="rounded-[15px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8 lg:p-10">
                    
                    <!-- Search & Action Toolbar -->
                    <div class="no-print mb-8 rounded-xl bg-[#F4FBF4] p-4 sm:p-6 ring-1 ring-[#D8EBD8]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            
                            <!-- Live Search Input -->
                            <div class="relative flex-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#5A8F5A]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="search"
                                    id="rule-search-input"
                                    placeholder="Search rules (e.g. walkover, tiebreak, points, late arrival)..."
                                    class="w-full rounded-lg border border-[#C2E0C2] bg-white py-3 pl-10 pr-10 text-[14px] font-medium text-[#1B3022] placeholder-[#7CA67C] focus:border-[#4CAF50] focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/20"
                                >
                                <button type="button" id="clear-search-btn" class="hidden absolute inset-y-0 right-0 flex items-center pr-3 text-[#7CA67C] hover:text-[#1B3022]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Controls: Expand/Collapse, Print, Download, Share -->
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 shrink-0">
                                <button type="button" id="btn-expand-all" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2.5 text-[13px] font-semibold text-[#2D4A2D] shadow-sm ring-1 ring-[#C2E0C2] transition-colors hover:bg-[#E8F5E9]">
                                    <svg class="h-4 w-4 text-[#4CAF50]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    Expand All
                                </button>

                                <button type="button" id="btn-collapse-all" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2.5 text-[13px] font-semibold text-[#2D4A2D] shadow-sm ring-1 ring-[#C2E0C2] transition-colors hover:bg-[#E8F5E9]">
                                    <svg class="h-4 w-4 text-[#4CAF50]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    Collapse All
                                </button>

                                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg bg-[#2D4A2D] px-3.5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition-colors hover:bg-[#1B3022]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print Rules
                                </button>

                                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg bg-[#4CAF50] px-3.5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition-colors hover:bg-[#3d9140]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download PDF
                                </button>

                                <button type="button" id="btn-share-link" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2.5 text-[13px] font-semibold text-[#2D4A2D] shadow-sm ring-1 ring-[#C2E0C2] transition-colors hover:bg-[#E8F5E9]">
                                    <svg class="h-4 w-4 text-[#4CAF50]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                    Share
                                </button>
                            </div>
                        </div>

                        <!-- Search Result Notice -->
                        <div id="search-status-bar" class="hidden mt-3 text-[13px] font-semibold text-[#2D4A2D]">
                            <span id="search-count-text">Found 0 rules matching your query</span>
                        </div>
                    </div>

                    <!-- Table of Contents -->
                    <nav id="table-of-contents" class="mb-10 rounded-xl bg-[#FAFDF9] p-5 sm:p-7 ring-1 ring-[#E1F0E1]" aria-label="Table of Contents">
                        <div class="mb-4 flex items-center justify-between border-b border-[#E1F0E1] pb-3">
                            <h2 class="text-[17px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">Table of Contents</h2>
                            <span class="text-[12px] font-semibold text-[#5A8F5A]">{{ count($sections) }} Sections</span>
                        </div>

                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($sections as $index => $sec)
                                <a href="#section-{{ $sec->id }}" class="js-toc-link flex items-center gap-2.5 rounded-lg bg-white px-3.5 py-2 text-[14px] font-medium text-[#2D4A2D] shadow-2xs ring-1 ring-[#E1F0E1] transition-all hover:bg-[#E8F5E9] hover:text-[#1B3022]">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-[#E8F5E9] text-[12px] font-bold text-[#4CAF50]">{{ $index + 1 }}</span>
                                    <span class="truncate">{{ $sec->title }}</span>
                                </a>
                            @endforeach
                            <a href="#section-faqs" class="js-toc-link flex items-center gap-2.5 rounded-lg bg-white px-3.5 py-2 text-[14px] font-medium text-[#2D4A2D] shadow-2xs ring-1 ring-[#E1F0E1] transition-all hover:bg-[#E8F5E9]">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-[#E8F5E9] text-[12px] font-bold text-[#4CAF50]">FAQ</span>
                                <span class="truncate">Frequently Asked Questions</span>
                            </a>
                            <a href="#section-history" class="js-toc-link flex items-center gap-2.5 rounded-lg bg-white px-3.5 py-2 text-[14px] font-medium text-[#2D4A2D] shadow-2xs ring-1 ring-[#E1F0E1] transition-all hover:bg-[#E8F5E9]">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-[#E8F5E9] text-[12px] font-bold text-[#4CAF50]">v</span>
                                <span class="truncate">Version History</span>
                            </a>
                        </div>
                    </nav>

                    <!-- Rule Sections Cards -->
                    <div id="rules-cards-container" class="space-y-8">
                        @foreach ($sections as $sIndex => $sec)
                            <article id="section-{{ $sec->id }}" class="rule-card scroll-mt-28 rounded-xl bg-white shadow-[0_2px_12px_rgba(0,0,0,0.04)] ring-1 ring-[#E1F0E1] overflow-hidden">
                                
                                <!-- Card Header / Accordion Button -->
                                <button type="button" class="js-toggle-accordion w-full flex items-center justify-between bg-[#F4FBF4] px-6 py-4 text-left transition-colors hover:bg-[#E8F5E9]" aria-expanded="true">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#5E9E52] text-[14px] font-extrabold text-white">{{ $sIndex + 1 }}</span>
                                        <h2 class="rule-card-title text-[18px] font-bold uppercase tracking-[0.04em] text-[#1B3022] sm:text-[20px]">{{ $sec->title }}</h2>
                                    </div>
                                    <span class="accordion-icon shrink-0 rounded-full bg-white p-1 text-[#4CAF50] ring-1 ring-[#C2E0C2] transition-transform duration-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </button>

                                <!-- Card Body -->
                                <div class="accordion-body px-6 py-6 border-t border-[#E1F0E1] space-y-6">
                                    @forelse ($sec->items as $item)
                                        <div class="rule-item-block group rounded-lg p-4 transition-colors hover:bg-[#FAFDF9]">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-0.5 inline-block rounded bg-[#E8F5E9] px-2 py-0.5 text-[13px] font-bold text-[#4CAF50] shadow-2xs">{{ $item->item_number ?? ($sIndex + 1 . '.' . $loop->iteration) }}</span>
                                                <div class="flex-1">
                                                    <h3 class="rule-item-title text-[16px] font-bold text-[#1B3022] sm:text-[17px]">{{ $item->title }}</h3>
                                                    <p class="rule-item-content mt-2 text-[15px] leading-relaxed text-[#333333]">{{ $item->content }}</p>

                                                    @if ($item->is_highlighted)
                                                        @php
                                                            $badgeClasses = match($item->highlight_type) {
                                                                'warning' => 'bg-[#FFF3CD] border-[#FFE082] text-[#856404]',
                                                                'important' => 'bg-[#F8D7DA] border-[#F5C6CB] text-[#721C24]',
                                                                'success' => 'bg-[#D4EDDA] border-[#C3E6CB] text-[#155724]',
                                                                default => 'bg-[#E8F4FD] border-[#BEE5EB] text-[#0C5460]'
                                                            };
                                                        @endphp
                                                        <div class="mt-3 flex items-start gap-2 rounded-lg border px-4 py-3 text-[14px] font-medium shadow-2xs {{ $badgeClasses }}">
                                                            <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span><strong>Important Note:</strong> {{ $item->content }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-[14px] font-medium text-[#777777]">No sub-rules defined for this section.</p>
                                    @endforelse

                                    <!-- Back to Top Link -->
                                    <div class="no-print border-t border-[#F0F4F0] pt-4 text-right">
                                        <a href="#table-of-contents" class="js-toc-link inline-flex items-center gap-1 text-[13px] font-semibold text-[#5A8F5A] hover:text-[#1B3022]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7 7 7M12 3v18" />
                                            </svg>
                                            Back to Top
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Frequently Asked Questions Accordion Section -->
                    <section id="section-faqs" class="rule-card scroll-mt-28 mt-10 rounded-xl bg-white shadow-[0_2px_12px_rgba(0,0,0,0.04)] ring-1 ring-[#E1F0E1] overflow-hidden">
                        <button type="button" class="js-toggle-accordion w-full flex items-center justify-between bg-[#F4FBF4] px-6 py-4 text-left transition-colors hover:bg-[#E8F5E9]" aria-expanded="true">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#5E9E52] text-[14px] font-extrabold text-white">FAQ</span>
                                <h2 class="rule-card-title text-[18px] font-bold uppercase tracking-[0.04em] text-[#1B3022] sm:text-[20px]">Frequently Asked Questions</h2>
                            </div>
                            <span class="accordion-icon shrink-0 rounded-full bg-white p-1 text-[#4CAF50] ring-1 ring-[#C2E0C2]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>

                        <div class="accordion-body px-6 py-6 border-t border-[#E1F0E1] space-y-4">
                            @foreach ($faqs as $faq)
                                <div class="rule-item-block rounded-lg border border-[#E1F0E1] bg-[#FAFDF9] p-4 sm:p-5">
                                    <h3 class="rule-item-title text-[16px] font-bold text-[#1B3022] flex items-center gap-2">
                                        <span class="text-[#4CAF50]">Q:</span> {{ $faq->question }}
                                    </h3>
                                    <p class="rule-item-content mt-2 text-[15px] leading-relaxed text-[#333333] pl-6 border-l-2 border-[#4CAF50]">
                                        {{ $faq->answer }}
                                    </p>
                                </div>
                            @endforeach

                            <div class="no-print border-t border-[#F0F4F0] pt-4 text-right">
                                <a href="#table-of-contents" class="js-toc-link inline-flex items-center gap-1 text-[13px] font-semibold text-[#5A8F5A] hover:text-[#1B3022]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7 7 7M12 3v18" />
                                    </svg>
                                    Back to Top
                                </a>
                            </div>
                        </div>
                    </section>

                    <!-- Version History Log -->
                    <section id="section-history" class="rule-card scroll-mt-28 mt-10 rounded-xl bg-white shadow-[0_2px_12px_rgba(0,0,0,0.04)] ring-1 ring-[#E1F0E1] overflow-hidden">
                        <button type="button" class="js-toggle-accordion w-full flex items-center justify-between bg-[#F4FBF4] px-6 py-4 text-left transition-colors hover:bg-[#E8F5E9]" aria-expanded="true">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#2D4A2D] text-[14px] font-extrabold text-white">LOG</span>
                                <h2 class="rule-card-title text-[18px] font-bold uppercase tracking-[0.04em] text-[#1B3022] sm:text-[20px]">Rule Version History</h2>
                            </div>
                            <span class="accordion-icon shrink-0 rounded-full bg-white p-1 text-[#4CAF50] ring-1 ring-[#C2E0C2]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>

                        <div class="accordion-body px-6 py-6 border-t border-[#E1F0E1] space-y-4">
                            @foreach ($versionHistory as $vh)
                                <div class="rounded-lg bg-[#FAFDF9] p-4 ring-1 ring-[#E1F0E1]">
                                    <div class="flex items-center justify-between border-b border-[#E1F0E1] pb-2 mb-2">
                                        <span class="font-bold text-[#1B3022]">Version {{ $vh->version_number }}</span>
                                        <span class="text-[13px] font-medium text-[#5A8F5A]">{{ $vh->last_updated }}</span>
                                    </div>
                                    <p class="whitespace-pre-line text-[14px] text-[#333333]">{{ $vh->changelog }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <!-- Related Quick Links -->
                    <div class="no-print mt-12 border-t border-[#E1F0E1] pt-8">
                        <h3 class="mb-4 text-[16px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">Related Pages & Quick Links</h3>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                            <a href="{{ route('league.overview', ['slug' => 'ptl-summer-2026']) }}" class="rounded-lg bg-[#F4FBF4] px-3.5 py-2.5 text-center text-[13px] font-semibold text-[#2D4A2D] ring-1 ring-[#C2E0C2] hover:bg-[#E8F5E9]">Tournament Format</a>
                            <a href="{{ route('league.overview', ['slug' => 'ptl-summer-2026']) }}" class="rounded-lg bg-[#F4FBF4] px-3.5 py-2.5 text-center text-[13px] font-semibold text-[#2D4A2D] ring-1 ring-[#C2E0C2] hover:bg-[#E8F5E9]">Rankings</a>
                            <a href="{{ route('league.overview', ['slug' => 'ptl-summer-2026']) }}" class="rounded-lg bg-[#F4FBF4] px-3.5 py-2.5 text-center text-[13px] font-semibold text-[#2D4A2D] ring-1 ring-[#C2E0C2] hover:bg-[#E8F5E9]">Schedule</a>
                            <a href="{{ route('league.overview', ['slug' => 'ptl-summer-2026']) }}" class="rounded-lg bg-[#F4FBF4] px-3.5 py-2.5 text-center text-[13px] font-semibold text-[#2D4A2D] ring-1 ring-[#C2E0C2] hover:bg-[#E8F5E9]">Standings</a>
                            <a href="{{ route('league.overview', ['slug' => 'ptl-summer-2026']) }}" class="rounded-lg bg-[#F4FBF4] px-3.5 py-2.5 text-center text-[13px] font-semibold text-[#2D4A2D] ring-1 ring-[#C2E0C2] hover:bg-[#E8F5E9]">Playoffs</a>
                            <a href="{{ url('/') }}#contact" class="rounded-lg bg-[#5E9E52] px-3.5 py-2.5 text-center text-[13px] font-bold text-white hover:bg-[#4CAF50]">Contact PTL</a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- Custom Print Styles for Clean PDF Export -->
    <style>
        @media print {
            body { background: white !important; color: black !important; font-size: 12pt; }
            .no-print, header, footer, nav, button, .site-hero { display: none !important; }
            .accordion-body { display: block !important; border: none !important; padding: 0 !important; }
            .rule-card { box-shadow: none !important; border: 1px solid #ccc !important; margin-bottom: 20px !important; page-break-inside: avoid; }
            mark { background: transparent !important; color: black !important; font-weight: bold; }
        }
    </style>

    <!-- Interactive JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('rule-search-input');
            var clearSearchBtn = document.getElementById('clear-search-btn');
            var searchStatusBar = document.getElementById('search-status-bar');
            var searchCountText = document.getElementById('search-count-text');
            var ruleCards = document.querySelectorAll('.rule-card');
            var expandAllBtn = document.getElementById('btn-expand-all');
            var collapseAllBtn = document.getElementById('btn-collapse-all');
            var shareLinkBtn = document.getElementById('btn-share-link');

            // Accordion Toggle
            document.querySelectorAll('.js-toggle-accordion').forEach(function (button) {
                button.addEventListener('click', function () {
                    var isExpanded = button.getAttribute('aria-expanded') === 'true';
                    var body = button.nextElementSibling;
                    var icon = button.querySelector('.accordion-icon');

                    button.setAttribute('aria-expanded', !isExpanded);
                    if (isExpanded) {
                        body.classList.add('hidden');
                        if (icon) icon.style.transform = 'rotate(-90deg)';
                    } else {
                        body.classList.remove('hidden');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    }
                });
            });

            // Expand All / Collapse All
            if (expandAllBtn) {
                expandAllBtn.addEventListener('click', function () {
                    document.querySelectorAll('.js-toggle-accordion').forEach(function (btn) {
                        btn.setAttribute('aria-expanded', 'true');
                        btn.nextElementSibling.classList.remove('hidden');
                        var icon = btn.querySelector('.accordion-icon');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    });
                });
            }

            if (collapseAllBtn) {
                collapseAllBtn.addEventListener('click', function () {
                    document.querySelectorAll('.js-toggle-accordion').forEach(function (btn) {
                        btn.setAttribute('aria-expanded', 'false');
                        btn.nextElementSibling.classList.add('hidden');
                        var icon = btn.querySelector('.accordion-icon');
                        if (icon) icon.style.transform = 'rotate(-90deg)';
                    });
                });
            }

            // Smooth Scroll for TOC links
            document.querySelectorAll('.js-toc-link').forEach(function (anchor) {
                anchor.addEventListener('click', function (e) {
                    var targetId = this.getAttribute('href');
                    if (targetId.startsWith('#')) {
                        var targetEl = document.querySelector(targetId);
                        if (targetEl) {
                            e.preventDefault();
                            targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            var toggleBtn = targetEl.querySelector('.js-toggle-accordion');
                            if (toggleBtn && toggleBtn.getAttribute('aria-expanded') === 'false') {
                                toggleBtn.click();
                            }
                        }
                    }
                });
            });

            // Live Search and Highlighting
            function filterRules() {
                var query = searchInput.value.trim().toLowerCase();
                var totalMatches = 0;

                if (query === '') {
                    clearSearchBtn.classList.add('hidden');
                    searchStatusBar.classList.add('hidden');
                    ruleCards.forEach(function (card) {
                        card.classList.remove('hidden');
                        card.querySelectorAll('.rule-item-block').forEach(function (item) {
                            item.classList.remove('hidden');
                        });
                    });
                    return;
                }

                clearSearchBtn.classList.remove('hidden');
                searchStatusBar.classList.remove('hidden');

                ruleCards.forEach(function (card) {
                    var cardTitle = card.querySelector('.rule-card-title')?.textContent.toLowerCase() || '';
                    var items = card.querySelectorAll('.rule-item-block');
                    var cardMatches = 0;

                    items.forEach(function (item) {
                        var text = item.textContent.toLowerCase();
                        if (text.includes(query) || cardTitle.includes(query)) {
                            item.classList.remove('hidden');
                            cardMatches++;
                            totalMatches++;
                        } else {
                            item.classList.add('hidden');
                        }
                    });

                    if (cardMatches > 0 || cardTitle.includes(query)) {
                        card.classList.remove('hidden');
                        var btn = card.querySelector('.js-toggle-accordion');
                        if (btn && btn.getAttribute('aria-expanded') === 'false') {
                            btn.click();
                        }
                    } else {
                        card.classList.add('hidden');
                    }
                });

                searchCountText.textContent = 'Found ' + totalMatches + ' matching rule' + (totalMatches === 1 ? '' : 's') + ' for "' + query + '"';
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterRules);
            }

            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    filterRules();
                });
            }

            // Share button
            if (shareLinkBtn) {
                shareLinkBtn.addEventListener('click', function () {
                    if (navigator.share) {
                        navigator.share({
                            title: 'Premier Tennis League Rules & Regulations',
                            url: window.location.href
                        }).catch(function (){});
                    } else {
                        navigator.clipboard.writeText(window.location.href);
                        alert('Rules page URL copied to clipboard!');
                    }
                });
            }
        });
    </script>
@endsection
