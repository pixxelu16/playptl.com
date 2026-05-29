<?php

namespace App\Http\Controllers;

use App\Models\CharityDonation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCharityDonationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $donations = CharityDonation::query()
            ->with('user')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $totalCompleted = CharityDonation::query()
            ->where('status', 'completed')
            ->sum('amount');

        return view('admin.charity-donations.index', [
            'donations' => $donations,
            'status' => $status,
            'totalCompleted' => $totalCompleted,
        ]);
    }
}
