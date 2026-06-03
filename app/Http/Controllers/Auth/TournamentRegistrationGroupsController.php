<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Support\TournamentRegistrationOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentRegistrationGroupsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'league_id' => ['required', 'integer', 'exists:leagues,id'],
            'tab' => ['required', 'string', 'in:singles,doubles'],
        ]);

        $league = League::query()->findOrFail((int) $validated['league_id']);
        $tab = (string) $validated['tab'];

        return response()->json([
            'groups' => TournamentRegistrationOptions::groupCardsFor($league, $tab),
            'tab' => $tab,
            'format_label' => $tab === 'singles' ? 'Singles' : 'Doubles',
        ]);
    }
}
