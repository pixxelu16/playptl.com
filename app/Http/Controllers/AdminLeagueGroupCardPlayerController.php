<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeagueGroupCardPlayerController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        $registrationsQuery = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->with(['user', 'group'])
            ->latest('id');

        if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
            $registrationsQuery->where('age_group_key', $ageGroupKey);
        }

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

        return view('admin.league-management.players.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'registrations' => $registrationsQuery->paginate(25),
            'groups' => $groups,
            'schemaReady' => Schema::hasTable('league_registrations')
                && Schema::hasColumn('league_registrations', 'group_id')
                && Schema::hasColumn('league_registrations', 'age_group_key'),
        ]);
    }

    public function updateGroup(Request $request, League $league, GroupCard $groupCard, LeagueRegistration $registration): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless($registration->league_id === $league->id && $registration->group_card_id === $groupCard->id, 404);

        $validated = $request->validate([
            'group_id' => ['nullable', 'integer', Rule::exists('groups', 'id')],
        ]);

        if (! Schema::hasColumn('league_registrations', 'group_id')) {
            return back()->with('status', 'Schema not ready yet. Run migrations first.');
        }

        $registration->update([
            'group_id' => $validated['group_id'] ?? null,
        ]);

        return back()->with('status', 'Player group updated.');
    }
}

