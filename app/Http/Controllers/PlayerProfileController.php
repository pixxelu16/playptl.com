<?php

namespace App\Http\Controllers;

use App\Enums\GroupMatchFormat;
use App\Enums\UserRole;
use App\Helpers\LeagueMenuHelper;
use App\Mail\PartnerAddedMail;
use App\Mail\RegistrationConfirmedMail;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\GroupMatchPlayerUpload;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\PaymentHistory;
use App\Models\PlayoffMatch;
use App\Models\PlayoffMatchPlayerUpload;
use App\Models\User;
use App\Support\LeaguePlayoffCalendar;
use App\Support\GroupMatchScheduleNotifier;
use App\Support\PlayerMatchDayConflict;
use App\Support\TournamentRegistrationOptions;
use App\Support\PlayoffMatchScheduleNotifier;
use App\Support\LeagueRegistrationFlow;
use App\Support\LeagueSeasonWindow;
use App\Support\LeagueRegistrationRoster;
use App\Support\UserSkillLevel;
use App\Support\LeagueWeekCalendar;
use App\Support\MatchStartTime;
use App\Support\MatchResultInput;
use App\Support\MatchSchedulePresenter;
use App\Support\MatchScoreReader;
use App\Support\PlayerMatchWorkflow;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PlayerProfileController extends Controller
{
    public function show(Request $request): View
    {
        return $this->profileShell(
            $request,
            'personal',
            null,
            'Personal Information | '.config('app.name', 'playptl'),
            'Update your player profile and profile photo.',
        );
    }

    public function showChooseLeague(Request $request): View
    {
        return $this->profileShell(
            $request,
            'league',
            'Choose League',
            'Choose League | '.config('app.name', 'playptl'),
            'Register for a league tournament as singles or doubles.',
            $this->chooseLeaguePanelData($request),
        );
    }

    public function lookupLeaguePartner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        if ($email === strtolower((string) $request->user()->email)) {
            return response()->json([
                'found' => false,
                'message' => 'Second player email must be different from your email.',
            ], 422);
        }

        $partner = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $partner) {
            return response()->json([
                'found' => false,
                'message' => 'Please tell your buddy to log in with this email ('.$email.') after you complete registration.',
            ]);
        }

        $firstName = trim((string) ($partner->first_name ?? ''));
        $lastName = trim((string) ($partner->last_name ?? ''));
        if ($firstName === '' && $lastName === '') {
            $parts = preg_split('/\s+/', trim((string) $partner->name), 2) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $partnerSkill = $this->partnerSkillLevelFromUser($partner);

        return response()->json([
            'found' => true,
            'message' => 'Tell your buddy to please log in with their account and see match details there.',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => (string) ($partner->phone ?? ''),
            'name' => trim((string) $partner->name) !== '' ? (string) $partner->name : trim($firstName.' '.$lastName),
            'skill_level' => $partnerSkill,
            'skill_locked' => $partnerSkill !== null,
        ]);
    }

    public function createLeaguePaymentIntent(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'league_id' => ['required', 'integer', 'exists:leagues,id'],
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if (strtolower((string) $validated['email']) !== strtolower((string) $user->email)) {
            return response()->json(['message' => 'Payment email must match your account.'], 422);
        }

        $league = League::query()->findOrFail((int) $validated['league_id']);
        $tab = (string) $validated['registration_tab'];

        if ($this->userIsRegisteredInLeague($user, (int) $league->id)) {
            return response()->json(['message' => 'You are already registered in this tournament.'], 422);
        }

        $skillLevel = $this->playerFixedSkillLevel($user, $request);
        if ($skillLevel === null) {
            return response()->json(['message' => 'Set your skill level on Personal Information before registering for another tournament.'], 422);
        }

        $registrationClosed = \App\Support\LeagueRegistrationGate::closedReasonForSelection($league, $tab, $skillLevel);
        if ($registrationClosed !== null) {
            return response()->json(['message' => $registrationClosed], 422);
        }

        $amountCents = \App\Support\LeagueEntryFee::centsForTab($league, $tab);
        $currency = (string) config('services.stripe.currency', 'USD');

        $secret = (string) (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            return response()->json(['message' => 'Stripe is not configured.'], 500);
        }

        $stripe = new StripeClient($secret);

        try {
            $intent = $stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => strtolower($currency),
                'automatic_payment_methods' => ['enabled' => true],
                'description' => 'League registration fee',
                'metadata' => [
                    'league_id' => (string) $validated['league_id'],
                    'registration_tab' => (string) $validated['registration_tab'],
                    'email' => strtolower((string) $validated['email']),
                    'source' => 'player_profile',
                ],
            ]);
        } catch (ApiErrorException) {
            return response()->json(['message' => 'Unable to create payment intent.'], 500);
        }

        return response()->json([
            'client_secret' => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'amount_cents' => $amountCents,
            'currency' => strtoupper($currency),
        ]);
    }

    public function storeLeagueRegistration(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = $request->validate([
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'payment_intent_id' => ['required', 'string', 'max:255'],
        ]);

        $tab = (string) $base['registration_tab'];

        $skillLevel = $this->playerFixedSkillLevel($user, $request);
        if ($skillLevel === null) {
            return response()->json(['message' => 'Set your skill level on Personal Information before registering for another tournament.'], 422);
        }

        $playerSkill = $skillLevel;

        if ($tab === 'singles') {
            $specific = $request->validate([
                'tournament_singles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_singles' => ['required', 'integer', 'exists:group_cards,id'],
            ]);
            $leagueId = (int) $specific['tournament_singles'];
            $groupCardId = (int) $specific['group_card_singles'];
            $assignmentSkill = $playerSkill;
        } else {
            $specific = $request->validate([
                'tournament_doubles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_doubles' => ['required', 'integer', 'exists:group_cards,id'],
                'd2_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'd2_phone' => ['required', 'string', 'max:32'],
                'd2_first' => ['required', 'string', 'max:255'],
                'd2_last' => ['required', 'string', 'max:255'],
                'd2_skill' => ['required', 'string', 'max:32'],
            ]);

            if (strtolower((string) $specific['d2_email']) === strtolower((string) $user->email)) {
                return response()->json(['message' => 'Second player email must be different from your email.'], 422);
            }

            $partnerSkill = trim((string) $specific['d2_skill']);
            $partner = User::query()->where('email', strtolower((string) $specific['d2_email']))->first();
            if ($partner instanceof User) {
                $knownSkill = $this->partnerSkillLevelFromUser($partner);
                if ($knownSkill !== null && $knownSkill !== $partnerSkill) {
                    return response()->json(['message' => 'Partner skill level does not match their profile.'], 422);
                }
            }

            if ($playerSkill === 'not-sure' || $partnerSkill === 'not-sure') {
                $assignmentSkill = 'not-sure';
            } else {
                $averageSkill = TournamentRegistrationOptions::averageSkillLevels($playerSkill, $partnerSkill);
                if ($averageSkill === null) {
                    return response()->json(['message' => 'Both players need a valid skill level for group assignment.'], 422);
                }
                $assignmentSkill = $averageSkill;
            }

            $leagueId = (int) $specific['tournament_doubles'];
            $groupCardId = (int) $specific['group_card_doubles'];
            $skillLevel = $playerSkill;
        }

        if ($this->userIsRegisteredInLeague($user, $leagueId)) {
            return response()->json(['message' => 'You are already registered in this tournament.'], 422);
        }

        $league = League::query()->findOrFail($leagueId);

        $expectedCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $tab, $assignmentSkill);
        if (! $expectedCard instanceof GroupCard) {
            return response()->json([
                'message' => $tab === 'doubles'
                    ? 'No group is available for your team skill level in this tournament.'
                    : 'No group is available for your skill level in this tournament.',
            ], 422);
        }

        if ($groupCardId !== (int) $expectedCard->id) {
            return response()->json(['message' => 'Group assignment does not match your skill level.'], 422);
        }

        $groupCard = TournamentRegistrationOptions::resolveGroupCard($league, $tab, $groupCardId);
        if (! $groupCard instanceof GroupCard) {
            return response()->json(['message' => 'Invalid group for this tournament and format.'], 422);
        }

        $registrationClosed = \App\Support\LeagueRegistrationGate::closedReason($league, $groupCard, $ageGroup);
        if ($registrationClosed !== null) {
            return response()->json(['message' => $registrationClosed], 422);
        }

        if (LeagueRegistration::query()
            ->where('user_id', $user->id)
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCard->id)
            ->exists()) {
            return response()->json(['message' => 'You are already registered in this league group.'], 422);
        }

        if (LeagueRegistrationRoster::isInAnotherLeagueSubGroupForType($user->id, $leagueId, $groupCard->id, $tab)) {
            $formatLabel = $tab === 'doubles' ? 'doubles' : 'singles';

            return response()->json(['message' => "You are already registered in another {$formatLabel} group for this league."], 422);
        }

        if (PaymentHistory::query()->where('transaction_id', $base['payment_intent_id'])->exists()) {
            return response()->json(['message' => 'This payment was already used.'], 422);
        }

        $secret = (string) (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            return response()->json(['message' => 'Stripe is not configured.'], 500);
        }

        $stripe = new StripeClient($secret);
        $intent = $stripe->paymentIntents->retrieve($base['payment_intent_id'], []);

        $expectedAmountCents = \App\Support\LeagueEntryFee::centsForTab($league, $tab);
        $expectedCurrency = strtolower((string) config('services.stripe.currency', 'USD'));
        $intentEmail = strtolower((string) ($intent->metadata['email'] ?? ''));
        $intentLeagueId = (string) ($intent->metadata['league_id'] ?? '');
        $intentTab = (string) ($intent->metadata['registration_tab'] ?? '');

        if (
            $intent->status !== 'succeeded'
            || (int) $intent->amount !== $expectedAmountCents
            || (string) $intent->currency !== $expectedCurrency
            || $intentEmail !== strtolower((string) $user->email)
            || $intentLeagueId !== (string) $leagueId
            || $intentTab !== $tab
        ) {
            return response()->json(['message' => 'Payment not completed or does not match registration.'], 422);
        }

        $ageGroup = $user->leagueRegistrations()->latest('id')->value('age_group_key');

        $groupId = LeagueRegistrationFlow::resolveGroupId($leagueId, $groupCard, $tab, $ageGroup);
        $teamKey = $tab === 'doubles' ? LeagueRegistrationFlow::newDoublesTeamKey() : null;

        $amountDecimal = number_format($expectedAmountCents / 100, 2, '.', '');

        PaymentHistory::create([
            'user_id' => $user->id,
            'league_id' => $leagueId,
            'amount' => $amountDecimal,
            'currency' => strtoupper((string) config('services.stripe.currency', 'USD')),
            'status' => 'completed',
            'transaction_id' => (string) $intent->id,
            'description' => 'League registration fee',
            'meta' => [
                'registration_tab' => $tab,
                'source' => 'player_profile',
                'payment_intent_status' => (string) $intent->status,
            ],
        ]);

        LeagueRegistrationFlow::registerUser($user, $leagueId, [
            'group_card_id' => $groupCard->id,
            'group_id' => $groupId,
            'skill_level' => $skillLevel,
            'age_group_key' => $ageGroup,
            'registration_type' => $tab,
            'team_key' => $teamKey,
            'payment_status' => 'completed',
        ]);

        $user->update([
            'registration_type' => $tab,
            'transaction_id' => (string) $intent->id,
        ]);

        if ($tab === 'doubles') {
            $partnerEmail = strtolower((string) $specific['d2_email']);
            $partnerName = trim(((string) $specific['d2_first']).' '.((string) $specific['d2_last']));

            $partner = User::query()->where('email', $partnerEmail)->first();
            if (! $partner) {
                $partner = User::create([
                    'name' => $partnerName !== '' ? $partnerName : $partnerEmail,
                    'first_name' => $specific['d2_first'],
                    'last_name' => $specific['d2_last'],
                    'email' => $partnerEmail,
                    'phone' => (string) $specific['d2_phone'],
                    'role' => UserRole::Player,
                    'status' => 'active',
                    'password' => Hash::make(Str::random(32)),
                    'registration_type' => 'doubles',
                    'skill_level' => (string) $specific['d2_skill'],
                ]);
            } else {
                UserSkillLevel::syncToUser($partner, (string) $specific['d2_skill']);
            }

            LeagueRegistrationFlow::registerUser($partner, $leagueId, [
                'group_card_id' => $groupCard->id,
                'group_id' => $groupId,
                'skill_level' => (string) $specific['d2_skill'],
                'age_group_key' => $ageGroup,
                'registration_type' => 'doubles',
                'team_key' => $teamKey,
                'payment_status' => 'completed',
            ]);

            try {
                $token = PasswordBroker::broker()->createToken($partner);
                $setupUrl = route('password.reset', ['token' => $token]).'?email='.urlencode($partnerEmail);
                Mail::to($partnerEmail)->send(new PartnerAddedMail(
                    inviterName: (string) $user->name,
                    leagueName: (string) $league->name,
                    setupUrl: $setupUrl,
                ));
            } catch (\Throwable) {
                // Registration remains valid if mail fails.
            }
        }

        try {
            Mail::to($user->email)->send(new RegistrationConfirmedMail(
                userName: (string) $user->name,
                leagueName: (string) $league->name,
                registrationType: $tab,
                skillLevel: $skillLevel,
                amount: $amountDecimal,
                currency: strtoupper((string) config('services.stripe.currency', 'USD')),
                paymentIntentId: (string) $intent->id,
            ));
        } catch (\Throwable) {
            // Registration remains valid if mail fails.
        }

        return response()->json([
            'message' => 'You have been registered for '.$league->name.'.',
            'redirect_url' => route('player.profile.league', ['league_id' => $leagueId, 'group_card_id' => $groupCard->id]),
        ]);
    }

    public function showPassword(Request $request): View
    {
        return $this->profileShell(
            $request,
            'password',
            'Password & Security',
            'Password & Security | '.config('app.name', 'playptl'),
            'Change your player account password.',
        );
    }

    public function showLocation(Request $request): View
    {
        return $this->profileShell(
            $request,
            'location',
            'My Matches',
            'My Matches | '.config('app.name', 'playptl'),
            'View your group match schedule and update venue and time.',
            $this->locationPanelData($request),
        );
    }

    public function updateMatchLocation(Request $request): RedirectResponse
    {
        $kind = (string) $request->input('match_kind', 'group');
        if ($kind === 'playoff') {
            return $this->updatePlayoffMatchLocation($request);
        }

        abort_unless(Schema::hasTable('group_matches'), 404);

        $validated = $request->validate([
            'match' => ['required', 'integer', 'exists:group_matches,id'],
            'match_kind' => ['nullable', 'string', 'in:group,playoff'],
            'schedule_date' => ['required', 'date'],
            'schedule_time' => ['required', 'string', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'schedule_venue' => ['nullable', 'string', 'max:255'],
            'schedule_court' => ['nullable', 'string', 'max:64'],
        ]);

        $match = GroupMatch::query()->with('league')->findOrFail((int) $validated['match']);
        $this->ensurePlayerOwnsGroupMatchAccess($request, $match);

        $league = $match->league;
        if ($league instanceof League) {
            $playWeekCount = max(
                (int) GroupMatch::query()
                    ->where('league_id', $match->league_id)
                    ->where('group_card_id', $match->group_card_id)
                    ->where('group_id', $match->group_id)
                    ->max('round_number'),
                (int) ($match->round_number ?? 0),
            );
            $dateRuleError = LeagueWeekCalendar::validatePendingMatchDate(
                Carbon::parse($validated['schedule_date'])->startOfDay(),
                $league,
                $match,
                $playWeekCount,
            );
            if ($dateRuleError !== null) {
                return back()->withErrors(['schedule_date' => $dateRuleError])->withInput();
            }
        }

        $oldDate = $match->match_date?->toDateString();
        $oldTime = MatchStartTime::toInputValue((string) ($match->start_time ?? ''));
        $oldVenue = trim((string) ($match->venue ?? ''));
        $oldCourt = trim((string) ($match->court ?? ''));

        $startTime = $this->normalizeTimeForStorage(trim($validated['schedule_time']));

        $venueRaw = trim((string) ($validated['schedule_venue'] ?? ''));
        $courtRaw = trim((string) ($validated['schedule_court'] ?? ''));

        $newDate = Carbon::parse($validated['schedule_date'])->toDateString();
        $newTime = MatchStartTime::toInputValue($startTime);
        $newVenue = $venueRaw;
        $newCourt = $courtRaw;

        $match->update([
            'match_date' => $newDate,
            'start_time' => $startTime,
            'venue' => $venueRaw !== '' ? $venueRaw : null,
            'court' => $courtRaw !== '' ? $courtRaw : null,
        ]);

        $scheduleChanged = $oldDate !== $newDate
            || $oldTime !== $newTime
            || $oldVenue !== $newVenue
            || $oldCourt !== $newCourt;

        if ($scheduleChanged) {
            $match->refresh();
            $match->load(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'group', 'league', 'groupCard']);
            GroupMatchScheduleNotifier::notifyParticipants($match, updatedByPlayer: true);
        }

        return redirect()
            ->route('player.profile.location', ['match' => $match->id])
            ->with('status', $scheduleChanged
                ? 'Match details saved. All players have been emailed the updated schedule.'
                : 'Match details saved successfully.');
    }

    public function updatePlayoffMatchLocation(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('playoff_matches'), 404);

        $validated = $request->validate([
            'match' => ['required', 'integer', 'exists:playoff_matches,id'],
            'match_kind' => ['nullable', 'string', 'in:group,playoff'],
            'schedule_date' => ['required', 'date'],
            'schedule_time' => ['required', 'string', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'schedule_venue' => ['nullable', 'string', 'max:255'],
            'schedule_court' => ['nullable', 'string', 'max:64'],
        ]);

        $match = PlayoffMatch::query()->with('league')->findOrFail((int) $validated['match']);
        $this->ensurePlayerOwnsPlayoffMatchAccess($request, $match);

        if (! $match->home_user_id || ! $match->away_user_id) {
            return back()->withErrors(['match' => 'Both players must be set before scheduling this playoff match.']);
        }

        $league = $match->league;
        if ($league instanceof League) {
            $dateRuleError = LeaguePlayoffCalendar::validatePlayoffMatchDate(
                Carbon::parse($validated['schedule_date'])->startOfDay(),
                $league,
            );
            if ($dateRuleError !== null) {
                return back()->withErrors(['schedule_date' => $dateRuleError])->withInput();
            }
        }

        $oldDate = $match->match_date?->toDateString();
        $oldTime = MatchStartTime::toInputValue((string) ($match->start_time ?? ''));
        $oldVenue = trim((string) ($match->venue ?? ''));
        $oldCourt = trim((string) ($match->court ?? ''));

        $newDate = Carbon::parse($validated['schedule_date'])->toDateString();
        $newTime = MatchStartTime::toInputValue($this->normalizeTimeForStorage(trim($validated['schedule_time'])));
        $newVenue = trim((string) ($validated['schedule_venue'] ?? ''));
        $newCourt = trim((string) ($validated['schedule_court'] ?? ''));

        $match->update([
            'match_date' => $newDate,
            'start_time' => $this->normalizeTimeForStorage(trim($validated['schedule_time'])),
            'venue' => $newVenue !== '' ? $newVenue : null,
            'court' => $newCourt !== '' ? $newCourt : null,
        ]);

        $scheduleChanged = $oldDate !== $newDate
            || $oldTime !== $newTime
            || $oldVenue !== $newVenue
            || $oldCourt !== $newCourt;

        if ($scheduleChanged) {
            $match->refresh();
            $match->load(['homeUser', 'awayUser', 'league', 'groupCard']);
            PlayoffMatchScheduleNotifier::notifyParticipants($match, updatedByPlayer: true);
        }

        return redirect()
            ->route('player.profile.location', ['match' => $match->id, 'kind' => 'playoff'])
            ->with('status', $scheduleChanged
                ? 'Playoff match details saved. Both players have been emailed.'
                : 'Playoff match details saved.');
    }

    public function updateMatchResult(Request $request): RedirectResponse
    {
        $kind = (string) $request->input('match_kind', 'group');
        if ($kind === 'playoff') {
            return $this->updatePlayoffMatchResult($request);
        }

        abort_unless(Schema::hasTable('group_matches'), 404);

        $validated = $request->validate([
            'match' => ['required', 'integer', 'exists:group_matches,id'],
            'match_kind' => ['nullable', 'string', 'in:group,playoff'],
            'score' => ['nullable', 'string', 'max:64'],
            ...MatchResultInput::setFieldValidationRules(),
            'result_type' => ['nullable', 'string', 'in:normal,walkover'],
            'walked_off_side' => ['nullable', 'string', 'in:home,away'],
        ]);

        $match = GroupMatch::query()->findOrFail((int) $validated['match']);
        $this->ensurePlayerOwnsGroupMatchAccess($request, $match);

        if (! $match->isPending()) {
            return back()->withErrors(['match' => 'This match already has a recorded result.'])->withInput();
        }

        $uploadGate = PlayerMatchWorkflow::ensurePlayerUploadedPhoto($match, (int) $request->user()->id);
        if ($uploadGate !== null) {
            return back()->withErrors(['match' => $uploadGate])->withInput();
        }

        $setPairError = MatchResultInput::validateSetPairs($validated);
        if ($setPairError !== null) {
            return back()->withErrors(['score' => $setPairError])->withInput();
        }

        $result = MatchResultInput::fromRequest(
            MatchResultInput::resolveScoreRaw($validated, $validated['result_type'] ?? null),
            $validated['result_type'] ?? null,
            $validated['walked_off_side'] ?? null,
        );
        $scoreTrimmed = $result['score'];
        $winnerSide = $result['winner_side'] ?? $this->resolvedWinnerSideForPersistence(
            $scoreTrimmed,
            null,
        );
        if ($scoreTrimmed !== '' && MatchScoreReader::isWalkover($scoreTrimmed) && $winnerSide === null) {
            return back()->withErrors([
                'walked_off_side' => 'Choose which player walked off (forfeit).',
            ])->withInput();
        }

        if ($scoreTrimmed === '' && $winnerSide === null) {
            return back()->withErrors(['score' => 'Enter a score or choose walkover.'])->withInput();
        }

        $match->update([
            'score' => $scoreTrimmed !== '' ? $scoreTrimmed : null,
            'winner_side' => $winnerSide,
            'winner_user_id' => $this->resolvedWinnerUserIdForPersistence($match, $winnerSide),
        ]);

        return redirect()
            ->route('player.profile.location', ['match' => $match->id])
            ->with('status', 'Score saved successfully.');
    }

    public function updatePlayoffMatchResult(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('playoff_matches'), 404);

        $validated = $request->validate([
            'match' => ['required', 'integer', 'exists:playoff_matches,id'],
            'match_kind' => ['nullable', 'string', 'in:group,playoff'],
            'score' => ['nullable', 'string', 'max:64'],
            ...MatchResultInput::setFieldValidationRules(),
            'result_type' => ['nullable', 'string', 'in:normal,walkover'],
            'walked_off_side' => ['nullable', 'string', 'in:home,away'],
        ]);

        $match = PlayoffMatch::query()->findOrFail((int) $validated['match']);
        $this->ensurePlayerOwnsPlayoffMatchAccess($request, $match);

        if (! $match->isPending()) {
            return back()->withErrors(['match' => 'This match already has a recorded result.'])->withInput();
        }

        if (! $match->home_user_id || ! $match->away_user_id) {
            return back()->withErrors(['match' => 'Both players must be assigned before saving a result.'])->withInput();
        }

        $uploadGate = PlayerMatchWorkflow::ensurePlayerUploadedPlayoffPhoto((int) $match->id, (int) $request->user()->id);
        if ($uploadGate !== null) {
            return back()->withErrors(['match' => $uploadGate])->withInput();
        }

        $setPairError = MatchResultInput::validateSetPairs($validated);
        if ($setPairError !== null) {
            return back()->withErrors(['score' => $setPairError])->withInput();
        }

        $result = MatchResultInput::fromRequest(
            MatchResultInput::resolveScoreRaw($validated, $validated['result_type'] ?? null),
            $validated['result_type'] ?? null,
            $validated['walked_off_side'] ?? null,
        );
        $scoreTrimmed = $result['score'];
        $winnerSide = $result['winner_side'] ?? $this->resolvedWinnerSideForPersistence($scoreTrimmed, null);
        if ($scoreTrimmed !== '' && MatchScoreReader::isWalkover($scoreTrimmed) && $winnerSide === null) {
            return back()->withErrors([
                'walked_off_side' => 'Choose which player walked off (forfeit).',
            ])->withInput();
        }

        if ($scoreTrimmed === '' && $winnerSide === null) {
            return back()->withErrors(['score' => 'Enter a score or choose walkover.'])->withInput();
        }

        $match->update([
            'score' => $scoreTrimmed !== '' ? $scoreTrimmed : null,
            'winner_side' => $winnerSide,
            'winner_user_id' => $this->resolvedWinnerUserIdForPlayoff($match, $winnerSide),
        ]);

        return redirect()
            ->route('player.profile.location', ['match' => $match->id, 'kind' => 'playoff'])
            ->with('status', 'Playoff score saved.');
    }

    public function showUpload(Request $request): View
    {
        return $this->profileShell(
            $request,
            'upload',
            'Upload Image',
            'Upload Image | '.config('app.name', 'playptl'),
            'Upload match images for your scheduled matches.',
            $this->uploadPanelData($request),
        );
    }

    public function storeMatchUpload(Request $request): RedirectResponse
    {
        $kind = (string) $request->input('match_kind', 'group');
        if ($kind === 'playoff') {
            return $this->storePlayoffMatchUpload($request);
        }

        abort_unless(Schema::hasTable('group_matches'), 404);
        abort_unless(Schema::hasTable('group_match_player_uploads'), 404);

        $validated = $request->validate([
            'match' => ['required', 'integer', 'exists:group_matches,id'],
            'match_kind' => ['nullable', 'string', 'in:group,playoff'],
            'images' => ['required', 'array', 'min:1', 'max:12'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $match = GroupMatch::query()->findOrFail((int) $validated['match']);
        $this->ensurePlayerOwnsGroupMatchAccess($request, $match);

        $notesRaw = trim((string) ($validated['notes'] ?? ''));
        $notes = $notesRaw === '' ? null : $notesRaw;
        $uploadDate = Carbon::today()->toDateString();
        $uid = (int) $request->user()->id;

        $dir = public_path('upload/group-match-uploads');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if ($request->file('images') === null || $request->file('images') === []) {
            return back()
                ->withInput()
                ->with('_upload_step', 'files')
                ->withErrors(['images' => 'Choose at least one image to upload.']);
        }

        foreach ($request->file('images', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'gm-'.$match->id.'-'.$uid.'-'.bin2hex(random_bytes(5)).'.'.$ext;
            $file->move($dir, $filename);
            $relative = 'upload/group-match-uploads/'.$filename;
            GroupMatchPlayerUpload::query()->create([
                'group_match_id' => $match->id,
                'uploaded_by_user_id' => $uid,
                'upload_date' => $uploadDate,
                'image_path' => $relative,
                'notes' => $notes,
            ]);
        }

        return redirect()
            ->route('player.profile.upload', ['match' => $match->id, 'kind' => 'group'])
            ->with('status', 'Images saved for this match.');
    }

    public function storePlayoffMatchUpload(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('playoff_matches'), 404);
        abort_unless(Schema::hasTable('playoff_match_player_uploads'), 404);

        $validated = $request->validate([
            'match' => ['required', 'integer', 'exists:playoff_matches,id'],
            'match_kind' => ['nullable', 'string', 'in:group,playoff'],
            'images' => ['required', 'array', 'min:1', 'max:12'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $match = PlayoffMatch::query()->findOrFail((int) $validated['match']);
        $this->ensurePlayerOwnsPlayoffMatchAccess($request, $match);

        $notesRaw = trim((string) ($validated['notes'] ?? ''));
        $notes = $notesRaw === '' ? null : $notesRaw;
        $uploadDate = Carbon::today()->toDateString();
        $uid = (int) $request->user()->id;

        $dir = public_path('upload/playoff-match-uploads');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if ($request->file('images') === null || $request->file('images') === []) {
            return back()
                ->withInput()
                ->with('_upload_step', 'files')
                ->withErrors(['images' => 'Choose at least one image to upload.']);
        }

        foreach ($request->file('images', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'pm-'.$match->id.'-'.$uid.'-'.bin2hex(random_bytes(5)).'.'.$ext;
            $file->move($dir, $filename);
            $relative = 'upload/playoff-match-uploads/'.$filename;
            PlayoffMatchPlayerUpload::query()->create([
                'playoff_match_id' => $match->id,
                'uploaded_by_user_id' => $uid,
                'upload_date' => $uploadDate,
                'image_path' => $relative,
                'notes' => $notes,
            ]);
        }

        return redirect()
            ->route('player.profile.upload', ['match' => $match->id, 'kind' => 'playoff'])
            ->with('status', 'Images saved for this playoff match.');
    }

    public function destroyMatchUpload(Request $request, GroupMatchPlayerUpload $upload): RedirectResponse
    {
        abort_unless(Schema::hasTable('group_match_player_uploads'), 404);

        $match = $upload->groupMatch;
        abort_unless($match !== null, 404);

        $this->ensurePlayerOwnsGroupMatchAccess($request, $match);

        $path = str_replace('\\', '/', trim((string) $upload->image_path));
        if ($path !== '' && str_starts_with($path, 'upload/group-match-uploads/')) {
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $matchId = $match->id;
        $upload->delete();

        return redirect()
            ->route('player.profile.upload', ['match' => $matchId, 'kind' => 'group'])
            ->with('status', 'Image removed.');
    }

    public function destroyPlayoffMatchUpload(Request $request, PlayoffMatchPlayerUpload $upload): RedirectResponse
    {
        abort_unless(Schema::hasTable('playoff_match_player_uploads'), 404);

        $match = $upload->playoffMatch;
        abort_unless($match !== null, 404);

        $this->ensurePlayerOwnsPlayoffMatchAccess($request, $match);

        $path = str_replace('\\', '/', trim((string) $upload->image_path));
        if ($path !== '' && str_starts_with($path, 'upload/playoff-match-uploads/')) {
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $matchId = (int) $match->id;
        $upload->delete();

        return redirect()
            ->route('player.profile.upload', ['match' => $matchId, 'kind' => 'playoff'])
            ->with('status', 'Image removed.');
    }

    /**
     * @param  array<string, mixed>  $extraViewData
     */
    protected function profileShell(Request $request, string $section, ?string $breadcrumbTail, string $pageTitle, string $metaDescription, array $extraViewData = []): View
    {
        $allowed = ['personal', 'league', 'password', 'location', 'upload'];
        abort_unless(in_array($section, $allowed, true), 404);

        return view('player.profile.shell', array_merge(
            $this->profilePayload($request),
            [
                'activeSection' => $section,
                'profileBreadcrumbTail' => $breadcrumbTail,
                'profilePageTitle' => $pageTitle,
                'profileMetaDescription' => $metaDescription,
            ],
            $extraViewData,
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $request->filled('preferred_play_time')) {
            $request->merge(['preferred_play_time' => null]);
        }
        if (! $request->filled('preferred_play_date')) {
            $request->merge(['preferred_play_date' => null]);
        }

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:64'],
            'date_of_birth' => ['nullable', 'date'],
            'ntrp' => ['nullable', 'string', 'max:32', 'regex:/^$|^not-sure$|^[0-9]+(\.[0-9]+)?$/'],
            'home_court' => ['nullable', 'string', 'max:255'],
            'preferred_play_date' => ['nullable', 'date'],
            'preferred_play_time' => ['nullable', 'string', 'max:16'],
            'dominant_hand' => ['nullable', 'in:Right,Left,Ambidextrous'],
            'league_id' => ['nullable', 'integer'],
            'group_card_id' => ['nullable', 'integer'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            '_section' => ['nullable', 'string', 'max:32'],
        ]);

        $updates = [];
        foreach (['first_name', 'last_name', 'phone', 'city', 'state'] as $column) {
            if (array_key_exists($column, $validated)) {
                $updates[$column] = $validated[$column] ?? null;
            }
        }

        $composedName = trim(((string) ($validated['first_name'] ?? '')).' '.((string) ($validated['last_name'] ?? '')));
        if ($composedName !== '') {
            $updates['name'] = $composedName;
        }

        foreach (['date_of_birth', 'home_court', 'dominant_hand'] as $column) {
            if (array_key_exists($column, $validated) && Schema::hasColumn('users', $column)) {
                $updates[$column] = $validated[$column] ?? null;
            }
        }

        foreach (['preferred_play_date', 'preferred_play_time'] as $column) {
            if (array_key_exists($column, $validated) && Schema::hasColumn('users', $column)) {
                $val = $validated[$column] ?? null;
                if ($column === 'preferred_play_time' && is_string($val) && $val !== '') {
                    $val = preg_match('/(\d{2}:\d{2})/', $val, $m) ? $m[1] : null;
                }
                $updates[$column] = ($val === '' || $val === null) ? null : $val;
            }
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'avatar-'.$user->id.'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dir = public_path('upload/user-avatar');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file->move($dir, $filename);

            $newPath = 'upload/user-avatar/'.$filename;
            $oldPath = (string) ($user->avatar_path ?? '');
            if ($oldPath !== '' && $oldPath !== 'upload/user-avatar/default-user-pic.png') {
                $oldFull = public_path($oldPath);
                if (File::exists($oldFull)) {
                    File::delete($oldFull);
                }
            }
            $updates['avatar_path'] = $newPath;
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        $registration = $this->profileRegistration($request);
        if ($registration && array_key_exists('ntrp', $validated)) {
            $ntrp = $validated['ntrp'] ?: null;
            $registration->update(['skill_level' => $ntrp]);
            UserSkillLevel::syncToUser($user, $ntrp);
        }

        return redirect()
            ->route('player.my-profile')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function profilePayload(Request $request): array
    {
        $user = $request->user();
        $registration = $this->profileRegistration($request);

        $firstName = trim((string) ($user->first_name ?? ''));
        $lastName = trim((string) ($user->last_name ?? ''));
        if ($firstName === '' && $lastName === '') {
            $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $division = (string) ($registration?->groupCard?->name ?? '—');
        $group = (string) ($registration?->group?->name ?? '—');
        $tournament = (string) ($registration?->league?->name ?? '');

        $roleLine = 'Player';
        if ($tournament !== '') {
            $roleLine .= ' · '.$tournament;
        }
        if ($group !== '—' && $group !== '') {
            $roleLine .= ' · '.$group;
        }

        $playerTournamentGroups = $this->playerTournamentGroups($user);
        $currentTournamentGroups = collect($playerTournamentGroups)
            ->filter(fn (array $group) => (bool) ($group['is_current'] ?? false))
            ->values()
            ->all();

        if ($currentTournamentGroups !== []) {
            $tournamentNames = collect($currentTournamentGroups)->pluck('tournament')->filter()->values();
            $roleLine = 'Player · '.$tournamentNames->join(', ');
            if (count($currentTournamentGroups) === 1) {
                $primaryEntry = $currentTournamentGroups[0]['registrations'][0] ?? null;
                if ($primaryEntry && ($primaryEntry['subgroup'] ?? '') !== 'Unassigned') {
                    $roleLine .= ' · '.$primaryEntry['subgroup'];
                }
            }
        }

        $myProfile = [
            'name' => trim((string) $user->name) !== '' ? (string) $user->name : trim($firstName.' '.$lastName),
            'roleLine' => $roleLine,
            'avatarUrl' => asset($user->avatar_path ?: 'upload/user-avatar/default-user-pic.png'),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'dob' => $user->date_of_birth?->format('Y-m-d') ?? '',
            'ntrp' => (string) (UserSkillLevel::resolvedFor($user) ?? $registration?->skill_level ?? ''),
            'email' => (string) $user->email,
            'phone' => (string) ($user->phone ?? ''),
            'city' => (string) ($user->city ?? ''),
            'state' => (string) ($user->state ?? ''),
            'division' => $division,
            'group' => $group,
            'homeCourt' => (string) ($user->home_court ?? ''),
            'preferredPlayDate' => $user->preferred_play_date?->format('Y-m-d') ?? '',
            'preferredPlayTime' => (string) ($user->preferred_play_time ?? ''),
            'dominantHand' => (string) ($user->dominant_hand ?? 'Right'),
        ];

        return array_merge([
            'leagueId' => (int) ($registration?->league_id ?? 0),
            'groupCardId' => (int) ($registration?->group_card_id ?? 0),
            'myProfile' => $myProfile,
            'playerTournamentGroups' => $playerTournamentGroups,
            'currentTournamentGroups' => $currentTournamentGroups,
        ], $this->profileLayoutTheme());
    }

    /**
     * CSS utility class strings for player profile layout and panel partials (passed as view data so @include receives them).
     *
     * @return array<string, mixed>
     */
    protected function profileLayoutTheme(): array
    {
        return [
            'profileInputClass' => 'w-full rounded-md border border-[#D1D5DB] bg-white px-3.5 py-2.5 text-[15px] text-[#374151] shadow-sm placeholder:text-[#9CA3AF] focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157] sm:text-[16px]',
            'profileInputReadonlyClass' => 'w-full cursor-not-allowed rounded-md border border-[#D1D5DB] bg-[#F9FAFB] px-3.5 py-2.5 text-[15px] text-[#6B7280] shadow-sm focus:border-[#D1D5DB] focus:ring-0 sm:text-[16px]',
            'profileLabelClass' => 'mb-1.5 block text-[12px] font-bold text-[#424242] sm:text-[13px]',
            'profileNavActive' => 'inline-block w-full rounded-lg bg-[#66A157] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-sm transition-colors sm:text-[15px]',
            'profileNavInactive' => 'inline-block w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#424242] transition-colors hover:bg-[#FAFAFA] sm:text-[15px]',
            'pwdLabelClass' => 'mb-1.5 block text-[12px] font-bold text-[#333333] sm:text-[13px]',
            'pwdInputClass' => 'w-full rounded-lg border border-[#E0E0E0] bg-white py-2.5 pl-3.5 pr-11 text-[15px] font-normal text-[#333333] shadow-sm placeholder:text-[#9E9E9E] focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157] sm:text-[16px]',
            'pwdEyeBtn' => 'absolute inset-y-0 right-0 flex items-center px-3 text-[#9E9E9E] transition-colors hover:text-[#757575] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#66A157] focus-visible:ring-offset-1 rounded-r-lg',
            'scheduleLabelClass' => 'mb-1.5 block text-[12px] font-bold text-[#000000] sm:text-[13px]',
            'scheduleInputClass' => 'w-full rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 text-[15px] font-normal text-[#000000] shadow-sm placeholder:text-[#757575] focus:border-[#5FA052] focus:outline-none focus:ring-1 focus:ring-[#5FA052] sm:text-[16px]',
            'scheduleSelectClass' => 'w-full appearance-none rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 pr-10 text-[15px] text-[#757575] shadow-sm focus:border-[#5FA052] focus:outline-none focus:ring-1 focus:ring-[#5FA052] sm:text-[16px]',
            'scheduleInputIconPad' => 'pr-10',
            'scheduleFieldIcon' => 'pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#757575]',
            'uploadMatchLabelClass' => 'mb-1.5 block text-[12px] font-bold text-[#333333] sm:text-[13px]',
            'uploadMatchSelectClass' => 'w-full appearance-none rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 pr-10 text-[15px] text-[#666666] shadow-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157] sm:text-[16px]',
            'uploadDropzoneClass' => 'relative flex min-h-[200px] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-[#CCCCCC] bg-[#F5F5F5] px-6 py-10 text-center sm:min-h-[220px]',
            'uploadNotesLabelClass' => 'mb-1.5 block text-[12px] font-bold text-[#333333] sm:text-[13px]',
            'uploadNotesClass' => 'min-h-[120px] w-full resize-y rounded-lg border border-[#DDDDDD] bg-white px-3.5 py-2.5 text-[15px] text-[#333333] placeholder:text-[#999999] shadow-sm focus:border-[#5DA051] focus:outline-none focus:ring-1 focus:ring-[#5DA051] sm:text-[16px]',
        ];
    }

    /**
     * Resolve which group match row powers the location / upload panels (from ?match= or smart default).
     *
     * @return array{selectedMatchId: int|null, selectedMatch: ?GroupMatch}
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<GroupMatch>
     */
    protected function playerMatchesQuery(Request $request, ?Collection $registrations = null)
    {
        $registrations ??= $this->profileRegistrations($request);
        if ($registrations->isEmpty() || ! Schema::hasTable('group_matches')) {
            return GroupMatch::query()->whereRaw('0 = 1');
        }

        $uid = (int) $request->user()->id;

        return GroupMatch::query()
            ->where(function ($participant) use ($uid) {
                $participant->where('home_user_id', $uid)
                    ->orWhere('away_user_id', $uid)
                    ->orWhere('home_partner_user_id', $uid)
                    ->orWhere('away_partner_user_id', $uid);
            })
            ->where(function ($scope) use ($registrations) {
                foreach ($registrations as $registration) {
                    $scope->orWhere(function ($q) use ($registration) {
                        $q->where('league_id', $registration->league_id);
                        if ($registration->group_card_id) {
                            $q->where(function ($qq) use ($registration) {
                                $qq->whereNull('group_card_id')
                                    ->orWhere('group_card_id', $registration->group_card_id);
                            });
                        }
                    });
                }
            })
            ->with(['group', 'homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'league', 'groupCard'])
            ->orderBy('match_date')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<PlayoffMatch>
     */
    protected function playerPlayoffMatchesQuery(Request $request, ?Collection $registrations = null)
    {
        $registrations ??= $this->profileRegistrations($request);
        if ($registrations->isEmpty() || ! Schema::hasTable('playoff_matches')) {
            return PlayoffMatch::query()->whereRaw('0 = 1');
        }

        $uid = (int) $request->user()->id;
        $divisionScopes = $registrations
            ->map(static fn (LeagueRegistration $registration) => [
                'league_id' => (int) $registration->league_id,
                'group_card_id' => $registration->group_card_id ? (int) $registration->group_card_id : null,
            ])
            ->unique(static fn (array $row) => $row['league_id'].'-'.($row['group_card_id'] ?? 'any'))
            ->values()
            ->all();

        return PlayoffMatch::query()
            ->where(function ($participant) use ($uid) {
                $participant->where('home_user_id', $uid)
                    ->orWhere('away_user_id', $uid);
            })
            ->where(function ($scope) use ($divisionScopes) {
                foreach ($divisionScopes as $division) {
                    $scope->orWhere(function ($q) use ($division) {
                        $q->where('league_id', $division['league_id']);
                        if ($division['group_card_id'] !== null) {
                            $q->where('group_card_id', $division['group_card_id']);
                        }
                    });
                }
            })
            ->with(['homeUser', 'awayUser', 'league', 'groupCard'])
            ->orderByRaw("FIELD(round, 'ppq', 'pq', 'qf', 'sf', 'f')")
            ->orderBy('slot')
            ->orderBy('match_date')
            ->orderBy('id');
    }

    protected function playerMatchSelectionContext(Request $request): array
    {
        $empty = [
            'selectedMatchId' => null,
            'selectedMatch' => null,
            'selectedPlayoffMatch' => null,
            'selectedMatchKind' => 'group',
        ];

        $kind = (string) $request->query('kind', 'group');
        $requestedId = (int) $request->query('match', 0);

        if ($kind === 'playoff' && Schema::hasTable('playoff_matches')) {
            $playoffs = $this->playerPlayoffMatchesQuery($request)->get();
            if ($playoffs->isEmpty()) {
                return $empty;
            }
            $validIds = $playoffs->pluck('id')->map(fn ($id) => (int) $id)->all();
            $selectedId = in_array($requestedId, $validIds, true)
                ? $requestedId
                : (int) $playoffs->first()->id;
            $selected = $playoffs->firstWhere('id', $selectedId) ?? $playoffs->first();

            return [
                'selectedMatchId' => (int) $selected->id,
                'selectedMatch' => null,
                'selectedPlayoffMatch' => $selected,
                'selectedMatchKind' => 'playoff',
            ];
        }

        $matches = $this->playerMatchesQuery($request)->get();

        if ($matches->isEmpty()) {
            return $empty;
        }

        $validIds = $matches->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selectedId = in_array($requestedId, $validIds, true)
            ? $requestedId
            : $this->defaultGroupMatchIdForPlayer($matches);

        $selected = $matches->firstWhere('id', $selectedId);
        if (! $selected instanceof GroupMatch) {
            $selected = $matches->first();
        }

        return [
            'selectedMatchId' => (int) $selected->id,
            'selectedMatch' => $selected,
            'selectedPlayoffMatch' => null,
            'selectedMatchKind' => 'group',
        ];
    }

    /**
     * Prefer the URL ?match= when valid; otherwise pick the player's "current" match:
     * earliest pending match on or after today, else the most recent pending match, else the first row.
     *
     * @param  Collection<int, GroupMatch>  $matches
     */
    protected function defaultGroupMatchIdForPlayer(Collection $matches): int
    {
        $first = $matches->first();
        if (! $first instanceof GroupMatch) {
            return 0;
        }

        $pending = $matches->filter(fn (GroupMatch $m) => $m->isPending())->values();
        if ($pending->isEmpty()) {
            return (int) $first->id;
        }

        $today = Carbon::today()->toDateString();
        $sortKey = static fn (GroupMatch $m): string => $m->match_date->format('Y-m-d')
            .'#'.str_pad((string) ((int) ($m->sort_order ?? 0)), 5, '0', STR_PAD_LEFT)
            .'#'.str_pad((string) $m->id, 10, '0', STR_PAD_LEFT);

        $upcoming = $pending->filter(fn (GroupMatch $m) => $m->match_date->toDateString() >= $today)
            ->sortBy($sortKey);

        if ($upcoming->isNotEmpty()) {
            return (int) $upcoming->first()->id;
        }

        return (int) $pending->sortByDesc($sortKey)->first()->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function locationPanelData(Request $request): array
    {
        $registration = $this->profileRegistration($request);
        $matchRegistrations = $this->profileRegistrationsForMatchesPanel($request);
        $profileActiveTournaments = collect($this->playerTournamentGroups($request->user()))
            ->filter(fn (array $group) => (bool) ($group['is_current'] ?? false))
            ->values()
            ->all();
        $base = [
            'profileLeagueName' => (string) ($registration?->league?->name ?? ''),
            'profileDivisionName' => (string) ($registration?->groupCard?->name ?? ''),
            'profileTournamentWindow' => $registration?->league instanceof League
                ? LeagueSeasonWindow::label($registration->league)
                : '',
            'profileActiveTournaments' => $profileActiveTournaments,
            'playerLeagueScheduleDays' => [],
            'playerPlayoffScheduleDays' => [],
            'playerScheduleDays' => [],
            'playerMatchOptions' => [],
            'locationSelectedMatchId' => null,
            'locationMatchPlayersLabel' => '',
            'locationDateValue' => '',
            'locationTimeValue' => '',
            'locationVenueValue' => '',
        ];

        if (! $registration && $matchRegistrations->isEmpty() && $profileActiveTournaments === []) {
            return $base;
        }

        $userId = (int) $request->user()->id;
        $matches = $this->playerMatchesQuery($request, $matchRegistrations)->get();
        $leagueForCalendar = $matches->first()?->league;
        $base['playerLeagueScheduleDays'] = $this->enrichPlayerScheduleDays(
            MatchSchedulePresenter::groupIntoDays(
                $matches,
                $leagueForCalendar,
                null,
                showSeedsInMeta: false,
                showLeagueInMeta: true,
            ),
            $userId,
        );
        $base['playerScheduleDays'] = $base['playerLeagueScheduleDays'];

        $playoffMatches = $this->playerPlayoffMatchesQuery($request, $matchRegistrations)->get();
        if ($playoffMatches->isEmpty()) {
            $leagueIds = $matchRegistrations
                ->pluck('league_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            if ($leagueIds !== []) {
                $playoffMatches = PlayoffMatch::query()
                    ->where(function ($participant) use ($userId) {
                        $participant->where('home_user_id', $userId)
                            ->orWhere('away_user_id', $userId);
                    })
                    ->whereIn('league_id', $leagueIds)
                    ->with(['homeUser', 'awayUser', 'league', 'groupCard'])
                    ->orderByRaw("FIELD(round, 'ppq', 'pq', 'qf', 'sf', 'f')")
                    ->orderBy('slot')
                    ->orderBy('match_date')
                    ->orderBy('id')
                    ->get();
            }
        }
        if ($playoffMatches->isNotEmpty()) {
            $base['playerPlayoffScheduleDays'] = $this->enrichPlayerPlayoffScheduleDays(
                MatchSchedulePresenter::playoffGroupIntoDays($playoffMatches),
                $userId,
            );
        }
        $base['playerMatchOptions'] = $matches->map(fn (GroupMatch $m) => [
            'id' => (int) $m->id,
            'label' => ($m->league?->name ? $m->league->name.' — ' : '')
                .$this->groupMatchVersusLabel($m).' · '.$m->match_date->format('D, M j, Y'),
        ])->values()->all();

        $leagueLabels = $matches
            ->map(fn (GroupMatch $m) => trim(($m->league?->name ?? '').($m->groupCard?->name ? ' · '.$m->groupCard->name : '')))
            ->filter()
            ->unique()
            ->values();
        if ($leagueLabels->isNotEmpty()) {
            $base['profileLeagueName'] = $leagueLabels->join(' · ');
            $base['profileDivisionName'] = '';
            $base['profileTournamentWindow'] = '';
        }

        $ctx = $this->playerMatchSelectionContext($request);
        if ($ctx['selectedMatch'] === null) {
            return $base;
        }

        $selected = $ctx['selectedMatch'];

        return array_merge($base, [
            'locationSelectedMatchId' => $ctx['selectedMatchId'],
            'locationMatchPlayersLabel' => $this->groupMatchVersusLabel($selected),
            'locationDateValue' => $selected->match_date->format('Y-m-d'),
            'locationTimeValue' => $this->normalizeTimeForTimeInput($selected->start_time),
            'locationVenueValue' => $this->formatMatchVenueInput($selected),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function uploadPanelData(Request $request): array
    {
        $ctx = $this->playerMatchSelectionContext($request);
        $base = [
            'uploadSelectedMatchId' => $ctx['selectedMatchId'],
            'uploadSelectedMatchKind' => $ctx['selectedMatchKind'],
            'uploadMatchPlayersLabel' => '',
            'uploadScheduledDateLabel' => null,
            'uploadExistingImages' => [],
            'uploadMatchOptions' => $this->playerUploadMatchOptions($request),
            'uploadWizardStart' => $request->query('step') === 'files' || $request->old('_upload_step') === 'files'
                ? 'files'
                : 'gallery',
        ];

        if ($ctx['selectedMatchKind'] === 'playoff' && $ctx['selectedPlayoffMatch'] instanceof PlayoffMatch) {
            $selected = $ctx['selectedPlayoffMatch'];
            $base['uploadMatchPlayersLabel'] = $this->playoffMatchVersusLabel($selected);
            $base['uploadScheduledDateLabel'] = $selected->match_date
                ? $selected->match_date->format('l, M j, Y')
                : 'Date not set';

            if (Schema::hasTable('playoff_match_player_uploads')) {
                $base['uploadExistingImages'] = PlayoffMatchPlayerUpload::query()
                    ->where('playoff_match_id', $selected->id)
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn (PlayoffMatchPlayerUpload $row) => [
                        'id' => $row->id,
                        'url' => asset($row->image_path),
                        'upload_date' => $row->upload_date->format('l, M j, Y'),
                        'notes' => $row->notes,
                        'kind' => 'playoff',
                    ])
                    ->values()
                    ->all();
            }

            return $base;
        }

        if (! $ctx['selectedMatch'] instanceof GroupMatch) {
            return $base;
        }

        $selected = $ctx['selectedMatch'];
        $base['uploadMatchPlayersLabel'] = $this->groupMatchVersusLabel($selected);
        $base['uploadScheduledDateLabel'] = $selected->match_date->format('l, M j, Y');

        if (Schema::hasTable('group_match_player_uploads')) {
            $base['uploadExistingImages'] = GroupMatchPlayerUpload::query()
                ->where('group_match_id', $selected->id)
                ->orderByDesc('id')
                ->get()
                ->map(fn (GroupMatchPlayerUpload $row) => [
                    'id' => $row->id,
                    'url' => asset($row->image_path),
                    'upload_date' => $row->upload_date->format('l, M j, Y'),
                    'notes' => $row->notes,
                    'kind' => 'group',
                ])
                ->values()
                ->all();
        }

        return $base;
    }

    /**
     * @return list<array{id: int, kind: string, label: string}>
     */
    protected function playerUploadMatchOptions(Request $request): array
    {
        $options = [];
        foreach ($this->playerMatchesQuery($request)->get() as $m) {
            $options[] = [
                'id' => (int) $m->id,
                'kind' => 'group',
                'label' => $this->groupMatchVersusLabel($m).' · '.$m->match_date->format('D, M j, Y'),
            ];
        }
        foreach ($this->playerPlayoffMatchesQuery($request)->get() as $m) {
            $dateLabel = $m->match_date ? $m->match_date->format('D, M j, Y') : 'Date TBD';
            $options[] = [
                'id' => (int) $m->id,
                'kind' => 'playoff',
                'label' => '[Playoff] '.$this->playoffMatchVersusLabel($m).' · '.$dateLabel,
            ];
        }

        return $options;
    }

    /**
     * @param  list<array{dateLabel: string, matches: list<array<string, mixed>>}>  $days
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    protected function enrichPlayerScheduleDays(array $days, int $userId): array
    {
        $matchIds = [];
        foreach ($days as $day) {
            foreach ($day['matches'] ?? [] as $row) {
                $gid = (int) ($row['groupMatchId'] ?? 0);
                if ($gid > 0) {
                    $matchIds[] = $gid;
                }
            }
        }

        $uploadedSet = [];
        if ($matchIds !== [] && Schema::hasTable('group_match_player_uploads')) {
            foreach (GroupMatchPlayerUpload::query()
                ->whereIn('group_match_id', array_values(array_unique($matchIds)))
                ->where('uploaded_by_user_id', $userId)
                ->pluck('group_match_id') as $id) {
                $uploadedSet[(int) $id] = true;
            }
        }

        $scheduleIndex = PlayerMatchDayConflict::scheduleIndexForPlayerIds([$userId]);

        foreach ($days as $dayIndex => $day) {
            foreach ($day['matches'] ?? [] as $matchIndex => $row) {
                $gid = (int) ($row['groupMatchId'] ?? 0);
                if ($gid <= 0) {
                    continue;
                }
                $hasUpload = isset($uploadedSet[$gid]);
                $days[$dayIndex]['matches'][$matchIndex]['hasPlayerUpload'] = $hasUpload;
                $days[$dayIndex]['matches'][$matchIndex]['uploadUrl'] = route('player.profile.upload', [
                    'match' => $gid,
                ] + ($hasUpload ? [] : ['step' => 'files']));
                if (! $hasUpload) {
                    $days[$dayIndex]['matches'][$matchIndex]['score'] = null;
                    $days[$dayIndex]['matches'][$matchIndex]['scoreRaw'] = '';
                    $days[$dayIndex]['matches'][$matchIndex]['homeSideWon'] = null;
                    $days[$dayIndex]['matches'][$matchIndex]['winnerLabel'] = null;
                }

                $dateValue = trim((string) ($row['dateValue'] ?? ''));
                if ($dateValue !== '') {
                    $days[$dayIndex]['matches'][$matchIndex]['scheduleConflictMessages'] = PlayerMatchDayConflict::viewerNoticeLinesFromIndex(
                        $scheduleIndex,
                        $userId,
                        $dateValue,
                        $gid,
                    );
                }
            }
        }

        return $days;
    }

    /**
     * @param  list<array{dateLabel: string, matches: list<array<string, mixed>>}>  $days
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    protected function enrichPlayerPlayoffScheduleDays(array $days, int $userId): array
    {
        $matchIds = [];
        foreach ($days as $day) {
            foreach ($day['matches'] ?? [] as $row) {
                $pid = (int) ($row['playoffMatchId'] ?? 0);
                if ($pid > 0) {
                    $matchIds[] = $pid;
                }
            }
        }

        $uploadedSet = [];
        if ($matchIds !== [] && Schema::hasTable('playoff_match_player_uploads')) {
            foreach (PlayoffMatchPlayerUpload::query()
                ->whereIn('playoff_match_id', array_values(array_unique($matchIds)))
                ->where('uploaded_by_user_id', $userId)
                ->pluck('playoff_match_id') as $id) {
                $uploadedSet[(int) $id] = true;
            }
        }

        $scheduleIndex = PlayerMatchDayConflict::scheduleIndexForPlayerIds([$userId]);

        foreach ($days as $dayIndex => $day) {
            foreach ($day['matches'] ?? [] as $matchIndex => $row) {
                $pid = (int) ($row['playoffMatchId'] ?? 0);
                if ($pid <= 0) {
                    continue;
                }
                $hasUpload = isset($uploadedSet[$pid]);
                $days[$dayIndex]['matches'][$matchIndex]['hasPlayerUpload'] = $hasUpload;
                $days[$dayIndex]['matches'][$matchIndex]['uploadUrl'] = route('player.profile.upload', [
                    'match' => $pid,
                    'kind' => 'playoff',
                ] + ($hasUpload ? [] : ['step' => 'files']));
                if (! $hasUpload) {
                    $days[$dayIndex]['matches'][$matchIndex]['score'] = null;
                    $days[$dayIndex]['matches'][$matchIndex]['scoreRaw'] = '';
                    $days[$dayIndex]['matches'][$matchIndex]['homeSideWon'] = null;
                    $days[$dayIndex]['matches'][$matchIndex]['winnerLabel'] = null;
                }

                $dateValue = trim((string) ($row['dateValue'] ?? ''));
                if ($dateValue !== '') {
                    $days[$dayIndex]['matches'][$matchIndex]['scheduleConflictMessages'] = PlayerMatchDayConflict::viewerNoticeLinesFromIndex(
                        $scheduleIndex,
                        $userId,
                        $dateValue,
                        null,
                        $pid,
                    );
                }
            }
        }

        return $days;
    }

    protected function resolvedWinnerSideForPersistence(string $scoreTrimmed, ?string $winnerSideInput): ?string
    {
        $winnerInput = in_array($winnerSideInput, ['home', 'away'], true) ? $winnerSideInput : null;

        if ($scoreTrimmed !== '' && MatchScoreReader::isWalkover($scoreTrimmed)) {
            return $winnerInput ?? MatchScoreReader::homeSideWonFromWalkover($scoreTrimmed, $winnerInput);
        }

        if ($scoreTrimmed !== '') {
            $parsed = MatchScoreReader::homeSideWon($scoreTrimmed);
            if ($parsed !== null) {
                return $parsed ? 'home' : 'away';
            }
        }

        return $winnerInput;
    }

    protected function resolvedWinnerUserIdForPersistence(GroupMatch $match, ?string $winnerSide): ?int
    {
        if ($winnerSide === null || $match->format !== GroupMatchFormat::Singles) {
            return null;
        }

        return $winnerSide === 'home'
            ? (int) $match->home_user_id
            : (int) $match->away_user_id;
    }

    protected function formatMatchVenueInput(GroupMatch $match): string
    {
        $parts = array_filter([
            trim((string) ($match->venue ?? '')),
            trim((string) ($match->court ?? '')),
        ], fn (string $p) => $p !== '');

        return implode(' · ', $parts);
    }

    protected function ensurePlayerOwnsGroupMatchAccess(Request $request, GroupMatch $match): void
    {
        abort_unless($this->playerCanAccessGroupMatch($request, $match), 403);
    }

    protected function ensurePlayerOwnsPlayoffMatchAccess(Request $request, PlayoffMatch $match): void
    {
        abort_unless($this->playerCanAccessPlayoffMatch($request, $match), 403);
    }

    protected function playerCanAccessPlayoffMatch(Request $request, PlayoffMatch $match): bool
    {
        $uid = (int) $request->user()->id;
        $isParticipant = ((int) $match->home_user_id === $uid) || ((int) $match->away_user_id === $uid);
        if (! $isParticipant) {
            return false;
        }

        foreach ($this->profileRegistrations($request) as $registration) {
            if ((int) $match->league_id !== (int) $registration->league_id) {
                continue;
            }
            if ($registration->group_card_id && (int) $match->group_card_id !== (int) $registration->group_card_id) {
                continue;
            }

            return true;
        }

        return false;
    }

    protected function playoffMatchVersusLabel(PlayoffMatch $match): string
    {
        return MatchSchedulePresenter::playoffSideName($match, 'home')
            .' vs '
            .MatchSchedulePresenter::playoffSideName($match, 'away');
    }

    protected function resolvedWinnerUserIdForPlayoff(PlayoffMatch $match, ?string $winnerSide): ?int
    {
        if ($winnerSide === null) {
            return null;
        }

        return $winnerSide === 'home'
            ? (int) $match->home_user_id
            : (int) $match->away_user_id;
    }

    protected function playerCanAccessGroupMatch(Request $request, GroupMatch $match): bool
    {
        $uid = (int) $request->user()->id;
        $isParticipant = ((int) $match->home_user_id === $uid) || ((int) $match->away_user_id === $uid);
        if ($match->format === GroupMatchFormat::Doubles) {
            $isParticipant = $isParticipant
                || ((int) ($match->home_partner_user_id ?? 0) === $uid)
                || ((int) ($match->away_partner_user_id ?? 0) === $uid);
        }
        if (! $isParticipant) {
            return false;
        }

        foreach ($this->profileRegistrations($request) as $registration) {
            if ((int) $match->league_id !== (int) $registration->league_id) {
                continue;
            }
            if ($registration->group_card_id) {
                $matchCardId = $match->group_card_id;
                if ($matchCardId !== null && (int) $matchCardId !== (int) $registration->group_card_id) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Store HTML time input (H:i) in group_matches.start_time.
     */
    protected function normalizeTimeForStorage(string $time): string
    {
        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            if (preg_match('/^(\d{1,2}):(\d{2})$/', trim($time), $m)) {
                $h = max(0, min(23, (int) $m[1]));

                return str_pad((string) $h, 2, '0', STR_PAD_LEFT).':'.$m[2];
            }

            return substr(trim($time), 0, 32);
        }
    }

    protected function normalizeTimeForTimeInput(mixed $startTime): string
    {
        $t = trim((string) ($startTime ?? ''));
        if ($t === '' || strcasecmp($t, 'TBA') === 0) {
            return '';
        }
        try {
            return Carbon::parse($t)->format('H:i');
        } catch (\Throwable) {
            if (preg_match('/(\d{1,2}):(\d{2})/', $t, $m)) {
                $h = max(0, min(23, (int) $m[1]));

                return str_pad((string) $h, 2, '0', STR_PAD_LEFT).':'.$m[2];
            }

            return '';
        }
    }

    protected function groupMatchVersusLabel(GroupMatch $match): string
    {
        return $this->formatLocationSideNames($match, 'home').' vs '.$this->formatLocationSideNames($match, 'away');
    }

    protected function formatLocationSideNames(GroupMatch $match, string $side): string
    {
        $isHome = $side === 'home';
        if ($match->format === GroupMatchFormat::Doubles) {
            if ($isHome && $match->homePartnerUser) {
                return $this->matchPlayerDisplayName($match->homeUser).' & '.$this->matchPlayerDisplayName($match->homePartnerUser);
            }
            if (! $isHome && $match->awayPartnerUser) {
                return $this->matchPlayerDisplayName($match->awayUser).' & '.$this->matchPlayerDisplayName($match->awayPartnerUser);
            }
        }

        $u = $isHome ? $match->homeUser : $match->awayUser;

        return $this->matchPlayerDisplayName($u);
    }

    protected function matchPlayerDisplayName(?User $user): string
    {
        $rawName = trim((string) ($user?->name ?? ''));
        $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);

        return $displayName !== '' ? $displayName : '—';
    }

    /**
     * @return array<string, mixed>
     */
    protected function chooseLeaguePanelData(Request $request): array
    {
        $user = $request->user();
        $registeredLeagueIds = $this->registeredLeagueIdsForUser($user);

        $allLeagues = LeagueMenuHelper::activeLeagues();
        $registrationLeagues = $allLeagues
            ->filter(fn ($league) => ! in_array((int) $league->id, $registeredLeagueIds, true))
            ->values();

        $playerSkillLevel = $this->playerFixedSkillLevel($user, $request) ?? '';
        $playerSkillLabel = $playerSkillLevel === 'not-sure'
            ? 'Not Sure'
            : ($playerSkillLevel !== '' ? $playerSkillLevel : '—');

        return [
            'registrationLeagues' => $registrationLeagues,
            'registrationClosedDivisions' => \App\Support\LeagueRegistrationGate::closedSelectionKeys(),
            'registrationClosedGroupCards' => \App\Support\LeagueRegistrationGate::closedGroupCardKeys(),
            'leagueEntryFees' => \App\Support\LeagueEntryFee::mapForLeagues($allLeagues),
            'stripePublishableKey' => (string) (config('services.stripe.key') ?: env('STRIPE_PUBLISHABLE_KEY', '')),
            'tournamentGroupsUrl' => route('player.profile.league.tournament-groups'),
            'playerFixedSkillLevel' => $playerSkillLevel,
            'playerFixedSkillLabel' => $playerSkillLabel,
            'hasPlayerSkillLevel' => $playerSkillLevel !== '' && $playerSkillLevel !== 'not-sure' && is_numeric($playerSkillLevel),
            'registeredLeagueCount' => count($registeredLeagueIds),
            'registrationSkillLevelValues' => ['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', 'not-sure'],
        ];
    }

    protected function partnerSkillLevelFromUser(User $user): ?string
    {
        $skill = trim((string) (UserSkillLevel::resolvedFor($user) ?? ''));

        if ($skill === '' || $skill === 'not-sure' || ! is_numeric($skill)) {
            return null;
        }

        return $skill;
    }

    /**
     * @return list<int>
     */
    protected function registeredLeagueIdsForUser(User $user): array
    {
        return LeagueRegistration::query()
            ->where('user_id', $user->id)
            ->whereHas('league', fn ($q) => $q->where('stats', 'active'))
            ->pluck('league_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function userIsRegisteredInLeague(User $user, int $leagueId): bool
    {
        return in_array($leagueId, $this->registeredLeagueIdsForUser($user), true);
    }

    protected function playerFixedSkillLevel(User $user, Request $request): ?string
    {
        $fromUser = trim((string) (UserSkillLevel::resolvedFor($user) ?? ''));
        if ($fromUser !== '' && $fromUser !== 'not-sure' && is_numeric($fromUser)) {
            return $fromUser;
        }

        return null;
    }

    /**
     * @return Collection<int, LeagueRegistration>
     */
    protected function profileRegistrations(Request $request): Collection
    {
        $query = $request->user()
            ->leagueRegistrations()
            ->with(['league', 'groupCard', 'group'])
            ->whereHas('league', fn ($q) => $q->where('stats', 'active'))
            ->whereHas('groupCard', fn ($q) => $q->where('status', 'active'));

        if ($request->filled('league_id')) {
            $query->where('league_id', (int) $request->input('league_id'));
        }
        if ($request->filled('group_card_id')) {
            $query->where('group_card_id', (int) $request->input('group_card_id'));
        }

        return $query->orderByDesc('id')->get();
    }

    protected function profileRegistration(Request $request): ?LeagueRegistration
    {
        $registrations = $this->profileRegistrations($request);
        if ($registrations->isEmpty()) {
            return null;
        }

        if ($request->filled('league_id') || $request->filled('group_card_id')) {
            return $registrations->first();
        }

        return $this->pickPrimaryRegistration($registrations);
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $registrations
     */
    protected function pickPrimaryRegistration(Collection $registrations): ?LeagueRegistration
    {
        if ($registrations->isEmpty()) {
            return null;
        }

        $current = $this->currentLeagueRegistrations($registrations);

        if ($current->isNotEmpty()) {
            return $current->sortByDesc('id')->first();
        }

        return $registrations->first();
    }

    /**
     * Registrations in leagues that are currently in season (for My Matches when no explicit league filter).
     *
     * @return Collection<int, LeagueRegistration>
     */
    protected function profileRegistrationsForMatchesPanel(Request $request): Collection
    {
        $registrations = $this->profileRegistrations($request);
        if ($registrations->isEmpty()) {
            return $registrations;
        }

        if ($request->filled('league_id') || $request->filled('group_card_id')) {
            return $registrations;
        }

        $currentRegistrations = $this->currentLeagueRegistrations($registrations);
        if ($currentRegistrations->isNotEmpty()) {
            return $currentRegistrations;
        }

        return $registrations;
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $registrations
     * @return Collection<int, LeagueRegistration>
     */
    protected function currentLeagueRegistrations(Collection $registrations): Collection
    {
        return $registrations->filter(function (LeagueRegistration $registration) {
            $league = $registration->league;

            return $league instanceof League && LeagueSeasonWindow::isCurrent($league);
        })->values();
    }

    /**
     * @return list<array{
     *     tournament: string,
     *     window: string,
     *     is_current: bool,
     *     status_label: string,
     *     registrations: list<array{group: string, subgroup: string, format: string}>
     * }>
     */
    protected function playerTournamentGroups(User $user): array
    {
        $registrations = $user->leagueRegistrations()
            ->with(['league', 'groupCard', 'group'])
            ->whereHas('league', fn ($q) => $q->whereNull('finished_at'))
            ->orderByDesc('id')
            ->get();

        $today = now()->startOfDay();
        $grouped = [];

        foreach ($registrations as $registration) {
            $league = $registration->league;
            if (! $league instanceof League) {
                continue;
            }

            $leagueId = (int) $league->id;
            if (! isset($grouped[$leagueId])) {
                $season = LeagueSeasonWindow::status($league, $today);
                $grouped[$leagueId] = [
                    'tournament' => trim((string) $league->name) ?: '—',
                    'window' => LeagueSeasonWindow::label($league),
                    'is_current' => $season['is_current'],
                    'status_label' => $season['label'],
                    'registrations' => [],
                ];
            }

            $grouped[$leagueId]['registrations'][] = [
                'group' => trim((string) ($registration->groupCard?->name ?? '')) ?: '—',
                'subgroup' => trim((string) ($registration->group?->name ?? '')) ?: 'Unassigned',
                'format' => ucfirst((string) ($registration->registration_type ?? 'singles')),
            ];
        }

        $groups = array_values($grouped);

        usort($groups, function (array $a, array $b): int {
            if ($a['is_current'] !== $b['is_current']) {
                return $a['is_current'] ? -1 : 1;
            }

            return strcmp($a['tournament'], $b['tournament']);
        });

        return $groups;
    }
}
