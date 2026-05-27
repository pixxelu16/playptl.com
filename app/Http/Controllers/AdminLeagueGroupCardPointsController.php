<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Support\LeagueStandingsBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminLeagueGroupCardPointsController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View|RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        $playerSchemaReady = Schema::hasTable('league_registrations')
            && Schema::hasColumn('league_registrations', 'group_id')
            && Schema::hasColumn('league_registrations', 'group_card_id');

        $groups = Group::query()
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            )
            ->orderBy('name')
            ->get();

        // No ?group= → Standings (overall). ?group=id → that subgroup only.
        $pointsView = $request->has('group') ? 'subgroup' : 'overall';

        $activeGroupId = 0;
        $activeGroup = null;
        $filterGroupId = null;

        if ($pointsView === 'subgroup') {
            $activeGroupId = (int) $request->query('group');
            $activeGroup = $groups->firstWhere('id', $activeGroupId);
            if (! $activeGroup instanceof Group && $groups->isNotEmpty()) {
                return redirect()->route('admin.league-management.points.index', [
                    'league' => $league,
                    'groupCard' => $groupCard,
                    'group' => $groups->first()->id,
                ] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []));
            }
            $filterGroupId = $activeGroupId > 0 ? $activeGroupId : null;
        }

        $standingsRows = $playerSchemaReady
            ? LeagueStandingsBuilder::forSubGroup($league, $groupCard, $ageGroupKey, $filterGroupId)
            : [];

        return view('admin.league-management.points.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'groups' => $groups,
            'pointsView' => $pointsView,
            'activeGroupId' => $activeGroupId,
            'activeGroup' => $activeGroup,
            'standingsRows' => $standingsRows,
            'playerSchemaReady' => $playerSchemaReady,
        ]);
    }
}
