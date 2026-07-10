@php
    $tab = old('registration_tab', 'singles');
    $isDoubles = $tab === 'doubles';
    $leagueEntryFees = $leagueEntryFees ?? [];
    $feeSingles = $leagueEntryFees['default']['singles'] ?? \App\Support\LeagueEntryFee::formatDollars(\App\Support\LeagueEntryFee::defaultSinglesCents());
    $feeDoubles = $leagueEntryFees['default']['doubles'] ?? \App\Support\LeagueEntryFee::formatDollars(\App\Support\LeagueEntryFee::defaultDoublesCents());
    $selectedSinglesLeague = old('tournament_singles');
    $selectedDoublesLeague = old('tournament_doubles');
    if ($selectedSinglesLeague && isset($leagueEntryFees[(string) $selectedSinglesLeague])) {
        $feeSingles = $leagueEntryFees[(string) $selectedSinglesLeague]['singles'];
    }
    if ($selectedDoublesLeague && isset($leagueEntryFees[(string) $selectedDoublesLeague])) {
        $feeDoubles = $leagueEntryFees[(string) $selectedDoublesLeague]['doubles'];
    }
    $playerSkillLevel = $playerFixedSkillLevel ?? '';
    $playerSkillLabel = $playerFixedSkillLabel ?? '—';
    $hasPlayerSkill = $hasPlayerSkillLevel ?? false;
    $tournamentCount = ($registrationLeagues ?? collect())->count();
    $registrationSkillLevelValues = $registrationSkillLevelValues ?? ['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', 'not-sure'];
@endphp

<div
    id="profile-section-choose-league"
    class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8"
>
    <h3 class="mb-2 text-center text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Choose League</h3>
    <p class="mb-6 text-center text-[13px] text-[#666666] sm:text-[14px]">Register for another tournament as Singles or Doubles. Your skill level is fixed from your profile.</p>

    <div
        id="profile-league-registration"
        data-initial-tab="{{ $tab }}"
        data-closed-divisions='@json($registrationClosedDivisions ?? [])'
        data-closed-group-cards='@json($registrationClosedGroupCards ?? [])'
        data-league-fees='@json($leagueEntryFees ?? [])'
        data-tournament-groups-url="{{ $tournamentGroupsUrl ?? '' }}"
        data-fixed-skill="{{ $playerSkillLevel }}"
    >
        <div class="flex gap-3 sm:gap-4">
            <button
                type="button"
                id="profile-tab-singles"
                class="flex-1 rounded-lg border py-2.5 text-center text-[14px] font-semibold transition-colors sm:text-[15px] {{ $isDoubles ? 'border-[#E0E0E0] bg-white text-[#424242]' : 'border-transparent text-white shadow-sm' }}"
                style="{{ ! $isDoubles ? 'background-color:#66A157' : '' }}"
            >
                Singles
            </button>
            <button
                type="button"
                id="profile-tab-doubles"
                class="flex-1 rounded-lg border py-2.5 text-center text-[14px] font-semibold transition-colors sm:text-[15px] {{ $isDoubles ? 'border-transparent text-white shadow-sm' : 'border-[#E0E0E0] bg-white text-[#424242]' }}"
                style="{{ $isDoubles ? 'background-color:#5FA252' : '' }}"
            >
                Doubles
            </button>
        </div>

        <div class="mt-6 rounded-lg border border-[#E8F5E9] bg-[#F1F8F2] px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wide text-[#66A157]">You (logged in)</p>
            <p class="mt-1 text-[15px] font-bold text-[#333333]">{{ $myProfile['name'] }}</p>
            <p class="text-[13px] text-[#666666]">{{ $myProfile['email'] }}@if($myProfile['phone']) · {{ $myProfile['phone'] }}@endif</p>
        </div>

        @if (! $hasPlayerSkill)
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-[14px] text-amber-900">
                Set your skill level on
                <a href="{{ route('player.my-profile') }}" class="font-bold underline">Personal Information</a>
                before registering for another tournament.
            </div>
        @elseif ($tournamentCount === 0)
            <div class="mt-6 rounded-lg border border-[#E8E8E8] bg-[#FAFAFA] px-4 py-6 text-center text-[14px] text-[#666666]">
                You are already registered in every open tournament. Check back when a new tournament is available.
            </div>
        @else
        <form
            id="profile-singles-league-form"
            class="mt-6 space-y-5 {{ $isDoubles ? 'hidden' : '' }}"
            method="post"
            action="{{ route('player.profile.league.store') }}"
            novalidate
            data-registration-tab="singles"
            data-stripe-key="{{ $stripePublishableKey ?? '' }}"
            data-payment-intent-url="{{ route('player.profile.league.payment-intent') }}"
            data-register-url="{{ route('player.profile.league.store') }}"
            data-csrf="{{ csrf_token() }}"
            data-player-email="{{ $myProfile['email'] }}"
            data-player-name="{{ $myProfile['name'] }}"
        >
            @csrf
            <input type="hidden" name="registration_tab" value="singles">
            <input type="hidden" name="payment_intent_id" class="payment_intent_id" value="{{ old('payment_intent_id') }}">
            <input type="hidden" name="skill_singles" value="{{ $playerSkillLevel }}">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $profileLabelClass }}">Skill Level</label>
                    <input
                        type="text"
                        class="{{ $profileInputClass }} bg-[#f5f5f5] cursor-not-allowed"
                        value="{{ $playerSkillLabel }}"
                        readonly
                        disabled
                    >
                </div>
                <div>
                    <label class="{{ $profileLabelClass }}">Tournament <span class="text-red-600">*</span></label>
                    <select name="tournament_singles" required class="{{ $profileInputClass }} appearance-none pr-10">
                        <option value="">Select tournament</option>
                        @foreach ($registrationLeagues as $league)
                            <option value="{{ $league->id }}" @selected(old('tournament_singles') == $league->id)>{{ $league->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="tournament-group-wrap hidden" data-tab="singles">
                <label class="{{ $profileLabelClass }}">Your group</label>
                <input
                    type="text"
                    class="tournament-group-preview {{ $profileInputClass }} bg-[#f5f5f5]"
                    readonly
                    disabled
                    placeholder="Select a tournament"
                >
                <input type="hidden" name="group_card_singles" class="tournament-group-id" value="{{ old('group_card_singles') }}">
                <p class="tournament-group-hint mt-1 hidden text-[12px] text-[#666666]">Based on your skill level. Subgroup (A, B, C…) is assigned automatically.</p>
                <p class="tournament-group-loading mt-1 hidden text-[12px] text-[#666666]">Finding your group…</p>
                <p class="tournament-group-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
            </div>

            <div class="rounded-lg border border-[#EEEEEE] bg-[#FAFAFA] px-4 py-3">
                <p class="text-[13px] font-semibold text-[#333333]">Entry Fee: $<span class="entry-fee-amount font-bold">{{ $feeSingles }}</span></p>
                <label class="{{ $profileLabelClass }} mt-2">Payment card <span class="text-red-600">*</span></label>
                <div class="stripe-card-element mt-1 min-h-[46px] rounded-lg border border-[#D7E6D7] bg-white px-3 py-3 shadow-sm"></div>
                <p class="stripe-card-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
            </div>

            <button class="profile-league-submit w-full rounded-lg bg-[#66A157] px-6 py-3 text-[15px] font-bold text-white shadow-sm transition hover:bg-[#5a9048]">
                Submit
            </button>
            <div class="profile-league-loader hidden rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] px-3 py-2 text-[13px] text-[#374151]">Processing...</div>
            <div class="profile_league_form_res text-sm"></div>
        </form>

        <form
            id="profile-doubles-league-form"
            class="mt-6 space-y-5 {{ $isDoubles ? '' : 'hidden' }}"
            method="post"
            action="{{ route('player.profile.league.store') }}"
            novalidate
            data-registration-tab="doubles"
            data-stripe-key="{{ $stripePublishableKey ?? '' }}"
            data-payment-intent-url="{{ route('player.profile.league.payment-intent') }}"
            data-register-url="{{ route('player.profile.league.store') }}"
            data-csrf="{{ csrf_token() }}"
            data-player-email="{{ $myProfile['email'] }}"
            data-player-name="{{ $myProfile['name'] }}"
            data-partner-lookup-url="{{ route('player.profile.league.partner-lookup') }}"
        >
            @csrf
            <input type="hidden" name="registration_tab" value="doubles">
            <input type="hidden" name="payment_intent_id" class="payment_intent_id" value="{{ old('payment_intent_id') }}">
            <input type="hidden" name="skill_doubles" value="{{ $playerSkillLevel }}">

            <div>
                <label class="{{ $profileLabelClass }}">Your skill level</label>
                <input
                    type="text"
                    class="{{ $profileInputClass }} bg-[#f5f5f5] cursor-not-allowed"
                    value="{{ $playerSkillLabel }}"
                    readonly
                    disabled
                >
            </div>

            <div class="border-t border-[#E8E8E8] pt-5">
                <h4 class="text-center text-[14px] font-bold text-[#333333] underline decoration-[#66A157] decoration-2 underline-offset-4">Second Player Details</h4>
                <div class="mt-4 space-y-5">
                    <div>
                        <label for="d2-email" class="{{ $profileLabelClass }}">Email <span class="text-red-600">*</span></label>
                        <input
                            id="d2-email"
                            type="email"
                            name="d2_email"
                            value="{{ old('d2_email') }}"
                            placeholder="Enter partner email first"
                            required
                            class="{{ $profileInputClass }} partner-email-lookup"
                            autocomplete="email"
                        />
                        <p class="partner-email-lookup-status mt-1.5 hidden text-[12px] font-semibold sm:text-[13px]" aria-live="polite"></p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $profileLabelClass }}">First Name <span class="text-red-600">*</span></label>
                            <input type="text" name="d2_first" value="{{ old('d2_first') }}" placeholder="First name" required class="{{ $profileInputClass }} partner-detail-field" autocomplete="off" />
                        </div>
                        <div>
                            <label class="{{ $profileLabelClass }}">Last Name <span class="text-red-600">*</span></label>
                            <input type="text" name="d2_last" value="{{ old('d2_last') }}" placeholder="Last name" required class="{{ $profileInputClass }} partner-detail-field" autocomplete="off" />
                        </div>
                        <div>
                            <label class="{{ $profileLabelClass }}">Phone Number <span class="text-red-600">*</span></label>
                            <input type="tel" name="d2_phone" value="{{ old('d2_phone') }}" placeholder="Phone" required class="{{ $profileInputClass }} partner-detail-field" inputmode="numeric" pattern="[0-9]*" maxlength="15" autocomplete="tel" />
                        </div>
                        <div class="partner-skill-field">
                            <label class="{{ $profileLabelClass }}">Skill Level <span class="text-red-600">*</span></label>
                            <select name="d2_skill" required class="partner-skill-select {{ $profileInputClass }} appearance-none pr-10">
                                <option value="">Select</option>
                                @foreach ($registrationSkillLevelValues as $skillValue)
                                    <option value="{{ $skillValue }}" @selected(old('d2_skill') == $skillValue)>{{ $skillValue === 'not-sure' ? 'Not Sure' : $skillValue }}</option>
                                @endforeach
                            </select>
                            <input
                                type="text"
                                class="partner-skill-locked {{ $profileInputClass }} hidden cursor-not-allowed bg-[#f5f5f5]"
                                readonly
                                disabled
                                tabindex="-1"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#E8E8E8] pt-5">
                <h4 class="text-center text-[14px] font-bold text-[#333333] underline decoration-[#66A157] decoration-2 underline-offset-4">Other Details</h4>
                <div class="mt-4 space-y-5">
                    <div>
                        <label class="{{ $profileLabelClass }}">Tournament <span class="text-red-600">*</span></label>
                        <select name="tournament_doubles" required class="{{ $profileInputClass }} appearance-none pr-10">
                            <option value="">Select tournament</option>
                            @foreach ($registrationLeagues as $league)
                                <option value="{{ $league->id }}" @selected(old('tournament_doubles') == $league->id)>{{ $league->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="tournament-group-wrap hidden" data-tab="doubles">
                        <label class="{{ $profileLabelClass }}">Your group</label>
                        <input
                            type="text"
                            class="tournament-group-preview {{ $profileInputClass }} bg-[#f5f5f5]"
                            readonly
                            disabled
                            placeholder="Select tournament and both skill levels"
                        >
                        <input type="hidden" name="group_card_doubles" class="tournament-group-id" value="{{ old('group_card_doubles') }}">
                        <p class="tournament-group-hint mt-1 hidden text-[12px] text-[#666666]">Subgroup (A, B, C…) is assigned automatically.</p>
                        <p class="tournament-group-loading mt-1 hidden text-[12px] text-[#666666]">Finding your group…</p>
                        <p class="tournament-group-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-[#EEEEEE] bg-[#FAFAFA] px-4 py-3">
                <p class="text-[13px] font-semibold text-[#333333]">Entry Fee: $<span class="entry-fee-amount font-bold">{{ $feeDoubles }}</span></p>
                <label class="{{ $profileLabelClass }} mt-2">Payment card <span class="text-red-600">*</span></label>
                <div class="stripe-card-element mt-1 min-h-[46px] rounded-lg border border-[#D7E6D7] bg-white px-3 py-3 shadow-sm"></div>
                <p class="stripe-card-error mt-1 hidden text-[12px] font-semibold text-red-600"></p>
            </div>

            <button class="profile-league-submit w-full rounded-lg bg-[#5FA252] px-6 py-3 text-[15px] font-bold text-white shadow-sm transition hover:bg-[#549648]">
                Submit
            </button>
            <div class="profile-league-loader hidden rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] px-3 py-2 text-[13px] text-[#374151]">Processing...</div>
            <div class="profile_league_form_res text-sm"></div>
        </form>
        @endif
    </div>
</div>

@push('profile_scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('frontend/js/profile-league-register.js') }}?v={{ @filemtime(public_path('frontend/js/profile-league-register.js')) ?: time() }}"></script>
@endpush
