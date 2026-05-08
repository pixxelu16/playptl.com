@extends('layouts.website')

@section('title', 'My Profile | '.config('app.name', 'playptl'))
@section('meta_description', 'Update your player profile and profile photo.')
@section('page_bg', '#E8F5E9')
@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')
@section('suppress_global_status', true)

@section('content')
    @php
        $mp = $myProfile;
        $profileInputClass =
            'w-full rounded-md border border-[#D1D5DB] bg-white px-3.5 py-2.5 text-[15px] text-[#374151] shadow-sm placeholder:text-[#9CA3AF] focus:border-[#62A351] focus:outline-none focus:ring-1 focus:ring-[#62A351] sm:text-[16px]';
        $profileInputReadonlyClass =
            'w-full cursor-not-allowed rounded-md border border-[#D1D5DB] bg-[#F9FAFB] px-3.5 py-2.5 text-[15px] text-[#6B7280] shadow-sm focus:border-[#D1D5DB] focus:ring-0 sm:text-[16px]';
        $profileLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]';
        $profileNavActive = 'w-full rounded-lg bg-[#62A351] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]';
        $profileNavInactive =
            'w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#424242] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]';
    @endphp

    <main class="bg-[#E8F5E9] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#2d4a2d] antialiased">
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
                        <span class="text-[#B4F000]">My Profile</span>
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
                        <div class="rounded-[5px] bg-[#E1F0E1] px-5 py-6 text-center">
                            <div class="relative mx-auto h-[100px] w-[100px]">
                                <img
                                    src="{{ $mp['avatarUrl'] }}"
                                    alt=""
                                    class="h-full w-full rounded-full object-cover ring-2 ring-white"
                                    width="100"
                                    height="100"
                                    loading="lazy"
                                    decoding="async"
                                />
                                <button
                                    type="button"
                                    data-profile-jump-upload
                                    class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#62A351] text-white shadow-md transition hover:bg-[#5a9449]"
                                    aria-label="Edit profile photo"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <h2 class="mt-4 text-[18px] font-bold leading-tight text-[#212121]">{{ $mp['name'] }}</h2>
                            <p class="mt-1 text-[14px] font-medium text-[#757575]">{{ $mp['roleLine'] }}</p>
                        </div>
                        <nav id="profile-side-nav" class="space-y-2 p-4" aria-label="Profile sections">
                            <button type="button" data-profile-section="personal" class="{{ $profileNavActive }}">Personal Information</button>
                            <button type="button" data-profile-section="password" class="{{ $profileNavInactive }}">Password &amp; Security</button>
                            <button type="button" data-profile-section="location" class="{{ $profileNavInactive }}">Add Location</button>
                            <button type="button" data-profile-section="upload" class="{{ $profileNavInactive }}">Upload Image</button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-red-200 bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-red-600 transition-colors hover:bg-red-50 sm:text-[15px]">
                                    Logout
                                </button>
                            </form>
                        </nav>
                    </div>
                </aside>

                <div class="min-w-0 w-full space-y-6 lg:w-[810px] lg:min-w-[810px] lg:max-w-[810px] lg:shrink-0">
                    <div
                        id="profile-section-personal"
                        class="profile-section-panel overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8"
                        data-profile-section="personal"
                    >
                        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#212121] sm:text-[20px]">Personal Information</h3>
                        @if (session('status'))
                            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-700">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form class="space-y-5" action="{{ route('player.profile.update') }}" method="post">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="league_id" value="{{ $leagueId }}">
                            <input type="hidden" name="group_card_id" value="{{ $groupCardId }}">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="mp-first" class="{{ $profileLabelClass }}">First Name</label>
                                    <input id="mp-first" name="first_name" type="text" value="{{ old('first_name', $mp['firstName']) }}" placeholder="Enter first name" class="{{ $profileInputClass }}" autocomplete="given-name" />
                                </div>
                                <div>
                                    <label for="mp-last" class="{{ $profileLabelClass }}">Last Name</label>
                                    <input id="mp-last" name="last_name" type="text" value="{{ old('last_name', $mp['lastName']) }}" placeholder="Enter last name" class="{{ $profileInputClass }}" autocomplete="family-name" />
                                </div>
                                <div>
                                    <label for="mp-dob" class="{{ $profileLabelClass }}">Date Of Birth</label>
                                    <input id="mp-dob" name="date_of_birth" type="date" value="{{ old('date_of_birth', $mp['dob']) }}" class="{{ $profileInputClass }}" />
                                </div>
                                <div>
                                    <label for="mp-ntrp" class="{{ $profileLabelClass }}">NTRP Rating</label>
                                    <div class="relative">
                                        <select id="mp-ntrp" name="ntrp" class="{{ $profileInputClass }} appearance-none pr-10">
                                            <option value="" @selected(old('ntrp', $mp['ntrp']) === '')>Select rating</option>
                                            @foreach (['2.5', '3.0', '3.5', '4.0', '4.5', '5.0'] as $r)
                                                <option value="{{ $r }}" @selected(old('ntrp', $mp['ntrp']) === $r)>{{ $r }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="mp-email" class="{{ $profileLabelClass }}">Email Address</label>
                                    <input id="mp-email" type="email" value="{{ $mp['email'] }}" class="{{ $profileInputReadonlyClass }} bg-[#EEF2F0] text-[#6B7280]" disabled />
                                </div>
                                <div>
                                    <label for="mp-phone" class="{{ $profileLabelClass }}">Phone Number</label>
                                    <input id="mp-phone" name="phone" type="tel" value="{{ old('phone', $mp['phone']) }}" placeholder="Enter phone number" class="{{ $profileInputClass }}" autocomplete="tel" />
                                </div>
                                <div>
                                    <label for="mp-city" class="{{ $profileLabelClass }}">City / Location</label>
                                    <input id="mp-city" name="city" type="text" value="{{ old('city', $mp['city']) }}" placeholder="Enter city" class="{{ $profileInputClass }}" />
                                </div>
                                <div>
                                    <label for="mp-court" class="{{ $profileLabelClass }}">Home Court</label>
                                    <input id="mp-court" name="home_court" type="text" value="{{ old('home_court', $mp['homeCourt']) }}" placeholder="Home court" class="{{ $profileInputClass }}" />
                                </div>
                            </div>
                            <div>
                                <label for="mp-hand" class="{{ $profileLabelClass }}">Dominant Hand</label>
                                <div class="relative max-w-full sm:max-w-md">
                                    <select id="mp-hand" name="dominant_hand" class="{{ $profileInputClass }} appearance-none pr-10">
                                        @foreach (['Right', 'Left', 'Ambidextrous'] as $h)
                                            <option value="{{ $h }}" @selected(old('dominant_hand', $mp['dominantHand']) === $h)>{{ $h }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 pt-2">
                                <a href="{{ route('player.my-profile') }}" class="rounded-lg border border-[#E0E0E0] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#424242] transition hover:bg-[#E5E7EB] sm:text-[15px]">
                                    Cancel
                                </a>
                                <button type="submit" class="rounded-lg bg-[#62A351] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#569649] sm:text-[15px]">
                                    Save Change
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="profile-section-password" class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8" data-profile-section="password">
                        <h3 class="mb-2 text-[18px] font-bold text-[#212121] sm:text-[20px]">Password &amp; Security</h3>
                        <p class="text-[14px] text-[#757575]">This section will be available soon.</p>
                    </div>

                    <div id="profile-section-location" class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8" data-profile-section="location">
                        <h3 class="mb-2 text-[18px] font-bold text-[#212121] sm:text-[20px]">Add Location</h3>
                        <p class="text-[14px] text-[#757575]">Location tools will be available soon.</p>
                    </div>

                    <div id="profile-section-upload" class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8" data-profile-section="upload">
                        <h3 class="mb-2 text-[18px] font-bold text-[#212121] sm:text-[20px]">Upload Image</h3>
                        <p class="mb-5 text-[14px] text-[#757575]">Upload a JPG, PNG, or WebP profile photo up to 2MB.</p>
                        <form class="space-y-4" action="{{ route('player.profile.update') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="league_id" value="{{ $leagueId }}">
                            <input type="hidden" name="group_card_id" value="{{ $groupCardId }}">
                            <div>
                                <label for="mp-avatar" class="{{ $profileLabelClass }}">Profile Image</label>
                                <input id="mp-avatar" name="avatar" type="file" accept="image/*" class="{{ $profileInputClass }}">
                            </div>
                            <button type="submit" class="rounded-lg bg-[#62A351] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#569649] sm:text-[15px]">
                                Upload Image
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            var nav = document.getElementById('profile-side-nav');
            if (!nav) return;
            var activeNav =
                'w-full rounded-lg bg-[#62A351] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]';
            var inactiveNav =
                'w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#424242] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]';
            var buttons = nav.querySelectorAll('[data-profile-section]');
            var panels = document.querySelectorAll('.profile-section-panel');

            function showSection(sectionId) {
                panels.forEach(function (el) {
                    var match = el.getAttribute('data-profile-section') === sectionId;
                    el.classList.toggle('hidden', !match);
                });
                buttons.forEach(function (btn) {
                    var on = btn.getAttribute('data-profile-section') === sectionId;
                    btn.className = on ? activeNav : inactiveNav;
                });
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    showSection(btn.getAttribute('data-profile-section'));
                });
            });

            document.querySelectorAll('[data-profile-jump-upload]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    showSection('upload');
                    document.getElementById('mp-avatar')?.click();
                });
            });
        })();
    </script>
@endpush
