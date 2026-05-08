<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class RegisterStripePaymentIntentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'league_id' => ['required', 'integer', 'exists:leagues,id'],
            'registration_tab' => ['required', 'string', 'in:singles,doubles'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $league = League::query()->findOrFail((int) $validated['league_id']);

        $email = strtolower((string) $validated['email']);
        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return response()->json([
                'message' => 'This email is already registered. Please sign in or use a different email address.',
            ], 422);
        }

        $amountCents = (int) ($validated['registration_tab'] === 'doubles'
            ? config('services.stripe.doubles_amount_cents', 4500)
            : config('services.stripe.singles_amount_cents', 3000));
        $currency = (string) config('services.stripe.currency', 'USD');

        $secret = (string) (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', ''));
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
                'message' => 'Unable to create payment intent.',
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

