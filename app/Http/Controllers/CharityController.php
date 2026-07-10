<?php

namespace App\Http\Controllers;

use App\Models\CharityCause;
use App\Support\CharityFundraisingStats;
use Illuminate\View\View;

class CharityController extends Controller
{
    public function show(): View
    {
        $stats = CharityFundraisingStats::current();

        $charityCauses = CharityCause::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('title')
            ->get();

        return view('charity', array_merge([
            'stripePublishableKey' => (string) config('services.stripe.key', ''),
            'charityCauses' => $charityCauses,
            'charityCausesCount' => $charityCauses->count(),
            'selectedCharityCause' => null,
        ], $stats));
    }

    public function showCause(CharityCause $charityCause): View
    {
        if (! $charityCause->is_active) {
            abort(404);
        }

        $stats = CharityFundraisingStats::current();

        return view('charity-cause', array_merge([
            'stripePublishableKey' => (string) config('services.stripe.key', ''),
            'charityCause' => $charityCause,
        ], $stats));
    }
}
