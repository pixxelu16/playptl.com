<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Helpers\LeagueMenuHelper;
use App\Support\LeagueEntryFee;
use App\Support\LeagueRegistrationFlow;
use App\Support\LeagueRegistrationGate;
use App\Support\TournamentRegistrationOptions;
use App\Support\UserSkillLevel;
use App\Http\Controllers\Controller;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\PaymentHistory;
use App\Models\SiteSetting;
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
        $registrationLeagues = LeagueMenuHelper::registrationLeagues()
            ->filter(function ($league) {
                if ($league->registration_deadline !== null && now()->startOfDay()->gt($league->registration_deadline)) {
                    return false;
                }
                return true;
            })
            ->values();

        $allCategories = \App\Models\Category::query()->orderBy('menu_order')->get();
        $categoriesSingles = \App\Models\Category::query()->forType('single')->orderBy('menu_order')->get();
        $categoriesDoubles = \App\Models\Category::query()->forType('doubles')->orderBy('menu_order')->get();

        return view('auth.register', [
            'registrationLeagues' => $registrationLeagues,
            'categories' => $allCategories,
            'categoriesSingles' => $categoriesSingles,
            'categoriesDoubles' => $categoriesDoubles,
            'registrationClosedDivisions' => LeagueRegistrationGate::closedSelectionKeys(),
            'registrationClosedGroupCards' => LeagueRegistrationGate::closedGroupCardKeys(),
            'leagueEntryFees' => LeagueEntryFee::mapForLeagues($registrationLeagues),
            'publicKey' => \App\Support\PasswordEncryptionHelper::getPublicKey(),
            'stripePublishableKey' => SiteSetting::stripePublishableKey(),
            'tournamentGroupsUrl' => route('register.tournament-groups'),
        ]);
    }

    public function store(Request $request): Response
    {
        

        $roleInput = $request->input('role');
        if (in_array($roleInput, ['mentor', 'coach', 'student'], true)) {
            $validated = $request->validate([
                'role' => ['required', 'string', 'in:mentor,coach,student'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->symbols()],
                'city' => ['required', 'string', 'max:255'],
                'state' => ['required', 'string', 'max:64'],
            ]);

            $isPendingApproval = in_array($validated['role'], ['mentor', 'coach'], true);
            $status = $isPendingApproval ? 'pending' : 'active';

            $user = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $isPendingApproval, $status) {
                $createdUser = User::create([
                    'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'username' => User::generateUniqueUsername($validated['email']),
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'role' => UserRole::from($validated['role']),
                    'status' => $status,
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                ]);

                // Ensure Spatie role exists in database before assigning
                $roleName = ucfirst($validated['role']);
                \Spatie\Permission\Models\Role::findOrCreate($roleName, 'web');
                $createdUser->assignRole($roleName);

                return $createdUser;
            });

            try {
                if ($isPendingApproval) {
                    Mail::to($user->email)->send(new \App\Mail\ProviderApplicationReceivedMail($user));
                    $adminEmail = SiteSetting::getValue('contact_email');
                    if ($adminEmail) {
                        Mail::to($adminEmail)->send(new \App\Mail\AdminProviderApplicationNotificationMail($user));
                    }
                } else {
                    Mail::to($user->email)->send(new \App\Mail\UserRegisteredMail($user, false));
                    $adminEmail = SiteSetting::getValue('contact_email');
                    if ($adminEmail) {
                        Mail::to($adminEmail)->send(new \App\Mail\UserRegisteredMail($user, true));
                    }
                }
            } catch (\Throwable $e) {
                // Ignore mail fail
            }

            if ($isPendingApproval) {
                $pendingMsg = 'Your ' . ucfirst($validated['role']) . ' registration application has been submitted successfully! An administrator will review your application soon. You will receive an email once approved.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'redirect_url' => route('login'),
                        'message' => $pendingMsg,
                        'redirect_delay_seconds' => 4,
                    ]);
                }

                return redirect()->route('login')->with('status', $pendingMsg);
            }

            auth()->login($user);
            $request->session()->regenerate();

            $profileUrl = route(strtolower($user->role->value) . '.profile');
            $ajaxSuccessMessage = 'Registration successful! Redirecting to complete your profile...';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'redirect_url' => $profileUrl,
                    'message' => $ajaxSuccessMessage,
                    'redirect_delay_seconds' => 2,
                ]);
            }

            return redirect()->route(strtolower($user->role->value) . '.profile')->with('status', 'Registration successful!');
        }

        $isFreeReg = (SiteSetting::getValue('enable_free_registration', '0') === '1');

        if (!$isFreeReg && ! class_exists(StripeClient::class)) {
            return $this->fail($request, 'Payments are temporarily unavailable. Please try again later.');
        }

        $baseRules = [
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->symbols()],
            'category' => ['required', 'integer', 'exists:categories,id'],
        ];

        if ($isFreeReg) {
            $baseRules['payment_intent_id'] = ['nullable', 'string', 'max:255'];
        } else {
            $baseRules['payment_intent_id'] = ['required', 'string', 'max:255'];
        }

        $base = $request->validate($baseRules);

        $tab = (string) $base['registration_tab'];
        $categoryModel = \App\Models\Category::findOrFail((int) $base['category']);
        $category = (string) $categoryModel->id;
        $isDoublesRegistration = ($tab === 'doubles');

        if ($tab === 'singles') {
            $rules = [
                'phone_singles' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'city_singles' => ['required', 'string', 'max:255'],
                'state_singles' => ['required', 'string', 'max:64'],
                'age_group_singles' => ['required', 'string', 'max:32'],
                'skill_singles' => ['required', 'string', 'max:32'],
                'sex_singles' => ['required', 'string', 'max:32'],
                'tournament_singles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_singles' => ['required', 'integer', 'exists:group_cards,id'],
                'singles_first' => ['nullable'],
                'singles_last' => ['nullable'],
            ];
            if ($isDoublesRegistration) {
                $rules = array_merge($rules, [
                    'd2_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                    'd2_phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
                    'd2_city' => ['required', 'string', 'max:255'],
                    'd2_state' => ['required', 'string', 'max:64'],
                    'd2_age_group' => ['required', 'string', 'max:32'],
                    'd2_skill' => ['required', 'string', 'max:32'],
                    'd2_sex' => ['required', 'string', 'max:32'],
                    'd2_first' => ['nullable'],
                    'd2_last' => ['nullable'],
                ]);
            }
            $specific = $request->validate($rules, [], [
                'phone_singles' => 'phone',
                'city_singles' => 'city',
                'state_singles' => 'state',
                'age_group_singles' => 'age group',
                'skill_singles' => 'skill level',
                'sex_singles' => 'gender',
                'tournament_singles' => 'tournament',
                'group_card_singles' => 'group',
                'd2_email' => 'Player 2 email',
                'd2_phone' => 'Player 2 phone',
            ]);
        } else {
            $rules = [
                'phone_doubles' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'city_doubles' => ['required', 'string', 'max:255'],
                'state_doubles' => ['required', 'string', 'max:64'],
                'age_group_doubles' => ['required', 'string', 'max:32'],
                'skill_doubles' => ['required', 'string', 'max:32'],
                'sex_doubles' => ['required', 'string', 'max:32'],
                'tournament_doubles' => ['required', 'integer', 'exists:leagues,id'],
                'group_card_doubles' => ['required', 'integer', 'exists:group_cards,id'],
                'd2_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'd2_phone' => ['required', 'string', 'max:32', 'unique:users,phone'],
                'd2_city' => ['required', 'string', 'max:255'],
                'd2_state' => ['required', 'string', 'max:64'],
                'd2_age_group' => ['required', 'string', 'max:32'],
                'd2_skill' => ['required', 'string', 'max:32'],
                'd2_sex' => ['required', 'string', 'max:32'],
                'd1_first' => ['nullable'],
                'd1_last' => ['nullable'],
                'd2_first' => ['nullable'],
                'd2_last' => ['nullable'],
            ];
            $specific = $request->validate($rules, [], [
                'phone_doubles' => 'phone',
                'city_doubles' => 'city',
                'state_doubles' => 'state',
                'age_group_doubles' => 'age group',
                'skill_doubles' => 'skill level',
                'sex_doubles' => 'gender',
                'tournament_doubles' => 'tournament',
                'group_card_doubles' => 'group',
                'd2_email' => 'Player 2 email',
                'd2_phone' => 'Player 2 phone',
            ]);
        }

        if ($isDoublesRegistration) {
            $email1 = strtolower((string) $base['email']);
            $email2 = strtolower((string) $specific['d2_email']);

            if ($email2 === $email1) {
                return $this->fail($request, 'Second player email must be different from your email.');
            }
        }

        $leagueId = (int) ($tab === 'singles' ? $specific['tournament_singles'] : $specific['tournament_doubles']);
        $skillLevel = (string) ($tab === 'singles' ? $specific['skill_singles'] : $specific['skill_doubles']);
        $assignmentSkill = $skillLevel;

        if ($isDoublesRegistration) {
            $skillOne = (string) ($tab === 'singles' ? $specific['skill_singles'] : $specific['skill_doubles']);
            $skillTwo = (string) $specific['d2_skill'];
            if ($skillOne === 'not-sure' || $skillTwo === 'not-sure') {
                $assignmentSkill = 'not-sure';
            } else {
                $averageSkill = TournamentRegistrationOptions::averageSkillLevels($skillOne, $skillTwo);
                if ($averageSkill === null) {
                    return $this->fail($request, 'Both players need a valid skill level for group assignment.');
                }
                $assignmentSkill = $averageSkill;
            }
        }

        $ageGroup = (string) ($tab === 'singles' ? $specific['age_group_singles'] : $specific['age_group_doubles']);
        $sex = (string) ($tab === 'singles' ? $specific['sex_singles'] : $specific['sex_doubles']);
        $phone = (string) ($tab === 'singles' ? $specific['phone_singles'] : $specific['phone_doubles']);
        $city = (string) ($tab === 'singles' ? $specific['city_singles'] : $specific['city_doubles']);
        $state = (string) ($tab === 'singles' ? $specific['state_singles'] : $specific['state_doubles']);

        $league = League::query()->findOrFail($leagueId);

        if (! LeagueMenuHelper::acceptsRegistration($league)) {
            return $this->fail($request, 'Registration is not open for this tournament.');
        }

        $actualFeeTab = $isDoublesRegistration ? 'doubles' : 'singles';
        $expectedAmountCents = LeagueEntryFee::centsForTab($league, $actualFeeTab);

        if (!$isFreeReg) {
            if (PaymentHistory::query()->where('transaction_id', $base['payment_intent_id'])->exists()) {
                return $this->fail($request, 'This payment was already used.');
            }

            $secret = SiteSetting::stripeSecretKey();
            if ($secret === '') {
                return $this->fail($request, 'Stripe is not configured.');
            }

            $stripe = new StripeClient($secret);
            $intent = $stripe->paymentIntents->retrieve($base['payment_intent_id'], []);

            $expectedCurrency = strtolower(SiteSetting::stripeCurrency());
            $intentEmail = strtolower((string) ($intent->metadata['email'] ?? ''));
            $intentLeagueId = (string) ($intent->metadata['league_id'] ?? '');
            $intentTab = (string) ($intent->metadata['registration_tab'] ?? '');

            if (
                ! in_array($intent->status, ['succeeded', 'requires_capture'], true)
                || (int) $intent->amount !== $expectedAmountCents
                || (string) $intent->currency !== $expectedCurrency
                || $intentEmail !== strtolower((string) $base['email'])
                || $intentLeagueId !== (string) $leagueId
                || $intentTab !== $tab
            ) {
                return $this->fail($request, 'Payment not authorized or does not match registration.');
            }

            $transactionId = (string) $intent->id;
            $paymentIntentStatus = (string) $intent->status;
        } else {
            $transactionId = 'free_' . Str::uuid();
            $paymentIntentStatus = 'succeeded';
        }

        $groupCardId = (int) ($tab === 'singles' ? $specific['group_card_singles'] : $specific['group_card_doubles']);
        $expectedCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $actualFeeTab, $assignmentSkill);
        if (! $expectedCard instanceof GroupCard) {
            return $this->fail($request, 'No group is available for your skill level in this tournament.');
        }
        if ($groupCardId !== (int) $expectedCard->id) {
            return $this->fail($request, 'Group assignment does not match your skill level.');
        }
        $groupCard = $expectedCard;

        $registrationClosed = LeagueRegistrationGate::closedReason($league, $groupCard, $ageGroup);
        if ($registrationClosed !== null) {
            return $this->fail($request, $registrationClosed);
        }

        $groupId = LeagueRegistrationFlow::resolveGroupId($leagueId, $groupCard, $actualFeeTab, $ageGroup);
        $amountDecimal = $isFreeReg ? '0.00' : number_format($expectedAmountCents / 100, 2, '.', '');

        $user = \Illuminate\Support\Facades\DB::transaction(function () use (
            $base, $tab, $category, $specific, $phone, $city, $state, $sex, $skillLevel, $transactionId, $paymentIntentStatus, $expectedAmountCents, $leagueId, $groupCard, $groupId, $ageGroup, $amountDecimal, $isDoublesRegistration, $isFreeReg
        ) {
            $createdUser = User::create([
                'name' => $base['name'],
                'first_name' => $tab === 'singles' ? ($specific['singles_first'] ?? null) : ($specific['d1_first'] ?? null),
                'last_name' => $tab === 'singles' ? ($specific['singles_last'] ?? null) : ($specific['d1_last'] ?? null),
                'email' => $base['email'],
                'username' => User::generateUniqueUsername($base['email']),
                'phone' => $phone,
                'role' => UserRole::Player,
                'status' => 'active',
                'password' => Hash::make($base['password']),
                'city' => $city,
                'state' => $state,
                'sex' => $sex,
                'skill_level' => $skillLevel,
                'registration_type' => $tab,
                'transaction_id' => $transactionId,
            ]);

            \Spatie\Permission\Models\Role::findOrCreate('Player', 'web');
            $createdUser->assignRole('Player');

            PaymentHistory::create([
                'user_id' => $createdUser->id,
                'league_id' => $leagueId,
                'amount' => $amountDecimal,
                'currency' => strtoupper(SiteSetting::stripeCurrency()),
                'status' => 'completed',
                'transaction_id' => $transactionId,
                'description' => 'Tournament registration fee',
                'meta' => [
                    'registration_tab' => $tab,
                    'category' => $category,
                    'payment_intent_status' => $paymentIntentStatus,
                ],
            ]);

            $primaryTeamKey = null;
            if ($isDoublesRegistration) {
                $primaryTeamKey = (string) Str::uuid();
            }

            LeagueRegistration::updateOrCreate(
                ['user_id' => $createdUser->id, 'league_id' => $leagueId],
                [
                    'group_card_id' => $groupCard instanceof GroupCard ? $groupCard->id : null,
                    'group_id' => $groupId,
                    'skill_level' => $skillLevel,
                    'age_group_key' => $ageGroup,
                    'registration_type' => $tab,
                    'category' => $category,
                    'team_key' => $primaryTeamKey,
                    'payment_status' => 'completed',
                ]
            );

            UserSkillLevel::syncToUser($createdUser, $skillLevel);

            // Doubles: create/attach second player as separate user + registration
            if ($isDoublesRegistration) {
                $partnerEmail = strtolower((string) $specific['d2_email']);
                $partnerName = trim(((string) ($specific['d2_first'] ?? '')).' '.((string) ($specific['d2_last'] ?? '')));

                $partner = User::query()->where('email', $partnerEmail)->first();
                if (! $partner) {
                    $partner = User::create([
                        'name' => $partnerName !== '' ? $partnerName : $partnerEmail,
                        'first_name' => $specific['d2_first'] ?? null,
                        'last_name' => $specific['d2_last'] ?? null,
                        'email' => $partnerEmail,
                        'username' => User::generateUniqueUsername($partnerEmail),
                        'phone' => (string) $specific['d2_phone'],
                        'city' => (string) $specific['d2_city'],
                        'state' => (string) $specific['d2_state'],
                        'sex' => (string) $specific['d2_sex'],
                        'role' => UserRole::Player,
                        'status' => 'active',
                        'password' => Hash::make(Str::random(32)),
                        'registration_type' => $tab,
                        'skill_level' => (string) $specific['d2_skill'],
                    ]);
                    try {
                        $partner->assignRole('Player');
                    } catch (\Throwable $e) {
                        $partner->assignRole('player');
                    }
                } else {
                    UserSkillLevel::syncToUser($partner, (string) $specific['d2_skill']);
                }

                LeagueRegistration::updateOrCreate(
                    ['user_id' => $partner->id, 'league_id' => $leagueId],
                    [
                        'group_card_id' => $groupCard instanceof GroupCard ? $groupCard->id : null,
                        'group_id' => $groupId,
                        'skill_level' => (string) $specific['d2_skill'],
                        'age_group_key' => (string) $specific['d2_age_group'],
                        'registration_type' => $tab,
                        'category' => $category,
                        'team_key' => $primaryTeamKey,
                        'payment_status' => 'completed',
                    ]
                );
            }

            // Capture the Stripe Authorized payment only after everything succeeds
            if (!$isFreeReg) {
                try {
                    $stripeClient = new \Stripe\StripeClient(SiteSetting::stripeSecretKey());
                    $stripeClient->paymentIntents->capture($transactionId);
                } catch (\Throwable $e) {
                    // Throwing here triggers full DB rollback
                    throw new \RuntimeException('Stripe payment capture failed: ' . $e->getMessage());
                }
            }

            return $createdUser;
        });

        if ($isDoublesRegistration && isset($partner) && isset($partnerEmail)) {
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
                currency: strtoupper(SiteSetting::stripeCurrency()),
                paymentIntentId: (string) $intent->id,
            ));

            $adminEmail = SiteSetting::getValue('contact_email');
            if ($adminEmail) {
                $pName = null;
                $pEmail = null;
                $pPhone = null;
                $pSkill = null;
                if ($tab === 'doubles') {
                    $pName = trim(((string) ($specific['d2_first'] ?? '')).' '.((string) ($specific['d2_last'] ?? '')));
                    if ($pName === '') {
                        $pName = (string) ($specific['d2_email'] ?? '');
                    }
                    $pEmail = (string) ($specific['d2_email'] ?? '');
                    $pPhone = (string) ($specific['d2_phone'] ?? '');
                    $pSkill = (string) ($specific['d2_skill'] ?? '');
                }

                Mail::to($adminEmail)->send(new \App\Mail\AdminPlayerRegistrationNotificationMail(
                    playerName: (string) $user->name,
                    playerEmail: (string) $user->email,
                    playerPhone: (string) $user->phone,
                    leagueName: (string) $league->name,
                    registrationType: $tab,
                    skillLevel: $skillLevel,
                    amount: $amountDecimal,
                    currency: strtoupper(SiteSetting::stripeCurrency()),
                    paymentIntentId: (string) $intent->id,
                    partnerName: $pName,
                    partnerEmail: $pEmail,
                    partnerPhone: $pPhone,
                    partnerSkill: $pSkill,
                ));
            }
        } catch (\Throwable $e) {
            // If mail fails, registration/payment is still valid; do not block.
        }

        auth()->login($user);
        $request->session()->regenerate();

        $profileUrl = route('player.my-profile');
        $ajaxSuccessMessage = 'Registration successful! Redirecting to complete your profile...';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'redirect_url' => $profileUrl,
                'message' => $ajaxSuccessMessage,
                'redirect_delay_seconds' => 2,
            ]);
        }

        return redirect()->route('player.my-profile')->with('status', 'Registration successful!');
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
