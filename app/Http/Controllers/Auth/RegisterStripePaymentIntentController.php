<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\LeagueEntryFee;
use App\Support\LeagueRegistrationValidation;
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

        $validated = LeagueRegistrationValidation::validate($request, false);

        $league = $validated['league'];
        $tab = (string) $validated['registration_tab'];
        $isDoublesRegistration = (bool) $validated['is_doubles_registration'];
        $actualFeeTab = $isDoublesRegistration ? 'doubles' : 'singles';

        $amountCents = LeagueEntryFee::centsForTab($league, $actualFeeTab);
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
                'capture_method' => 'manual',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => 'Tournament registration fee',
                'metadata' => [
                    'league_id' => (string) $league->id,
                    'league_name' => (string) $league->name,
                    'registration_tab' => $tab,
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


