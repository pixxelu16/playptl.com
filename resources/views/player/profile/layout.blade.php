@extends('layouts.website')

@section('title')
    @yield('profile_title')
@endsection

@section('meta_description')
    @yield('profile_meta_description', 'Update your player profile and account settings.')
@endsection

@section('page_bg', '#E8F7E9')
@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')
@section('suppress_global_status', true)

@section('content')
    <main class="bg-[#E8F7E9] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#333333] antialiased">
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

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-24 pt-36 sm:px-8 sm:pb-28 sm:pt-40 lg:px-14 lg:pb-32 lg:pt-44">
                <header class="max-w-5xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#B4F000] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        @if (! empty($profileBreadcrumbTail))
                            <a href="{{ route('player.my-profile') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">My Profile</a>
                            <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                            <span class="text-[#B4F000]">{{ $profileBreadcrumbTail }}</span>
                        @else
                            <span class="text-[#B4F000]">My Profile</span>
                        @endif
                    </nav>

                    <h1 class="league-1 text-[clamp(4.5rem,11vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">MY</span><span class="text-[#B4F000]"> PROFILE</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[18px] font-medium leading-relaxed text-white sm:mt-10">
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">Player Account</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">Profile Details</span>
                        <span class="text-[#B4F000]">&#8226;</span>
                        <span class="mx-2">Image Upload</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="mx-auto max-w-[1400px] px-5 py-10 sm:px-8 sm:py-12 lg:px-14 lg:py-16">
            <div class="flex flex-col gap-6 overflow-x-auto pb-1 lg:flex-row lg:items-start lg:gap-6">
                <aside class="w-full shrink-0 lg:w-[450px] lg:min-w-[450px] lg:max-w-[450px]">
                    <div class="overflow-hidden rounded-[12px] bg-white p-[5px] shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0]">
                        <div class="rounded-[5px] bg-[#E8F5E9] px-5 py-6 text-center">
                            <div class="relative mx-auto h-[100px] w-[100px]">
                                <img
                                    src="{{ $myProfile['avatarUrl'] }}"
                                    alt=""
                                    class="h-full w-full rounded-full object-cover ring-2 ring-white"
                                    width="100"
                                    height="100"
                                    loading="lazy"
                                    decoding="async"
                                />
                                @if (($activeSection ?? '') === 'personal')
                                    <button
                                        type="button"
                                        data-profile-jump-upload
                                        class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#66A157] text-white shadow-md transition hover:bg-[#5a9048]"
                                        aria-label="Edit profile photo"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                @else
                                    <a
                                        href="{{ route('player.my-profile') }}#mp-avatar-personal"
                                        class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#66A157] text-white shadow-md transition hover:bg-[#5a9048]"
                                        aria-label="Edit profile photo"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                            <h2 class="mt-4 text-[18px] font-bold leading-tight text-[#333333]">{{ $myProfile['name'] }}</h2>
                            <p class="mt-1 text-[14px] font-medium text-[#666666]">{{ $myProfile['roleLine'] }}</p>
                        </div>
                        @include('player.profile._sidebar')
                    </div>
                </aside>

                <div class="min-w-0 w-full space-y-6 lg:w-[810px] lg:min-w-[810px] lg:max-w-[810px] lg:shrink-0">
                    @if (session('status') && ! in_array($activeSection ?? '', ['upload', 'location'], true))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('profile_panel')
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @stack('profile_scripts')
@endpush
