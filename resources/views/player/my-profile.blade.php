@extends('layouts.website')

@section('title', 'My Profile | '.config('app.name', 'playptl'))
@section('meta_description', 'Update your player profile and profile photo.')
@section('page_bg', '#E8F7E9')
@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')
@section('suppress_global_status', true)

@section('content')
    @php
        $mp = $myProfile;
        $profileInputClass =
            'w-full rounded-md border border-[#D1D5DB] bg-white px-3.5 py-2.5 text-[15px] text-[#374151] shadow-sm placeholder:text-[#9CA3AF] focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157] sm:text-[16px]';
        $profileInputReadonlyClass =
            'w-full cursor-not-allowed rounded-md border border-[#D1D5DB] bg-[#F9FAFB] px-3.5 py-2.5 text-[15px] text-[#6B7280] shadow-sm focus:border-[#D1D5DB] focus:ring-0 sm:text-[16px]';
        $profileLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]';
        $profileNavActive = 'w-full rounded-lg bg-[#66A157] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]';
        $profileNavInactive =
            'w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#424242] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]';
        $pwdLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#333333] sm:text-[13px]';
        $pwdInputClass =
            'w-full rounded-lg border border-[#E0E0E0] bg-white py-2.5 pl-3.5 pr-11 text-[15px] font-normal text-[#333333] shadow-sm placeholder:text-[#9E9E9E] focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157] sm:text-[16px]';
        $pwdEyeBtn =
            'absolute inset-y-0 right-0 flex items-center px-3 text-[#9E9E9E] transition-colors hover:text-[#757575] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#66A157] focus-visible:ring-offset-1 rounded-r-lg';
        $scheduleLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#000000] sm:text-[13px]';
        $scheduleInputClass =
            'w-full rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 text-[15px] font-normal text-[#000000] shadow-sm placeholder:text-[#757575] focus:border-[#5FA052] focus:outline-none focus:ring-1 focus:ring-[#5FA052] sm:text-[16px]';
        $scheduleSelectClass =
            'w-full appearance-none rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 pr-10 text-[15px] text-[#757575] shadow-sm focus:border-[#5FA052] focus:outline-none focus:ring-1 focus:ring-[#5FA052] sm:text-[16px]';
        $scheduleInputIconPad = 'pr-10';
        $scheduleFieldIcon = 'pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#757575]';
        $uploadMatchLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#333333] sm:text-[13px]';
        $uploadMatchSelectClass =
            'w-full appearance-none rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 pr-10 text-[15px] text-[#666666] shadow-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157] sm:text-[16px]';
        $uploadGridImages = [asset('frontend/images/champion.png'), asset('frontend/images/league-hero.png')];
        $uploadDropzoneClass =
            'flex min-h-[200px] cursor-default flex-col items-center justify-center rounded-lg border-2 border-dashed border-[#CCCCCC] bg-[#F5F5F5] px-6 py-10 text-center sm:min-h-[220px]';
        $uploadNotesLabelClass = 'mb-1.5 block text-[12px] font-bold text-[#333333] sm:text-[13px]';
        $uploadNotesClass =
            'min-h-[120px] w-full resize-y rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 text-[15px] text-[#333333] placeholder:text-[#999999] shadow-sm focus:border-[#5DA051] focus:outline-none focus:ring-1 focus:ring-[#5DA051] sm:text-[16px]';
    @endphp

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
                        <div class="rounded-[5px] bg-[#E8F5E9] px-5 py-6 text-center">
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
                                    class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#66A157] text-white shadow-md transition hover:bg-[#5a9048]"
                                    aria-label="Edit profile photo"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <h2 class="mt-4 text-[18px] font-bold leading-tight text-[#333333]">{{ $mp['name'] }}</h2>
                            <p class="mt-1 text-[14px] font-medium text-[#666666]">{{ $mp['roleLine'] }}</p>
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
                    @if (session('status'))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div
                        id="profile-section-personal"
                        class="profile-section-panel overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8"
                        data-profile-section="personal"
                    >
                        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Personal Information</h3>
                        <form class="space-y-5" action="{{ route('player.profile.update') }}" method="post" enctype="multipart/form-data">
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
                            <div class="sm:col-span-2">
                                <label for="mp-avatar-personal" class="{{ $profileLabelClass }}">Profile photo</label>
                                <input
                                    id="mp-avatar-personal"
                                    name="avatar"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="block w-full cursor-pointer text-[15px] text-[#424242] file:mr-4 file:cursor-pointer file:rounded-lg file:border file:border-[#D1D5DB] file:bg-[#F3F4F6] file:px-4 file:py-2.5 file:text-[14px] file:font-semibold file:text-[#333333] hover:file:bg-[#E5E7EB] sm:text-[16px]"
                                />
                                <p class="mt-1.5 text-[12px] font-normal text-[#666666] sm:text-[13px]">JPG, PNG, or WebP up to 2MB.</p>
                            </div>
                            <div class="flex flex-wrap gap-3 pt-2">
                                <a href="{{ route('player.my-profile') }}" class="rounded-lg border border-[#E0E0E0] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#424242] transition hover:bg-[#E5E7EB] sm:text-[15px]">
                                    Cancel
                                </a>
                                <button type="submit" class="rounded-lg bg-[#66A157] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#5a9048] sm:text-[15px]">
                                    Save Change
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="profile-section-password" class="profile-section-panel hidden overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8" data-profile-section="password">
                        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Password &amp; Security</h3>

                        @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
                            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[14px] font-semibold text-red-700">
                                {{ $errors->first('current_password') ?: $errors->first('password') ?: $errors->first('password_confirmation') }}
                            </div>
                        @endif

                        <form class="space-y-5" method="POST" action="{{ route('player.password.update') }}">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="mp-current-password" class="{{ $pwdLabelClass }}">Current Password</label>
                                <div class="relative">
                                    <input
                                        id="mp-current-password"
                                        name="current_password"
                                        type="password"
                                        required
                                        autocomplete="current-password"
                                        class="{{ $pwdInputClass }} @error('current_password') border-red-400 focus:border-red-400 focus:ring-red-400 @enderror"
                                    />
                                    <button type="button" class="{{ $pwdEyeBtn }}" aria-label="Show password" data-password-toggle>
                                        <svg class="h-5 w-5" data-password-eye fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg class="hidden h-5 w-5" data-password-eye-off fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label for="mp-new-password" class="{{ $pwdLabelClass }}">New Password</label>
                                <div class="relative">
                                    <input
                                        id="mp-new-password"
                                        name="password"
                                        type="password"
                                        required
                                        autocomplete="new-password"
                                        class="{{ $pwdInputClass }} @error('password') border-red-400 focus:border-red-400 focus:ring-red-400 @enderror"
                                    />
                                    <button type="button" class="{{ $pwdEyeBtn }}" aria-label="Show password" data-password-toggle>
                                        <svg class="h-5 w-5" data-password-eye fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg class="hidden h-5 w-5" data-password-eye-off fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label for="mp-confirm-password" class="{{ $pwdLabelClass }}">Confirm New Password</label>
                                <div class="relative">
                                    <input
                                        id="mp-confirm-password"
                                        name="password_confirmation"
                                        type="password"
                                        required
                                        autocomplete="new-password"
                                        class="{{ $pwdInputClass }}"
                                    />
                                    <button type="button" class="{{ $pwdEyeBtn }}" aria-label="Show password" data-password-toggle>
                                        <svg class="h-5 w-5" data-password-eye fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg class="hidden h-5 w-5" data-password-eye-off fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 pt-2">
                                <button type="button" data-profile-cancel-password class="rounded-lg border border-[#E0E0E0] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#E8EAED] sm:text-[15px]">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-lg bg-[#66A157] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#5a9048] sm:text-[15px]">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <div
                        id="profile-section-location"
                        class="profile-section-panel hidden overflow-hidden rounded-[10px] bg-white p-6 shadow-[0_4px_12px_rgba(0,0,0,0.05)] ring-1 ring-[#E0E0E0] sm:p-8 sm:px-[28px] sm:py-[26px]"
                        data-profile-section="location"
                    >
                        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#000000] sm:text-[20px]">Players Schedule</h3>

                        {{-- Static UI only (no submit backend) --}}
                        <form class="space-y-5" action="#" method="post" onsubmit="return false;">
                            <div>
                                <label for="mp-schedule-match" class="{{ $scheduleLabelClass }}">Match Players</label>
                                <div class="relative">
                                    <select id="mp-schedule-match" name="schedule_match" class="{{ $scheduleSelectClass }}">
                                        <option selected>Arjun Kumar Vs Rahul Singh</option>
                                    </select>
                                    <span class="{{ $scheduleFieldIcon }}" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="mp-schedule-date" class="{{ $scheduleLabelClass }}">
                                        Date <span class="font-bold text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="mp-schedule-date"
                                            name="schedule_date"
                                            type="text"
                                            readonly
                                            placeholder="dd/mm/yyyy"
                                            class="{{ $scheduleInputClass }} {{ $scheduleInputIconPad }} cursor-default bg-white"
                                        />
                                        <span class="{{ $scheduleFieldIcon }}" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label for="mp-schedule-time" class="{{ $scheduleLabelClass }}">
                                        Time <span class="font-bold text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="mp-schedule-time"
                                            name="schedule_time"
                                            type="text"
                                            readonly
                                            placeholder="00:00"
                                            class="{{ $scheduleInputClass }} {{ $scheduleInputIconPad }} cursor-default bg-white"
                                        />
                                        <span class="{{ $scheduleFieldIcon }}" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="mp-schedule-venue" class="{{ $scheduleLabelClass }}">Venue / Club</label>
                                <input
                                    id="mp-schedule-venue"
                                    name="schedule_venue"
                                    type="text"
                                    value=""
                                    placeholder="Enter venue or club name"
                                    class="{{ $scheduleInputClass }}"
                                    autocomplete="off"
                                />
                            </div>

                            <div class="flex flex-wrap gap-3 pt-2">
                                <button type="button" data-profile-cancel-location class="rounded-lg border border-[#DDDDDD] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#E8EAED] sm:text-[15px]">
                                    Cancel
                                </button>
                                <button type="button" class="rounded-lg bg-[#66A157] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#5a9048] sm:text-[15px]">
                                    Save Change
                                </button>
                            </div>
                        </form>
                    </div>

                    <div
                        id="profile-section-upload"
                        class="profile-section-panel hidden overflow-hidden rounded-[10px] bg-white p-6 shadow-[0_4px_12px_rgba(0,0,0,0.05)] ring-1 ring-[#E0E0E0] sm:p-8"
                        data-profile-section="upload"
                    >
                        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Upload Match Images</h3>

                        {{-- Step 1: gallery — hidden when step 2 is active (replaced, not stacked) --}}
                        <div id="upload-step-gallery">
                            <div class="mb-6">
                                <label for="mp-upload-match" class="{{ $uploadMatchLabelClass }}">Match Players</label>
                                <div class="relative">
                                    <select id="mp-upload-match" class="{{ $uploadMatchSelectClass }}">
                                        <option selected>Arjun Kumar Vs Rahul Singh</option>
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#666666]" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-[14px]">
                                @for ($u = 0; $u < 12; $u++)
                                    <div class="overflow-hidden rounded-lg ring-1 ring-[#E8E8E8]">
                                        <img
                                            src="{{ $uploadGridImages[$u % 2] }}"
                                            alt=""
                                            class="aspect-square w-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                            width="320"
                                            height="320"
                                        />
                                    </div>
                                @endfor
                            </div>

                            <div class="mt-8 flex justify-center sm:mt-10">
                                <button
                                    type="button"
                                    data-upload-go-step2
                                    class="inline-flex min-w-[200px] items-center justify-center gap-2.5 rounded-lg border-2 border-dashed border-[#C8C8C8] bg-[#EEEEEE] px-10 py-3.5 text-[14px] font-semibold text-[#666666] sm:min-w-[220px] sm:text-[15px]"
                                >
                                    <svg class="h-5 w-5 shrink-0 text-[#666666]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    Upload Image
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: drag-drop + notes (shown instead of step 1) --}}
                        <div id="upload-step-files" class="hidden">
                            <div class="mb-6">
                                <label for="mp-upload-match-step2" class="{{ $uploadMatchLabelClass }}">Match Players</label>
                                <div class="relative">
                                    <select id="mp-upload-match-step2" class="{{ $uploadMatchSelectClass }}">
                                        <option selected>Arjun Kumar Vs Rahul Singh</option>
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#666666]" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div class="{{ $uploadDropzoneClass }}" aria-hidden="true">
                                <svg class="mb-3 h-10 w-10 text-[#999999]" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                <p class="text-[15px] font-bold text-[#333333] sm:text-[16px]">
                                    Drag &amp; drop images here,
                                    <span class="font-bold text-[#5DA051]"> or browse file</span>
                                </p>
                                <p class="mt-2 text-[12px] font-normal text-[#999999] sm:text-[13px]">JPG, PNG, WEBP — max 10 MB each</p>
                            </div>

                            <div class="mt-6">
                                <label for="mp-upload-notes" class="{{ $uploadNotesLabelClass }}">Match Notes (Optional)</label>
                                <textarea
                                    id="mp-upload-notes"
                                    name="upload_match_notes"
                                    rows="5"
                                    placeholder="Type here.."
                                    class="{{ $uploadNotesClass }}"
                                ></textarea>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3 pt-1">
                                <button
                                    type="button"
                                    data-upload-back-gallery
                                    class="rounded-lg border border-[#DDDDDD] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#E8EAED] sm:text-[15px]"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg bg-[#5DA051] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#539547] sm:text-[15px]"
                                >
                                    Add
                                </button>
                            </div>
                        </div>
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
                'w-full rounded-lg bg-[#66A157] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]';
            var inactiveNav =
                'w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#424242] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]';
            var buttons = nav.querySelectorAll('[data-profile-section]');
            var panels = document.querySelectorAll('.profile-section-panel');
            var uploadStepGallery = document.getElementById('upload-step-gallery');
            var uploadStepFiles = document.getElementById('upload-step-files');

            function setUploadWizardStep(step) {
                if (!uploadStepGallery || !uploadStepFiles) return;
                var showGallery = step === 'gallery';
                uploadStepGallery.classList.toggle('hidden', !showGallery);
                uploadStepFiles.classList.toggle('hidden', showGallery);
            }

            function showSection(sectionId) {
                panels.forEach(function (el) {
                    var match = el.getAttribute('data-profile-section') === sectionId;
                    el.classList.toggle('hidden', !match);
                });
                buttons.forEach(function (btn) {
                    var on = btn.getAttribute('data-profile-section') === sectionId;
                    btn.className = on ? activeNav : inactiveNav;
                });
                if (sectionId === 'upload') {
                    setUploadWizardStep('gallery');
                }
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    showSection(btn.getAttribute('data-profile-section'));
                });
            });

            document.querySelectorAll('[data-upload-go-step2]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var selA = document.getElementById('mp-upload-match');
                    var selB = document.getElementById('mp-upload-match-step2');
                    if (selA && selB) {
                        selB.selectedIndex = selA.selectedIndex;
                    }
                    setUploadWizardStep('files');
                });
            });

            document.querySelectorAll('[data-upload-back-gallery]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setUploadWizardStep('gallery');
                });
            });

            document.querySelectorAll('[data-profile-jump-upload]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    showSection('personal');
                    document.getElementById('mp-avatar-personal')?.click();
                });
            });

            document.querySelectorAll('[data-profile-cancel-password]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    showSection('personal');
                });
            });

            document.querySelectorAll('[data-profile-cancel-location]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    showSection('personal');
                });
            });

            document.querySelectorAll('[data-password-toggle]').forEach(function (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    var wrap = toggleBtn.closest('.relative');
                    var input = wrap ? wrap.querySelector('input') : null;
                    var eye = toggleBtn.querySelector('[data-password-eye]');
                    var eyeOff = toggleBtn.querySelector('[data-password-eye-off]');
                    if (!input || !eye || !eyeOff) return;
                    if (input.type === 'password') {
                        input.type = 'text';
                        eye.classList.add('hidden');
                        eyeOff.classList.remove('hidden');
                        toggleBtn.setAttribute('aria-label', 'Hide password');
                    } else {
                        input.type = 'password';
                        eye.classList.remove('hidden');
                        eyeOff.classList.add('hidden');
                        toggleBtn.setAttribute('aria-label', 'Show password');
                    }
                });
            });

            @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation') || session('status') === 'Password changed successfully.')
                showSection('password');
            @endif
        })();
    </script>
@endpush
