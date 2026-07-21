<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\SiteSetting;
use App\Models\User;
use App\Helpers\LeagueMenuHelper;
use App\Support\LeagueEntryFee;
use App\Support\LeagueRegistrationGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class RegisterStripePaymentIntentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! class_exists(StripeClient::class)) {
            return response()->json([
                'message' => 'Payments are temporarily unavailable. Please try again later.',
            ], 503);
        }

        $validated = $request->validate([
            'league_id' => ['required', 'integer', 'exists:leagues,id'],
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'skill_level' => ['required', 'string', 'max:32'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'd2_email' => ['nullable', 'string', 'email', 'max:255'],
            'd2_phone' => ['nullable', 'string', 'max:32'],
        ]);

        $league = League::query()->findOrFail((int) $validated['league_id']);
        if (! LeagueMenuHelper::acceptsRegistration($league)) {
            return response()->json(['message' => 'Registration is not open for this tournament.'], 422);
        }

        $registrationClosed = LeagueRegistrationGate::closedReasonForSelection(
            $league,
            (string) $validated['registration_tab'],
            (string) $validated['skill_level'],
        );
        if ($registrationClosed !== null) {
            return response()->json(['message' => $registrationClosed], 422);
        }

        $email = strtolower((string) $validated['email']);
        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return response()->json([
                'message' => 'The email has already been taken.',
            ], 422);
        }

        if (!empty($validated['phone'])) {
            $rawPhone = trim((string) $validated['phone']);
            $digitsPhone = preg_replace('/\D+/', '', $rawPhone);
            if (User::query()->where('phone', $rawPhone)->orWhere('phone', $digitsPhone)->exists()) {
                return response()->json([
                    'message' => 'The phone has already been taken.',
                ], 422);
            }
        }

        if ((string) $validated['registration_tab'] === 'doubles') {
            if (!empty($validated['d2_email'])) {
                $d2Email = strtolower((string) $validated['d2_email']);
                if ($d2Email === $email) {
                    return response()->json([
                        'message' => 'Second player email must be different from your email.',
                    ], 422);
                }
                if (User::query()->whereRaw('LOWER(email) = ?', [$d2Email])->exists()) {
                    return response()->json([
                        'message' => 'The Player 2 email has already been taken.',
                    ], 422);
                }
            }
            if (!empty($validated['d2_phone'])) {
                $d2Phone = trim((string) $validated['d2_phone']);
                if (User::query()->where('phone', $d2Phone)->exists()) {
                    return response()->json([
                        'message' => 'The Player 2 phone has already been taken.',
                    ], 422);
                }
            }
        }

        $amountCents = LeagueEntryFee::centsForTab($league, (string) $validated['registration_tab']);
        $currency = SiteSetting::stripeCurrency();

        $secret = SiteSetting::stripeSecretKey();
        if ($secret === '') {
            return response()->json([
                'message' => 'Stripe is not configured.',
            ], 500);
        }

        $stripe = new StripeClient($secret);

        try {
            $intent = $stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => strtolower($currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => 'Tournament registration fee',
                'metadata' => [
                    'league_id' => (string) $league->id,
                    'league_name' => (string) $league->name,
                    'registration_tab' => (string) $validated['registration_tab'],
                    'email' => (string) strtolower($validated['email']),
                ],
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'client_secret' => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'amount_cents' => $amountCents,
            'currency' => strtoupper($currency),
        ]);
    }
}

