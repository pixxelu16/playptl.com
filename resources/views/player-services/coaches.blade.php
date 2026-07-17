@extends('layouts.website')

@section('nav_active', 'player-services')

@section('title', 'Coach Marketplace | Premier Tennis League')
@section('meta_description', 'Hire certified and approved local tennis coaches to improve your skills and game strategy.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        {{-- Hero Header --}}
        <section class="site-hero relative flex flex-col overflow-hidden">
            <img class="absolute inset-0 z-0 h-full w-full object-cover" src="{{ asset('frontend/images/hero_tennis_banner.png') }}" alt="Tennis Banner Background" aria-hidden="true">

            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-24 pt-36 sm:pb-28 sm:pt-40 lg:pb-32 lg:pt-44">
                <header class="max-w-5xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#B4F000] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#B4F000]">Player Services</span>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#B4F000]">Coach Marketplace</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(2.2rem,7vw,4rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">COACH</span><span class="text-[#B4F000]"> MARKETPLACE</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[18px] font-medium leading-relaxed text-white sm:mt-10">
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">Certified Coaching</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">Private & Group Lessons</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">{{ number_format($coaches->total()) }} {{ $coaches->total() === 1 ? 'Coach' : 'Coaches' }} Available</span>
                    </p>
                </header>
            </div>
        </section>

        {{-- Filter & Grid Content --}}
        <section class="bg-[#E8F5E9] py-10 font-sans antialiased sm:py-12 lg:py-14">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                
                {{-- Search & Filters Panel --}}
                <div class="mb-8 rounded-xl bg-white p-5 shadow-[0_1px_5px_rgba(0,0,0,0.05)] border border-[#E0E0E0]">
                    <form method="GET" action="{{ route('player-services.coaches') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5" for="search">Search Name</label>
                            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="e.g. Jane Smith"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5" for="city">City</label>
                            <input type="text" name="city" id="city" value="{{ $city }}" placeholder="e.g. Mohali"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5" for="state">State</label>
                            <input type="text" name="state" id="state" value="{{ $state }}" placeholder="e.g. Punjab"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5" for="sort">Sort By</label>
                            <select name="sort" id="sort" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none">
                                <option value="newest" @selected($sort === 'newest')>Newest</option>
                                <option value="name_asc" @selected($sort === 'name_asc')>Name: A to Z</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Name: Z to A</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 rounded-md bg-[#5DA44E] hover:bg-[#4d8f40] px-4 py-2 text-sm font-bold text-white shadow-sm transition flex items-center justify-center gap-1.5 h-[38px]" title="Apply Filters">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Filter</span>
                            </button>
                            <a href="{{ route('player-services.coaches') }}" class="flex-1 rounded-md border border-gray-300 bg-white hover:bg-gray-50 px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition flex items-center justify-center gap-1.5 h-[38px]" title="Reset Filters">
                                <i class="fa-solid fa-arrow-rotate-left"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Coaches Cards Grid --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse ($coaches as $coach)
                        <div class="group overflow-hidden rounded-2xl bg-white shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-[#E0E0E0] hover:border-[#5DA44E] transition-all duration-300 hover:shadow-[0_4px_16px_rgba(93,164,78,0.12)] flex flex-col h-full">
                            
                            {{-- Avatar Header Area --}}
                            <div class="bg-gray-50/50 p-6 flex flex-col items-center border-b border-gray-100/80">
                                @if ($coach->avatar_path)
                                    <img src="{{ asset($coach->avatar_path) }}" alt="{{ $coach->name }}" 
                                        class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-sm" loading="lazy" />
                                @else
                                    @php
                                        $initials = '';
                                        if ($coach->first_name && $coach->last_name) {
                                            $initials = strtoupper(substr($coach->first_name, 0, 1) . substr($coach->last_name, 0, 1));
                                        } else {
                                            $names = explode(' ', $coach->name);
                                            $initials = count($names) >= 2 
                                                ? strtoupper(substr($names[0], 0, 1) . substr($names[count($names)-1], 0, 1)) 
                                                : strtoupper(substr($coach->name, 0, 2));
                                        }
                                    @endphp
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[#5DA44E] text-[32px] font-bold text-white uppercase select-none ring-4 ring-white shadow-sm">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <h3 class="mt-4 text-[18px] font-bold text-[#111827] text-center line-clamp-1">{{ $coach->name }}</h3>
                                <p class="text-xs font-semibold text-gray-500 mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-[#5DA44E]/80"></i>
                                    <span>{{ $coach->city }}, {{ $coach->state }}</span>
                                </p>
                            </div>

                            {{-- Description Bio Body --}}
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <p class="text-sm text-gray-600 leading-relaxed text-center line-clamp-4 mb-4 min-h-[80px]">
                                    {{ $coach->profile_bio ?: 'No profile bio description available at this moment.' }}
                                </p>

                                <a href="{{ route('player-services.coach.show', $coach) }}" 
                                    class="block w-full text-center rounded-lg bg-[#E8F7E9] hover:bg-[#5DA44E] text-[#5DA44E] hover:text-white px-4 py-2.5 text-sm font-bold transition duration-300">
                                    View Profile
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl border border-[#E0E0E0] bg-white px-5 py-12 text-center text-gray-500 font-medium">
                            <i class="fa-solid fa-users text-4xl text-gray-300 mb-3 block"></i>
                            <p class="text-lg font-semibold text-gray-700">No Coaches Found</p>
                            <p class="text-sm text-gray-400 mt-1">Try modifying your search query or location filters.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                @if ($coaches->hasPages())
                    <nav class="mt-10 flex justify-center" aria-label="Coaches pagination">
                        <div class="inline-flex flex-wrap items-center justify-center gap-2">
                            @if ($coaches->onFirstPage())
                                <span class="inline-flex h-10 w-10 select-none items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#C4C4C4]">&lsaquo;</span>
                            @else
                                <a href="{{ $coaches->previousPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#424242] transition hover:bg-[#FAFAFA]">&lsaquo;</a>
                            @endif

                            @foreach ($coaches->getUrlRange(1, $coaches->lastPage()) as $page => $url)
                                @if ($page === $coaches->currentPage())
                                    <span class="inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-md bg-[#5DA44E] px-3 text-[15px] font-semibold text-white shadow-sm">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-md border border-[#E0E0E0] bg-white px-3 text-[15px] font-semibold text-[#424242] transition hover:bg-[#FAFAFA]">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($coaches->hasMorePages())
                                <a href="{{ $coaches->nextPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#424242] transition hover:bg-[#FAFAFA]">&rsaquo;</a>
                            @else
                                <span class="inline-flex h-10 w-10 select-none items-center justify-center rounded-md border border-[#E0E0E0] bg-white text-[18px] font-semibold leading-none text-[#C4C4C4]">&rsaquo;</span>
                            @endif
                        </div>
                    </nav>
                @endif

            </div>
        </section>
    </main>
@endsection
