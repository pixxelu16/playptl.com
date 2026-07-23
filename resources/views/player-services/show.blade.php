@extends('layouts.website')

@section('nav_active', 'player-services')

@section('title', $user->name . ' - ' . $roleName . ' Profile | Premier Tennis League')
@section('meta_description', 'View the ' . strtolower($roleName) . ' profile details for ' . $user->name . ' on Premier Tennis League.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main class="font-sans antialiased text-[#333333]">
        {{-- Hero Header --}}
        <section class="site-hero relative flex flex-col overflow-hidden">
            @php
                $banners = \App\Models\SiteSetting::banners();
                $defaultBanner = 'frontend/images/hero_tennis_banner.png';
                $profileBannerSrc = $roleName === 'Mentor'
                    ? ($banners['mentor_profile_banner_path'] ?? $defaultBanner)
                    : ($banners['coach_profile_banner_path'] ?? $defaultBanner);
            @endphp
            <img class="absolute inset-0 z-0 h-full w-full object-cover" src="{{ asset($profileBannerSrc) }}" alt="{{ $roleName }} Profile Banner Background" aria-hidden="true">

            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-24 pt-36 sm:pb-28 sm:pt-40 lg:pb-32 lg:pt-44">
                <header class="max-w-5xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#B4F000] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <a href="{{ $roleName === 'Mentor' ? route('player-services.mentors') : route('player-services.coaches') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">
                            {{ $roleName === 'Mentor' ? 'Mentors' : 'Coach Marketplace' }}
                        </a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#B4F000]">{{ $user->name }}</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(3.5rem,10vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">{{ strtoupper($roleName) }}</span><span class="text-[#B4F000]"> PROFILE</span>
                    </h1>
                </header>
            </div>
        </section>

        {{-- Details Panel Section --}}
        <section class="bg-[#E8F5E9] py-12">
            <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                
                {{-- Flex Container --}}
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    
                    {{-- Left Sidebar Profile Card --}}
                    <div class="w-full lg:w-[350px] shrink-0 rounded-2xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-[#E0E0E0] text-center flex flex-col items-center">
                        @if ($user->avatar_path)
                            <img src="{{ asset($user->avatar_path) }}" alt="{{ $user->name }}" 
                                class="h-32 w-32 rounded-full object-cover ring-4 ring-[#E8F7E9] shadow-sm mb-4" />
                        @else
                            @php
                                $initials = '';
                                if ($user->first_name && $user->last_name) {
                                    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
                                } else {
                                    $names = explode(' ', $user->name);
                                    $initials = count($names) >= 2 
                                        ? strtoupper(substr($names[0], 0, 1) . substr($names[count($names)-1], 0, 1)) 
                                        : strtoupper(substr($user->name, 0, 2));
                                }
                            @endphp
                            <div class="flex h-32 w-32 items-center justify-center rounded-full bg-[#5DA44E] text-[42px] font-bold text-white uppercase select-none ring-4 ring-[#E8F7E9] shadow-sm mb-4">
                                {{ $initials }}
                            </div>
                        @endif

                        <h2 class="text-xl font-bold text-[#111827]">{{ $user->name }}</h2>
                        <span class="mt-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-[#5DA44E] border border-emerald-100 mb-6">
                            {{ $roleName }}
                        </span>

                        {{-- Details List --}}
                        <div class="w-full space-y-4 border-t border-gray-100 pt-6 text-left">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-location-dot text-[#5DA44E] mt-1 text-sm"></i>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Location</h4>
                                    <p class="text-sm font-semibold text-gray-700">{{ $user->city }}, {{ $user->state }}</p>
                                </div>
                            </div>
                            
                            @if ($user->profile_rate !== null)
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-circle-dollar-to-slot text-[#5DA44E] mt-1 text-sm"></i>
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Hourly Rate</h4>
                                        <p class="text-lg font-extrabold text-[#5DA44E]">${{ number_format($user->profile_rate, 2) }} <span class="text-xs font-normal text-gray-500">/ hour</span></p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if (auth()->check())
                            @php
                                $activeRole = session('active_dashboard_role');
                                if (!$activeRole) {
                                    if (auth()->user()->hasRole('Student')) {
                                        $activeRole = 'student';
                                    } else {
                                        $activeRole = strtolower(auth()->user()->role->value);
                                    }
                                }
                            @endphp

                            @if ($activeRole === 'student')
                                @if (auth()->id() !== $user->id)
                                    {{-- Book Now Block (Students only) --}}
                                    <div class="w-full space-y-3 mt-8 border-t border-gray-100 pt-6">
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider text-left mb-3">Book a Session</h4>
                                        <a href="{{ route('booking.create', $user->username) }}"
                                           class="flex items-center justify-center gap-2 w-full rounded-lg bg-[#5DA44E] hover:bg-[#4d8f40] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition">
                                            <i class="fa-solid fa-calendar-check"></i>
                                            <span>Book {{ $roleName }}</span>
                                        </a>
                                    </div>
                                @else
                                    {{-- Cannot book oneself --}}
                                    <div class="w-full space-y-3 mt-8 border-t border-gray-100 pt-6">
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider text-left mb-3">Book a Session</h4>
                                        <p class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                                            This is your profile. You cannot book sessions with yourself.
                                        </p>
                                    </div>
                                @endif
                            @else
                                {{-- Not active as student --}}
                                @if (auth()->user()->hasRole('Student'))
                                    <div class="w-full space-y-3 mt-8 border-t border-gray-100 pt-6">
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider text-left mb-3">Book a Session</h4>
                                        <p class="text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                                            Please switch to your <strong>Student</strong> account using the dashboard panel switcher to book sessions.
                                        </p>
                                    </div>
                                @else
                                    <div class="w-full space-y-3 mt-8 border-t border-gray-100 pt-6">
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider text-left mb-3">Book a Session</h4>
                                        <form method="POST" action="{{ route('player.become-student') }}">
                                            @csrf
                                            <p class="text-xs text-gray-500 mb-2.5">You must register as a Student to book sessions.</p>
                                            <button type="submit" class="flex items-center justify-center gap-2 w-full rounded-lg bg-[#2E7D32] hover:bg-[#1B5E20] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition">
                                                <i class="fa-solid fa-graduation-cap"></i>
                                                <span>Become a Student</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        @endif

                        @if (auth()->check() && auth()->user()->hasRole('Super Admin'))
                            {{-- Contact Block (Super Admins only) --}}
                            <div class="w-full space-y-3 mt-8 border-t border-gray-100 pt-6">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider text-left mb-3">Contact Details</h4>
                                <a href="mailto:{{ $user->email }}" class="flex items-center justify-center gap-2 w-full rounded-lg bg-[#5DA44E] hover:bg-[#4d8f40] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition">
                                    <i class="fa-solid fa-envelope"></i>
                                    <span>Email {{ $roleName }}</span>
                                </a>
                                @if ($user->phone)
                                    <a href="tel:{{ preg_replace('/\D+/', '', $user->phone) }}" class="flex items-center justify-center gap-2 w-full rounded-lg border border-[#5DA44E] text-[#5DA44E] hover:bg-[#E8F7E9] px-4 py-2.5 text-sm font-bold transition">
                                        <i class="fa-solid fa-phone"></i>
                                        <span>Call {{ $user->phone }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Right Detailed Information Panel --}}
                    <div class="w-full flex-1 space-y-6">
                        
                        {{-- Introduction Card --}}
                        <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-[#E0E0E0] space-y-4">
                            <h3 class="text-lg font-bold text-[#111827] border-b border-gray-100 pb-3 flex items-center gap-2">
                                <i class="fa-solid fa-star text-[#5DA44E]"></i>
                                <span>{{ $roleName === 'Mentor' ? 'What I Offer' : 'About My Lessons' }}</span>
                            </h3>
                            <h4 class="text-md font-bold text-gray-800 italic leading-snug">
                                "{{ $user->profile_title_ad ?: 'No introduction title configured.' }}"
                            </h4>
                            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">
                                {{ $user->profile_lessons ?: 'No description configured yet.' }}
                            </p>
                        </div>

                        {{-- Biography Details --}}
                        <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-[#E0E0E0] space-y-4">
                            <h3 class="text-lg font-bold text-[#111827] border-b border-gray-100 pb-3 flex items-center gap-2">
                                <i class="fa-solid fa-circle-user text-[#5DA44E]"></i>
                                <span>About Me & Experience</span>
                            </h3>
                            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">
                                {{ $user->profile_bio ?: 'Biography description has not been filled out yet.' }}
                            </p>
                        </div>

                        {{-- Locations & Rates Details --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Locations Card --}}
                            <div class="rounded-2xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-[#E0E0E0] space-y-4">
                                <h3 class="text-md font-bold text-[#111827] border-b border-gray-100 pb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-map-location-dot text-[#5DA44E]"></i>
                                    <span>Lesson Locations</span>
                                </h3>
                                <ul class="space-y-3">
                                    @php
                                        $locMap = [
                                            'outdoor' => 'At outdoor court',
                                            'indoor' => 'At Indoor court',
                                            'student_choice' => 'Student choice',
                                            'travel' => 'I Can travel (20 miles)',
                                            'online' => 'Online'
                                        ];
                                        $activeLocs = $user->profile_locations ?? [];
                                    @endphp
                                    @forelse ($locMap as $key => $label)
                                        @php $isChecked = in_array($key, $activeLocs, true); @endphp
                                        <li class="flex items-center gap-3 text-sm {{ $isChecked ? 'text-gray-800 font-semibold' : 'text-gray-400 line-through' }}">
                                            @if ($isChecked)
                                                <i class="fa-solid fa-circle-check text-[#5DA44E]"></i>
                                            @else
                                                <i class="fa-solid fa-circle-xmark text-gray-300"></i>
                                            @endif
                                            <span>{{ $label }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-gray-400 italic">No locations configured.</li>
                                    @endforelse
                                </ul>
                            </div>

                            {{-- Rates Details Card --}}
                            <div class="rounded-2xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-[#E0E0E0] space-y-4">
                                <h3 class="text-md font-bold text-[#111827] border-b border-gray-100 pb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-tags text-[#5DA44E]"></i>
                                    <span>Pricing & Rate Details</span>
                                </h3>
                                @if ($user->profile_rate !== null)
                                    <p class="text-sm text-gray-700 font-semibold leading-relaxed">
                                        Standard Rate: <span class="text-lg font-bold text-[#5DA44E]">${{ number_format($user->profile_rate, 2) }}</span> / hour
                                    </p>
                                @endif
                                <p class="text-sm text-gray-500 leading-relaxed whitespace-pre-line">
                                    {{ $user->profile_rate_details ?: 'No additional details about rates are configured.' }}
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </section>
    </main>
@endsection
