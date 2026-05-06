@extends('layouts.website')

@section('header_theme', 'light')
@section('header_logo_path', 'frontend/images/logo-2.png')

@section('page_bg', '#E4F7E7')
@php
    $tab = old('registration_tab', 'singles');
    $isDoubles = $tab === 'doubles';
    $heroRegisterImg = 'frontend/images/front-view-couple-tennis-court 1.png';
    $accentSingles = '#5DA44E';
    $accentDoubles = '#5FA252';
@endphp

@section('body_class', 'min-h-screen overflow-x-hidden font-sans antialiased text-[#1a1a1a]')

@section('title', 'Tournament Registration | '.config('app.name', 'playptl'))
@section('meta_description', 'Register for Premier Tennis League tournaments — Singles or Doubles.')

@section('content')
    <div class="register-page flex min-h-[calc(100vh-200px)] items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-[1360px] overflow-hidden rounded-[12px] bg-white shadow-[0_8px_30px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col lg:flex-row lg:items-stretch">
                <div class="flex w-full flex-col lg:w-1/2 lg:max-w-[50%]">
                    {{-- No outer scroll: Singles fits without scrollbar; Doubles panel scrolls internally --}}
                    <div class="px-6 py-6 sm:px-8 sm:py-7">
                        <h1 class="text-center text-lg font-bold text-[#222] sm:text-xl">Tournament Registration</h1>

                        {{-- Tabs: Singles left (first), Doubles right — mockup style --}}
                        <div class="mt-5 flex gap-3 sm:gap-4">
                            <button type="button" id="tab-singles"
                                class="tab-btn flex-1 rounded-[8px] py-2.5 text-center text-sm font-semibold transition-colors sm:text-[15px] {{ $isDoubles ? 'border border-[#d1d1d1] bg-white text-[#222] shadow-sm' : 'border border-transparent text-white shadow-sm' }}"
                                style="{{ ! $isDoubles ? 'background-color:'.$accentSingles : '' }}">
                                Singles
                            </button>
                            <button type="button" id="tab-doubles"
                                class="tab-btn flex-1 rounded-[8px] py-2.5 text-center text-sm font-semibold transition-colors sm:text-[15px] {{ $isDoubles ? 'border border-transparent text-white shadow-sm' : 'border border-[#d1d1d1] bg-white text-[#222] shadow-sm' }}"
                                style="{{ $isDoubles ? 'background-color:'.$accentDoubles : '' }}">
                                Doubles
                            </button>
                        </div>

                        <form id="tournament-register-form" class="mt-6 space-y-0" method="POST" action="{{ route('register') }}" novalidate>
                            @csrf
                            <input type="hidden" name="role" value="player">
                            <input type="hidden" name="name" id="computed_name" value="{{ old('name') }}">
                            <input type="hidden" name="registration_tab" id="registration_tab" value="{{ $tab }}">

                            {{-- Singles first tab: compact spacing, no inner scroll --}}
                            <div id="panel-singles" class="tab-panel {{ $isDoubles ? 'hidden' : '' }}" data-tab-panel="singles">
                                <fieldset id="fs-singles" class="register-fs-singles m-0 min-w-0 space-y-3 border-0 p-0 @disabled($tab === 'doubles')">
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">First Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="singles_first" value="{{ old('singles_first') }}" placeholder="First name"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="given-name">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Last Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="singles_last" value="{{ old('singles_last') }}" placeholder="Last name"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="family-name">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Email <span class="text-red-600">*</span></label>
                                            <input type="email" name="email" id="singles_email" value="{{ old('email') }}" placeholder="Email"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="email">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Phone Number <span class="text-red-600">*</span></label>
                                            <input type="tel" name="phone_singles" value="{{ old('phone_singles') }}" placeholder="Phone"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="tel">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">City <span class="text-red-600">*</span></label>
                                            <input type="text" name="city_singles" value="{{ old('city_singles') }}" placeholder="City"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="address-level2">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">State <span class="text-red-600">*</span></label>
                                            <input type="text" name="state_singles" value="{{ old('state_singles') }}" placeholder="State"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="address-level1">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Age Group <span class="text-red-600">*</span></label>
                                            <select name="age_group_singles"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select</option>
                                                <option value="under-18" @selected(old('age_group_singles') === 'under-18')>Under 18</option>
                                                <option value="18-21" @selected(old('age_group_singles') === '18-21')>18–21</option>
                                                <option value="above-21" @selected(old('age_group_singles', 'above-21') === 'above-21')>Above 21</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Skill Level <span class="text-red-600">*</span></label>
                                            <select name="skill_singles"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select</option>
                                                @foreach (['1', '2', '3', '4', '5'] as $lvl)
                                                    <option value="{{ $lvl }}" @selected(old('skill_singles') === $lvl)>{{ $lvl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Sex <span class="text-red-600">*</span></label>
                                            <select name="sex_singles"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select</option>
                                                <option value="male" @selected(old('sex_singles') === 'male')>Male</option>
                                                <option value="female" @selected(old('sex_singles') === 'female')>Female</option>
                                                <option value="other" @selected(old('sex_singles') === 'other')>Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Tournament <span class="text-red-600">*</span></label>
                                            <select name="tournament_singles"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select tournament</option>
                                                <option value="summer-2026" @selected(old('tournament_singles') === 'summer-2026')>Summer League 2026</option>
                                                <option value="winter-2026" @selected(old('tournament_singles') === 'winter-2026')>Winter Open 2026</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Group <span class="text-red-600">*</span></label>
                                        <select name="group_singles"
                                            class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                            <option value="">Select group</option>
                                            <option value="a" @selected(old('group_singles') === 'a')>Group A</option>
                                            <option value="b" @selected(old('group_singles') === 'b')>Group B</option>
                                            <option value="c" @selected(old('group_singles') === 'c')>Group C</option>
                                        </select>
                                    </div>

                                    <div class="rounded-[8px] border border-[#eeeeee] bg-[#fafafa] px-3 py-2">
                                        <p class="text-[12px] font-semibold text-[#333]">Entry Fee: <span class="text-[#5DA44E]">$30.00</span></p>
                                        <label class="mb-0.5 mt-2 block text-[12px] font-bold text-[#222]">Payment card</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[#888]" aria-hidden="true">💳</span>
                                            <input type="text" name="card_placeholder" readonly placeholder="Card Number · MM / YY · CVC"
                                                class="reg-input h-9 w-full rounded-[6px] border border-[#dddddd] bg-white py-1.5 pl-9 pr-2 text-[12px] text-[#888]"
                                                tabindex="-1">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 border-t border-[#eee] pt-3">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Password <span class="text-red-600">*</span></label>
                                            <input type="password" name="password" required autocomplete="new-password"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                placeholder="Create password">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Confirm Password <span class="text-red-600">*</span></label>
                                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                placeholder="Confirm password">
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="mt-1 h-11 w-full rounded-[8px] bg-[#5DA44E] text-[14px] font-bold text-white transition-opacity hover:opacity-95">
                                        Submit
                                    </button>
                                </fieldset>
                            </div>

                            {{-- Doubles: taller form scrolls inside this panel only --}}
                            <div id="panel-doubles" class="tab-panel {{ $isDoubles ? '' : 'hidden' }} max-h-[min(72vh,620px)] overflow-y-auto overflow-x-hidden overscroll-contain pr-0.5 [-webkit-overflow-scrolling:touch]" data-tab-panel="doubles">
                                <fieldset id="fs-doubles" class="m-0 min-w-0 space-y-4 border-0 pb-1 pt-0 @disabled($tab === 'singles')">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">First Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="d1_first" value="{{ old('d1_first') }}" placeholder="First name"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="given-name">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Last Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="d1_last" value="{{ old('d1_last') }}" placeholder="Last name"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="family-name">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Email <span class="text-red-600">*</span></label>
                                            <input type="email" name="email" id="doubles_email" value="{{ old('email') }}" placeholder="Email"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="email">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Phone Number <span class="text-red-600">*</span></label>
                                            <input type="tel" name="phone_doubles" value="{{ old('phone_doubles') }}" placeholder="Phone"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="tel">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">City <span class="text-red-600">*</span></label>
                                            <input type="text" name="city_doubles" value="{{ old('city_doubles') }}" placeholder="City"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="address-level2">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">State <span class="text-red-600">*</span></label>
                                            <input type="text" name="state_doubles" value="{{ old('state_doubles') }}" placeholder="State"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="address-level1">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Age Group <span class="text-red-600">*</span></label>
                                            <select name="age_group_doubles"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25">
                                                <option value="">Select</option>
                                                <option value="under-18" @selected(old('age_group_doubles') === 'under-18')>Under 18</option>
                                                <option value="18-21" @selected(old('age_group_doubles') === '18-21')>18–21</option>
                                                <option value="above-21" @selected(old('age_group_doubles', 'above-21') === 'above-21')>Above 21</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Skill Level <span class="text-red-600">*</span></label>
                                            <select name="skill_doubles"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25">
                                                <option value="">Select</option>
                                                @foreach (['1', '2', '3', '4', '5'] as $lvl)
                                                    <option value="{{ $lvl }}" @selected(old('skill_doubles') === $lvl)>{{ $lvl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[12px] font-bold text-black">Sex <span class="text-red-600">*</span></label>
                                        <select name="sex_doubles"
                                            class="h-11 w-full max-w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25 sm:max-w-[calc(50%-0.5rem)]">
                                            <option value="">Select</option>
                                            <option value="male" @selected(old('sex_doubles') === 'male')>Male</option>
                                            <option value="female" @selected(old('sex_doubles') === 'female')>Female</option>
                                            <option value="other" @selected(old('sex_doubles') === 'other')>Other</option>
                                        </select>
                                    </div>

                                    <div class="border-t border-[#e8e8e8] pt-4">
                                        <h2 class="text-center text-[14px] font-bold text-black underline decoration-[#5FA252] decoration-2 underline-offset-4">Second Player Details</h2>
                                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1 block text-[12px] font-bold text-black">First Name <span class="text-red-600">*</span></label>
                                                <input type="text" id="d2_first" value="{{ old('d2_first') }}" placeholder="First name"
                                                    class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                    autocomplete="off">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[12px] font-bold text-black">Last Name <span class="text-red-600">*</span></label>
                                                <input type="text" id="d2_last" value="{{ old('d2_last') }}" placeholder="Last name"
                                                    class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 border-t border-[#eee] pt-4">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Password <span class="text-red-600">*</span></label>
                                            <input type="password" name="password" required autocomplete="new-password"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                placeholder="Create password">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Confirm Password <span class="text-red-600">*</span></label>
                                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                placeholder="Confirm password">
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="h-12 w-full rounded-[8px] bg-[#5FA252] text-[15px] font-bold text-white transition-opacity hover:opacity-95">
                                        Submit
                                    </button>
                                </fieldset>
                            </div>
                        </form>
                    </div>

                    <div class="border-t border-[#eee] px-6 py-3 text-center text-[13px] text-[#666] lg:border-t-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-bold text-[#5DA44E] underline hover:opacity-90">Login</a>
                    </div>
                </div>

                <div class="relative min-h-[260px] w-full lg:min-h-0 lg:w-1/2 lg:max-w-[50%] lg:self-stretch">
                    <img id="register-hero-img" src="{{ asset($heroRegisterImg) }}" alt="Couple on tennis court"
                        class="h-full min-h-[260px] w-full object-cover lg:absolute lg:inset-0 lg:min-h-full lg:rounded-r-[12px]"
                        width="520" height="640" loading="eager" decoding="async"
                        data-img-singles="{{ asset($heroRegisterImg) }}"
                        data-img-doubles="{{ asset($heroRegisterImg) }}">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var greenSingles = '#5DA44E';
    var greenDoubles = '#5FA252';
    var tabSingles = document.getElementById('tab-singles');
    var tabDoubles = document.getElementById('tab-doubles');
    var panelSingles = document.getElementById('panel-singles');
    var panelDoubles = document.getElementById('panel-doubles');
    var heroImg = document.getElementById('register-hero-img');
    var registrationTab = document.getElementById('registration_tab');
    var form = document.getElementById('tournament-register-form');
    var computedName = document.getElementById('computed_name');
    var fsDoubles = document.getElementById('fs-doubles');
    var fsSingles = document.getElementById('fs-singles');

    function setActiveTab(which) {
        var isDoubles = which === 'doubles';
        registrationTab.value = which;

        if (panelDoubles) panelDoubles.classList.toggle('hidden', !isDoubles);
        if (panelSingles) panelSingles.classList.toggle('hidden', isDoubles);

        if (tabDoubles) {
            tabDoubles.style.backgroundColor = isDoubles ? greenDoubles : '#fff';
            tabDoubles.style.color = isDoubles ? '#fff' : '#222';
            tabDoubles.style.border = isDoubles ? 'none' : '1px solid #d1d1d1';
            tabDoubles.style.boxShadow = isDoubles ? '0 1px 2px rgba(0,0,0,0.06)' : 'none';
        }
        if (tabSingles) {
            tabSingles.style.backgroundColor = !isDoubles ? greenSingles : '#fff';
            tabSingles.style.color = !isDoubles ? '#fff' : '#222';
            tabSingles.style.border = !isDoubles ? 'none' : '1px solid #d1d1d1';
            tabSingles.style.boxShadow = !isDoubles ? '0 1px 2px rgba(0,0,0,0.06)' : 'none';
        }

        if (heroImg) heroImg.src = isDoubles ? heroImg.dataset.imgDoubles : heroImg.dataset.imgSingles;

        if (fsDoubles) fsDoubles.disabled = !isDoubles;
        if (fsSingles) fsSingles.disabled = isDoubles;
    }

    tabDoubles.addEventListener('click', function () { setActiveTab('doubles'); });
    tabSingles.addEventListener('click', function () { setActiveTab('singles'); });

    form.addEventListener('submit', function () {
        var which = registrationTab.value;
        var sFn = document.getElementById('singles_first');
        var sLn = document.getElementById('singles_last');
        var d1f = document.getElementById('d1_first');
        var d1l = document.getElementById('d1_last');
        var d2f = document.getElementById('d2_first');
        var d2l = document.getElementById('d2_last');

        if (which === 'singles') {
            computedName.value = (sFn.value.trim() + ' ' + sLn.value.trim()).trim();
        } else {
            var a = (d1f.value.trim() + ' ' + d1l.value.trim()).trim();
            var b = (d2f.value.trim() + ' ' + d2l.value.trim()).trim();
            computedName.value = (a + ' & ' + b).trim();
        }
    });

    setActiveTab(@json($tab));
})();
</script>
@endpush
