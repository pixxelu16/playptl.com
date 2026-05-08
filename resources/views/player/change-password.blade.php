@extends('layouts.website')

@section('title', 'Change Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Change your player account password.')
@section('page_bg', '#E8F5E9')
@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')
@section('suppress_global_status', true)

@section('content')
    <main class="bg-[#E8F5E9] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#2d4a2d] antialiased">
        <section class="relative flex h-[520px] min-h-[520px] flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full min-h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
            </video>
            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>
            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-20 pt-36 sm:px-8 sm:pt-40 lg:px-14 lg:pt-44">
                <header class="max-w-5xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#B4F000] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#B4F000]">Change Password</span>
                    </nav>
                    <h1 class="league-1 text-[clamp(4rem,10vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">CHANGE</span><span class="text-[#B4F000]"> PASSWORD</span>
                    </h1>
                </header>
            </div>
        </section>

        <section class="mx-auto max-w-[760px] px-5 py-10 sm:px-8 sm:py-12 lg:px-14 lg:py-16">
            <div class="rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
                <h2 class="mb-2 text-[20px] font-bold text-[#212121]">Password &amp; Security</h2>
                <p class="mb-6 text-[14px] text-[#757575]">Use a strong password to keep your player account secure.</p>

                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[14px] font-semibold text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="space-y-5" method="POST" action="{{ route('player.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="current_password" class="mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]">Current Password</label>
                        <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full rounded-md border border-[#D1D5DB] bg-white px-3.5 py-2.5 text-[15px] text-[#374151] shadow-sm focus:border-[#62A351] focus:outline-none focus:ring-1 focus:ring-[#62A351] sm:text-[16px]">
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]">New Password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-md border border-[#D1D5DB] bg-white px-3.5 py-2.5 text-[15px] text-[#374151] shadow-sm focus:border-[#62A351] focus:outline-none focus:ring-1 focus:ring-[#62A351] sm:text-[16px]">
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-md border border-[#D1D5DB] bg-white px-3.5 py-2.5 text-[15px] text-[#374151] shadow-sm focus:border-[#62A351] focus:outline-none focus:ring-1 focus:ring-[#62A351] sm:text-[16px]">
                    </div>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="{{ route('player.my-profile') }}" class="rounded-lg border border-[#E0E0E0] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#424242] transition hover:bg-[#E5E7EB] sm:text-[15px]">Cancel</a>
                        <button type="submit" class="rounded-lg bg-[#62A351] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#569649] sm:text-[15px]">Update Password</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection
