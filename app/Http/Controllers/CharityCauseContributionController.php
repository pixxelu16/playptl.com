<?php

namespace App\Http\Controllers;

use App\Models\CharityCause;
use App\Models\CharityDonation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharityCauseContributionController extends Controller
{
    public function store(Request $request, CharityCause $charityCause): JsonResponse
    {
        if (! $charityCause->is_active) {
            return response()->json(['message' => 'This charity cause is not available.'], 404);
        }

        $validated = $request->validate([
            'donation_type' => ['required', 'string', 'in:material,person'],
            'donor_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'material_detail' => ['required_if:donation_type,material', 'nullable', 'string', 'max:500'],
        ]);

        $type = (string) $validated['donation_type'];
        $email = isset($validated['email']) ? strtolower((string) $validated['email']) : null;

        CharityDonation::create([
            'user_id' => $request->user()?->id,
            'charity_cause_id' => $charityCause->id,
            'donation_type' => $type,
            'donor_name' => (string) $validated['donor_name'],
            'email' => $email !== '' ? $email : null,
            'phone' => isset($validated['phone']) && $validated['phone'] !== '' ? (string) $validated['phone'] : null,
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => null,
            'amount' => 0,
            'quantity' => round((float) $validated['quantity'], 2),
            'material_detail' => $type === 'material' ? (string) ($validated['material_detail'] ?? '') : null,
            'currency' => strtoupper((string) config('services.stripe.currency', 'USD')),
            'status' => 'submitted',
            'transaction_id' => null,
            'meta' => [
                'charity_cause_title' => (string) $charityCause->title,
            ],
        ]);

        $message = $type === 'material'
            ? 'Thank you! Your material contribution request has been received.'
            : 'Thank you! Your volunteer contribution has been received.';

        return response()->json(['message' => $message]);
    }
}
