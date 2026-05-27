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
    $feeSingles = number_format((int) config('services.stripe.singles_amount_cents', 3000) / 100, 2);
    $feeDoubles = number_format((int) config('services.stripe.doubles_amount_cents', 4500) / 100, 2);
    $initialFee = $isDoubles ? $feeDoubles : $feeSingles;
    $registrationAgeBrackets = [
        'under-18' => 'Under 18',
        '18-21' => '18–21',
        '21-25' => '21–25',
        '26-30' => '26–30',
        '31-35' => '31–35',
        '36-40' => '36–40',
        '41-45' => '41–45',
        '46-50' => '46–50',
        'above-50' => 'Above 50',
    ];
    $registrationSkillLevelValues = ['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', 'not-sure'];
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

                        <div id="register-league-gate" data-closed-divisions='@json($registrationClosedDivisions ?? [])' hidden></div>

                        {{-- Singles form --}}
                        <form id="singles-register-form"
                            class="mt-6 space-y-0 {{ $isDoubles ? 'hidden' : '' }}"
                            method="POST"
                            action="{{ route('register') }}"
                            novalidate
                            data-registration-tab="singles"
                            data-stripe-key="{{ $stripePublishableKey ?? '' }}"
                            data-payment-intent-url="{{ route('register.payment-intent') }}"
                            data-register-url="{{ route('register') }}"
                            data-csrf="{{ csrf_token() }}"
                            data-fee="{{ $feeSingles }}">
                            @csrf
                            <input type="hidden" name="name" class="computed_name" value="{{ old('name') }}">
                            <input type="hidden" name="registration_tab" value="singles">
                            <input type="hidden" name="payment_intent_id" class="payment_intent_id" value="{{ old('payment_intent_id') }}">

                            {{-- Singles first tab: compact spacing, no inner scroll --}}
                            <div id="panel-singles" class="tab-panel" data-tab-panel="singles">
                                <fieldset class="register-fs-singles m-0 min-w-0 space-y-3 border-0 p-0">
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">First Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="singles_first" name="singles_first" value="{{ old('singles_first') }}" placeholder="First name"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="given-name" required>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Last Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="singles_last" name="singles_last" value="{{ old('singles_last') }}" placeholder="Last name"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="family-name" required>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Email <span class="text-red-600">*</span></label>
                                            <input type="email" name="email" id="singles_email" value="{{ old('email') }}" placeholder="Email"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="email" required>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Phone Number <span class="text-red-600">*</span></label>
                                            <input type="tel" name="phone_singles" value="{{ old('phone_singles') }}" placeholder="Phone"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" required>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Password <span class="text-red-600">*</span></label>
                                            <input type="password" name="password" required autocomplete="new-password"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                placeholder="Create password">
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Confirm Password <span class="text-red-600">*</span></label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                placeholder="Confirm password">
                                            <p class="password-match-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">City <span class="text-red-600">*</span></label>
                                            <input type="text" name="city_singles" value="{{ old('city_singles') }}" placeholder="City"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="address-level2" required>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">State <span class="text-red-600">*</span></label>
                                            <input type="text" name="state_singles" value="{{ old('state_singles') }}" placeholder="State"
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] placeholder:text-[#888] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25"
                                                autocomplete="address-level1" required>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Age Group <span class="text-red-600">*</span></label>
                                            <select name="age_group_singles"
                                                required
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select</option>
                                                @foreach ($registrationAgeBrackets as $ageValue => $ageLabel)
                                                    <option value="{{ $ageValue }}" @selected(old('age_group_singles') === $ageValue)>{{ $ageLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Skill Level <span class="text-red-600">*</span></label>
                                            <select name="skill_singles"
                                                required
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select</option>
                                                @foreach ($registrationSkillLevelValues as $skillValue)
                                                    <option value="{{ $skillValue }}" @selected(old('skill_singles') == $skillValue)>{{ $skillValue === 'not-sure' ? 'Not Sure' : $skillValue }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Sex <span class="text-red-600">*</span></label>
                                            <select name="sex_singles"
                                                required
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select</option>
                                                <option value="male" @selected(old('sex_singles') === 'male')>Male</option>
                                                <option value="female" @selected(old('sex_singles') === 'female')>Female</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[12px] font-bold text-[#222]">Tournament <span class="text-red-600">*</span></label>
                                            <select name="tournament_singles"
                                                required
                                                class="reg-input h-10 w-full rounded-[6px] border border-[#dddddd] bg-white px-3 text-[13px] text-[#333] focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/25">
                                                <option value="">Select tournament</option>
                                                @foreach ($registrationLeagues as $league)
                                                    <option value="{{ $league->id }}" @selected(old('tournament_singles') == $league->id)>{{ $league->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                </fieldset>
                            </div>

                            {{-- Singles payment --}}
                            <div class="mt-4 rounded-[10px] border border-[#eeeeee] bg-[#fafafa] px-4 py-3">
                                <p class="text-[13px] font-semibold text-[#333]">Entry Fee: <span class="font-bold">${{ $feeSingles }}</span></p>
                                <label class="mb-1 mt-2 block text-[12px] font-bold text-black">Payment card <span class="text-red-600">*</span></label>
                                <div class="stripe-card-element mt-1 min-h-[46px] rounded-[10px] border border-[#d7e6d7] bg-white px-3 py-3 text-[14px] text-[#111] shadow-[inset_0_1px_0_rgba(0,0,0,0.02)]"></div>
                                <p class="stripe-card-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
                            </div>

                            <button type="submit"
                                class="disable-button mt-3 h-12 w-full rounded-[8px] bg-[#5DA44E] text-[15px] font-bold text-white transition-opacity hover:opacity-95">
                                Submit
                            </button>

                            <div class="common-loader mt-3 hidden rounded-[10px] border border-[#e5e7eb] bg-[#f9fafb] px-3 py-2 text-[13px] text-[#374151]">
                                Processing...
                            </div>
                            <div class="custom_register_form_res mt-3 text-sm"></div>
                        </form>

                        {{-- Doubles form --}}
                        <form id="doubles-register-form"
                            class="mt-6 space-y-0 {{ $isDoubles ? '' : 'hidden' }}"
                            method="POST"
                            action="{{ route('register') }}"
                            novalidate
                            data-registration-tab="doubles"
                            data-stripe-key="{{ $stripePublishableKey ?? '' }}"
                            data-payment-intent-url="{{ route('register.payment-intent') }}"
                            data-register-url="{{ route('register') }}"
                            data-csrf="{{ csrf_token() }}"
                            data-fee="{{ $feeDoubles }}">
                            @csrf
                            <input type="hidden" name="name" class="computed_name" value="{{ old('name') }}">
                            <input type="hidden" name="registration_tab" value="doubles">
                            <input type="hidden" name="payment_intent_id" class="payment_intent_id" value="{{ old('payment_intent_id') }}">

                            <div id="panel-doubles" class="tab-panel max-h-[min(72vh,620px)] overflow-y-auto overflow-x-hidden overscroll-contain pr-0.5 [-webkit-overflow-scrolling:touch]" data-tab-panel="doubles">
                                <fieldset class="m-0 min-w-0 space-y-4 border-0 pb-1 pt-0">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">First Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="d1_first" name="d1_first" value="{{ old('d1_first') }}" placeholder="First name"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="given-name" required>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Last Name <span class="text-red-600">*</span></label>
                                            <input type="text" id="d1_last" name="d1_last" value="{{ old('d1_last') }}" placeholder="Last name"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="family-name" required>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Email <span class="text-red-600">*</span></label>
                                            <input type="email" name="email" id="doubles_email" value="{{ old('email') }}" placeholder="Email"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="email" required>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Phone Number <span class="text-red-600">*</span></label>
                                            <input type="tel" name="phone_doubles" value="{{ old('phone_doubles') }}" placeholder="Phone"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" required>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                            <p class="password-match-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">City <span class="text-red-600">*</span></label>
                                            <input type="text" name="city_doubles" value="{{ old('city_doubles') }}" placeholder="City"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="address-level2" required>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">State <span class="text-red-600">*</span></label>
                                            <input type="text" name="state_doubles" value="{{ old('state_doubles') }}" placeholder="State"
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="address-level1" required>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Age Group <span class="text-red-600">*</span></label>
                                            <select name="age_group_doubles"
                                                required
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25">
                                                <option value="">Select</option>
                                                @foreach ($registrationAgeBrackets as $ageValue => $ageLabel)
                                                    <option value="{{ $ageValue }}" @selected(old('age_group_doubles') === $ageValue)>{{ $ageLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Skill Level <span class="text-red-600">*</span></label>
                                            <select name="skill_doubles"
                                                required
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25">
                                                <option value="">Select</option>
                                                @foreach ($registrationSkillLevelValues as $skillValue)
                                                    <option value="{{ $skillValue }}" @selected(old('skill_doubles') == $skillValue)>{{ $skillValue === 'not-sure' ? 'Not Sure' : $skillValue }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Sex <span class="text-red-600">*</span></label>
                                            <select name="sex_doubles"
                                                required
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25">
                                                <option value="">Select</option>
                                                <option value="male" @selected(old('sex_doubles') === 'male')>Male</option>
                                                <option value="female" @selected(old('sex_doubles') === 'female')>Female</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[12px] font-bold text-black">Tournament <span class="text-red-600">*</span></label>
                                            <select name="tournament_doubles"
                                                required
                                                class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25">
                                                <option value="">Select tournament</option>
                                                @foreach ($registrationLeagues as $league)
                                                    <option value="{{ $league->id }}" @selected(old('tournament_doubles') == $league->id)>{{ $league->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="border-t border-[#e8e8e8] pt-4">
                                        <h2 class="text-center text-[14px] font-bold text-black underline decoration-[#5FA252] decoration-2 underline-offset-4">Second Player Details</h2>
                                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1 block text-[12px] font-bold text-black">First Name <span class="text-red-600">*</span></label>
                                                <input type="text" id="d2_first" name="d2_first" value="{{ old('d2_first') }}" placeholder="First name"
                                                    class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                    autocomplete="off" required>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[12px] font-bold text-black">Last Name <span class="text-red-600">*</span></label>
                                                <input type="text" id="d2_last" name="d2_last" value="{{ old('d2_last') }}" placeholder="Last name"
                                                    class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                    autocomplete="off" required>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1 block text-[12px] font-bold text-black">Email <span class="text-red-600">*</span></label>
                                                <input type="email" name="d2_email" id="d2_email" value="{{ old('d2_email') }}" placeholder="Email"
                                                    class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="email" required>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[12px] font-bold text-black">Phone Number <span class="text-red-600">*</span></label>
                                                <input type="tel" name="d2_phone" id="d2_phone" value="{{ old('d2_phone') }}" placeholder="Phone"
                                                    class="h-11 w-full rounded-[8px] border border-[#dddddd] bg-white px-3 text-[14px] placeholder:text-[#888] focus:border-[#5FA252] focus:outline-none focus:ring-2 focus:ring-[#5FA252]/25"
                                                autocomplete="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" required>
                                            </div>
                                        </div>
                                    </div>

                                </fieldset>
                            </div>

                            <div class="mt-4 rounded-[10px] border border-[#eeeeee] bg-[#fafafa] px-4 py-3">
                                <p class="text-[13px] font-semibold text-[#333]">Entry Fee: <span class="font-bold">${{ $feeDoubles }}</span></p>
                                <label class="mb-1 mt-2 block text-[12px] font-bold text-black">Payment card <span class="text-red-600">*</span></label>
                                <div class="stripe-card-element mt-1 min-h-[46px] rounded-[10px] border border-[#d7e6d7] bg-white px-3 py-3 text-[14px] text-[#111] shadow-[inset_0_1px_0_rgba(0,0,0,0.02)]"></div>
                                <p class="stripe-card-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
                            </div>

                            <button type="submit"
                                class="disable-button mt-3 h-12 w-full rounded-[8px] bg-[#5FA252] text-[15px] font-bold text-white transition-opacity hover:opacity-95">
                                Submit
                            </button>

                            <div class="common-loader mt-3 hidden rounded-[10px] border border-[#e5e7eb] bg-[#f9fafb] px-3 py-2 text-[13px] text-[#374151]">
                                Processing...
                            </div>
                            <div class="custom_register_form_res mt-3 text-sm"></div>
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
