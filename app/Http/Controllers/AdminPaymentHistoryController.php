<?php

namespace App\Http\Controllers;

use App\Helpers\LeagueMenuHelper;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $leagueId = (string) $request->query('league_id', '');
        $leagueIdInt = $leagueId !== '' && ctype_digit($leagueId) ? (int) $leagueId : null;

        $payments = PaymentHistory::query()
            ->with(['user', 'league'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($leagueIdInt !== null, fn ($q) => $q->where('league_id', $leagueIdInt))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $leagues = LeagueMenuHelper::activeLeagues(latestFirst: true);

        return view('admin.payment-histories.index', [
            'payments' => $payments,
            'status' => $status,
            'leagueId' => $leagueIdInt,
            'leagues' => $leagues,
        ]);
    }
}

