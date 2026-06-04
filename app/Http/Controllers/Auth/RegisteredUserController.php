<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Helpers\LeagueMenuHelper;
use App\Support\LeagueEntryFee;
use App\Support\LeagueRegistrationFlow;
use App\Support\LeagueRegistrationGate;
use App\Support\TournamentRegistrationOptions;
use App\Http\Controllers\Controller;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Stripe\StripeClient;
use App\Mail\RegistrationConfirmedMail;
use App\Mail\PartnerAddedMail;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $registrationLeagues = LeagueMenuHelper::activeLeagues();

        return view('auth.register', [
            'registrationLeagues' => $registrationLeagues,
            'registrationClosedDivisions' => LeagueRegistrationGate::closedSelectionKeys(),
            'registrationClosedGroupCards' => LeagueRegistrationGate::closedGroupCardKeys(),
            'leagueEntryFees' => LeagueEntryFee::mapForLeagues($registrationLeagues),
            'stripePublishableKey' => (string) (config('services.stripe.key') ?: env('STRIPE_PUBLISHABLE_KEY', '')),
            'tournamentGroupsUrl' => route('register.tournament-groups'),
        ]);
    }

    public function store(Request $request): Response
    {
        if (! class_exists(StripeClient::class)) {
            return $this->fail($request, 'Payments are temporarily unavailable. Please try again later.');
        }

        $base = $request->validate([
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'payment_intent_id' => ['required', 'string', 'max:255'],
        ]);

        $tab = (string) $base['registration_tab'];

        if ($tab === 'singles') {
            $specific = $request->validate([
                'phone_singles' => ['required', 'string', 'max:32'],
                'city_singles' => ['required', 'string', 'max:255'],
                'state_singles' => ['required', 'string', 'max:64'],
                'age_group_singles' => ['required', 'string', 'max:32'],
                'skill_singles' => ['required', 'string', 'max:32'],
                'sex_singles' => ['required', 'string', 'max:32'],
                'tournament_singles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_singles' => ['required', 'integer', 'exists:group_cards,id'],
                'singles_first' => ['nullable'],
                'singles_last' => ['nullable'],
            ]);
        } else {
            $specific = $request->validate([
                'phone_doubles' => ['required', 'string', 'max:32'],
                'city_doubles' => ['required', 'string', 'max:255'],
                'state_doubles' => ['required', 'string', 'max:64'],
                'age_group_doubles' => ['required', 'string', 'max:32'],
                'skill_doubles' => ['required', 'string', 'max:32'],
                'sex_doubles' => ['required', 'string', 'max:32'],
                'tournament_doubles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_doubles' => ['required', 'integer', 'exists:group_cards,id'],
                'd2_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'd2_phone' => ['required', 'string', 'max:32'],
                'd2_city' => ['required', 'string', 'max:255'],
                'd2_state' => ['required', 'string', 'max:64'],
                'd2_age_group' => ['required', 'string', 'max:32'],
                'd2_skill' => ['required', 'string', 'max:32'],
                'd2_sex' => ['required', 'string', 'max:32'],
                'd1_first' => ['nullable'],
                'd1_last' => ['nullable'],
                'd2_first' => ['nullable'],
                'd2_last' => ['nullable'],
            ]);

            $email1 = strtolower((string) $base['email']);
            $email2 = strtolower((string) $specific['d2_email']);

            if ($email2 === $email1) {
                return $this->fail($request, 'Second player email must be different from your email.');
            }
        }

        $leagueId = (int) ($tab === 'singles' ? $specific['tournament_singles'] : $specific['tournament_doubles']);
        $skillLevel = (string) ($tab === 'singles' ? $specific['skill_singles'] : $specific['skill_doubles']);
        if ($tab === 'doubles') {
            $averageSkill = TournamentRegistrationOptions::averageSkillLevels(
                (string) $specific['skill_doubles'],
                (string) $specific['d2_skill'],
            );
            if ($averageSkill === null) {
                return $this->fail($request, 'Both players need a valid skill level for group assignment.');
            }
            $skillLevel = $averageSkill;
        }
        $ageGroup = (string) ($tab === 'singles' ? $specific['age_group_singles'] : $specific['age_group_doubles']);
        $sex = (string) ($tab === 'singles' ? $specific['sex_singles'] : $specific['sex_doubles']);
        $phone = (string) ($tab === 'singles' ? $specific['phone_singles'] : $specific['phone_doubles']);
        $city = (string) ($tab === 'singles' ? $specific['city_singles'] : $specific['city_doubles']);
        $state = (string) ($tab === 'singles' ? $specific['state_singles'] : $specific['state_doubles']);

        $league = League::query()->findOrFail($leagueId);

        if (PaymentHistory::query()->where('transaction_id', $base['payment_intent_id'])->exists()) {
            return $this->fail($request, 'This payment was already used.');
        }

        $secret = (string) (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            return $this->fail($request, 'Stripe is not configured.');
        }

        $stripe = new StripeClient($secret);
        $intent = $stripe->paymentIntents->retrieve($base['payment_intent_id'], []);

        $expectedAmountCents = LeagueEntryFee::centsForTab($league, $tab);
        $expectedCurrency = strtolower((string) config('services.stripe.currency', 'USD'));
        $intentEmail = strtolower((string) ($intent->metadata['email'] ?? ''));
        $intentLeagueId = (string) ($intent->metadata['league_id'] ?? '');
        $intentTab = (string) ($intent->metadata['registration_tab'] ?? '');

        if (
            $intent->status !== 'succeeded'
            || (int) $intent->amount !== $expectedAmountCents
            || (string) $intent->currency !== $expectedCurrency
            || $intentEmail !== strtolower((string) $base['email'])
            || $intentLeagueId !== (string) $leagueId
            || $intentTab !== $tab
        ) {
            return $this->fail($request, 'Payment not completed or does not match registration.');
        }

        $groupCardId = (int) ($tab === 'singles' ? $specific['group_card_singles'] : $specific['group_card_doubles']);

        if ($tab === 'singles') {
            $expectedCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $tab, $skillLevel);
            if (! $expectedCard instanceof GroupCard) {
                return $this->fail($request, 'No group is available for your skill level in this tournament.');
            }
            if ($groupCardId !== (int) $expectedCard->id) {
                return $this->fail($request, 'Group assignment does not match your skill level.');
            }
            $groupCard = $expectedCard;
        } else {
            $expectedCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $tab, $skillLevel);
            if (! $expectedCard instanceof GroupCard) {
                return $this->fail($request, 'No group is available for your team skill level in this tournament.');
            }
            if ($groupCardId !== (int) $expectedCard->id) {
                return $this->fail($request, 'Group assignment does not match your team skill level.');
            }
            $groupCard = $expectedCard;
        }

        $registrationClosed = LeagueRegistrationGate::closedReason($league, $groupCard, $ageGroup);
        if ($registrationClosed !== null) {
            return $this->fail($request, $registrationClosed);
        }

        $groupId = LeagueRegistrationFlow::resolveGroupId($leagueId, $groupCard, $tab, $ageGroup);

        $user = User::create([
            'name' => $base['name'],
            'first_name' => $tab === 'singles' ? ($specific['singles_first'] ?? null) : ($specific['d1_first'] ?? null),
            'last_name' => $tab === 'singles' ? ($specific['singles_last'] ?? null) : ($specific['d1_last'] ?? null),
            'email' => $base['email'],
            'phone' => $phone,
            'role' => UserRole::Player,
            'status' => 'active',
            'password' => Hash::make($base['password']),
            'city' => $city,
            'state' => $state,
            'sex' => $sex,
            'registration_type' => $tab,
            'transaction_id' => (string) $intent->id,
        ]);

        $amountDecimal = number_format($expectedAmountCents / 100, 2, '.', '');
        PaymentHistory::create([
            'user_id' => $user->id,
            'league_id' => $leagueId,
            'amount' => $amountDecimal,
            'currency' => strtoupper((string) config('services.stripe.currency', 'USD')),
            'status' => 'completed',
            'transaction_id' => (string) $intent->id,
            'description' => 'Tournament registration fee',
            'meta' => [
                'registration_tab' => $tab,
                'payment_intent_status' => (string) $intent->status,
            ],
        ]);

        $primaryTeamKey = null;

        if ($tab === 'doubles') {
            $primaryTeamKey = (string) Str::uuid();
        }

        LeagueRegistration::updateOrCreate(
            ['user_id' => $user->id, 'league_id' => $leagueId],
            [
                'group_card_id' => $groupCard instanceof GroupCard ? $groupCard->id : null,
                'group_id' => $groupId,
                'skill_level' => $skillLevel,
                'age_group_key' => $ageGroup,
                'registration_type' => $tab,
                'team_key' => $primaryTeamKey,
                'payment_status' => 'completed',
            ]
        );

        // Doubles: create/attach second player as separate user + registration, send invite/setup email
        if ($tab === 'doubles') {
            $partnerEmail = strtolower((string) $specific['d2_email']);
            $partnerName = trim(((string) ($specific['d2_first'] ?? '')).' '.((string) ($specific['d2_last'] ?? '')));

            $partner = User::query()->where('email', $partnerEmail)->first();
            if (! $partner) {
                $partner = User::create([
                    'name' => $partnerName !== '' ? $partnerName : $partnerEmail,
                    'first_name' => $specific['d2_first'] ?? null,
                    'last_name' => $specific['d2_last'] ?? null,
                    'email' => $partnerEmail,
                    'phone' => (string) $specific['d2_phone'],
                    'city' => (string) $specific['d2_city'],
                    'state' => (string) $specific['d2_state'],
                    'sex' => (string) $specific['d2_sex'],
                    'role' => UserRole::Player,
                    'status' => 'active',
                    'password' => Hash::make(Str::random(32)),
                    'registration_type' => 'doubles',
                ]);
            }

            LeagueRegistration::updateOrCreate(
                ['user_id' => $partner->id, 'league_id' => $leagueId],
                [
                    'group_card_id' => $groupCard instanceof GroupCard ? $groupCard->id : null,
                    'group_id' => $groupId,
                    'skill_level' => (string) $specific['d2_skill'],
                    'age_group_key' => (string) $specific['d2_age_group'],
                    'registration_type' => 'doubles',
                    'team_key' => $primaryTeamKey,
                    'payment_status' => 'completed',
                ]
            );

            try {
                // Use Laravel password reset flow so partner can setup account with same email.
                $token = PasswordBroker::broker()->createToken($partner);
                $setupUrl = route('password.reset', ['token' => $token]).'?email='.urlencode($partnerEmail);

                Mail::to($partnerEmail)->send(new PartnerAddedMail(
                    inviterName: (string) $user->name,
                    leagueName: (string) $league->name,
                    setupUrl: $setupUrl,
                ));
            } catch (\Throwable $e) {
                // If mail fails, registration/payment is still valid; do not block.
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
        } catch (\Throwable $e) {
            // If mail fails, registration/payment is still valid; do not block.
        }

        $request->session()->regenerate();

        $loginUrl = route('login');
        $statusMessage = 'Your account is registered. Please sign in with your email and password.';
        $ajaxSuccessMessage = $statusMessage.' You will be redirected to the login page in 3 seconds.';

        if ($request->expectsJson()) {
            return response()->json([
                'redirect_url' => $loginUrl,
                'message' => $ajaxSuccessMessage,
                'redirect_delay_seconds' => 3,
            ]);
        }

        if ($request->ajax()) {
            return response()->view('auth.partials.register-response', [
                'type' => 'success',
                'message' => $ajaxSuccessMessage,
                'redirectUrl' => $loginUrl,
            ]);
        }

        return redirect()->route('login')->with('status', $statusMessage);
    }

    private function fail(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        if ($request->ajax()) {
            return response()->view('auth.partials.register-response', [
                'type' => 'error',
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['payment' => $message])->withInput();
    }
}
