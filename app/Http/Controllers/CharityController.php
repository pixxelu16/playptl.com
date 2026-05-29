<?php

namespace App\Http\Controllers;

use App\Support\CharityFundraisingStats;
use Illuminate\View\View;

class CharityController extends Controller
{
    public function show(): View
    {
        $stats = CharityFundraisingStats::current();

        return view('charity', array_merge([
            'stripePublishableKey' => (string) config('services.stripe.key', ''),
        ], $stats));
    }
}
