<?php

namespace App\Http\Controllers;

use App\Models\CharityDonation;
use App\Support\CharityFundraisingStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class CharityDonationController extends Controller
{
    public function createPaymentIntent(Request $request): JsonResponse
    {
        if (! class_exists(StripeClient::class)) {
            return response()->json([
                'message' => 'Payments are temporarily unavailable. Please try again later.',
            ], 503);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'donor_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:64'],
            'zip' => ['nullable', 'string', 'max:20'],
        ]);

        $amountCents = (int) round(((float) $validated['amount']) * 100);
        if ($amountCents < 100) {
            return response()->json(['message' => 'Minimum donation is $1.'], 422);
        }

        $currency = (string) config('services.stripe.currency', 'USD');
        $secret = (string) (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            return response()->json(['message' => 'Stripe is not configured.'], 500);
        }

        $stripe = new StripeClient($secret);
        $email = isset($validated['email']) ? strtolower((string) $validated['email']) : '';

        try {
            $intent = $stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => strtolower($currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => 'Charity donation',
                'metadata' => [
                    'type' => 'charity_donation',
                    'donor_name' => (string) $validated['donor_name'],
                    'email' => $email,
                    'address' => (string) $validated['address'],
                    'city' => (string) $validated['city'],
                    'state' => (string) $validated['state'],
                    'zip' => (string) ($validated['zip'] ?? ''),
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

    public function store(Request $request): JsonResponse
    {
        if (! class_exists(StripeClient::class)) {
            return response()->json([
                'message' => 'Payments are temporarily unavailable. Please try again later.',
            ], 503);
        }

        $validated = $request->validate([
            'payment_intent_id' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'donor_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:64'],
            'zip' => ['nullable', 'string', 'max:20'],
        ]);

        if (CharityDonation::query()->where('transaction_id', $validated['payment_intent_id'])->exists()) {
            return response()->json(['message' => 'This donation was already recorded.'], 422);
        }

        $secret = (string) (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            return response()->json(['message' => 'Stripe is not configured.'], 500);
        }

        $stripe = new StripeClient($secret);

        try {
            $intent = $stripe->paymentIntents->retrieve($validated['payment_intent_id'], []);
        } catch (ApiErrorException) {
            return response()->json(['message' => 'Payment could not be verified.'], 422);
        }

        $expectedAmountCents = (int) round(((float) $validated['amount']) * 100);
        $expectedCurrency = strtolower((string) config('services.stripe.currency', 'USD'));
        $email = isset($validated['email']) ? strtolower((string) $validated['email']) : '';
        $meta = $intent->metadata ?? null;
        $metaType = is_object($meta) ? (string) ($meta->type ?? '') : '';
        $metaDonorName = is_object($meta) ? (string) ($meta->donor_name ?? '') : '';
        $metaEmail = is_object($meta) ? strtolower((string) ($meta->email ?? '')) : '';

        if (
            $intent->status !== 'succeeded'
            || (int) $intent->amount !== $expectedAmountCents
            || (string) $intent->currency !== $expectedCurrency
            || $metaType !== 'charity_donation'
            || $metaDonorName !== (string) $validated['donor_name']
            || ($email !== '' && $metaEmail !== $email)
        ) {
            return response()->json(['message' => 'Payment not completed or does not match donation details.'], 422);
        }

        $donation = CharityDonation::create([
            'user_id' => $request->user()?->id,
            'donor_name' => (string) $validated['donor_name'],
            'email' => $email !== '' ? $email : null,
            'address' => (string) $validated['address'],
            'city' => (string) $validated['city'],
            'state' => (string) $validated['state'],
            'zip' => isset($validated['zip']) && $validated['zip'] !== '' ? (string) $validated['zip'] : null,
            'amount' => round((float) $validated['amount'], 2),
            'currency' => strtoupper($expectedCurrency),
            'status' => 'completed',
            'transaction_id' => (string) $validated['payment_intent_id'],
            'meta' => [
                'payment_intent_status' => (string) $intent->status,
            ],
        ]);

        $stats = CharityFundraisingStats::current();

        return response()->json(array_merge([
            'message' => 'Thank you for your donation!',
            'donation_id' => $donation->id,
        ], $stats));
    }
}
