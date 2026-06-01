<?php

namespace App\Http\Controllers;

use App\Models\CharityDonation;
use App\Support\CharityFundraisingStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCharityDonationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');

        $donations = CharityDonation::query()
            ->with(['user', 'charityCause'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($type !== '', fn ($q) => $q->where('donation_type', $type))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $totalCompleted = CharityFundraisingStats::current()['total_raised'];

        return view('admin.charity-donations.index', [
            'donations' => $donations,
            'status' => $status,
            'type' => $type,
            'totalCompleted' => $totalCompleted,
        ]);
    }
}
