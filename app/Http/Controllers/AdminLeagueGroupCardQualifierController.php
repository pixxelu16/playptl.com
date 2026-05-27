<?php

namespace App\Http\Controllers;

use App\Enums\GroupPlayoffFormat;
use App\Enums\PlayoffQualifierPath;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\PlayoffQualifier;
use App\Support\GroupPlayoffConfig;
use App\Support\LeagueSeasonPhase;
use App\Support\LeagueStandingsBuilder;
use App\Support\PlayoffBracketBuilder;
use App\Support\PlayoffPathAssigner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeagueGroupCardQualifierController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View|RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (! Schema::hasTable('playoff_qualifiers')) {
            return redirect()
                ->route('admin.league-management.points.index', [$league, $groupCard])
                ->with('status', 'Run migrations to enable Qualifier (playoff_qualifiers table).');
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

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

        $playerSchemaReady = Schema::hasTable('league_registrations')
            && Schema::hasColumn('league_registrations', 'group_id')
            && Schema::hasColumn('league_registrations', 'group_card_id');

        $playoffConfig = GroupPlayoffConfig::fromGroupCard($groupCard);
        $showQualifierPlayoffs = LeagueSeasonPhase::showQualifierAndPlayoffs($league, $groupCard->id);

        if (! $showQualifierPlayoffs) {
            PlayoffBracketBuilder::clearDivisionPlayoffData($league->id, $groupCard->id, $ageKeyDb);
        }

        $syncStatus = null;
        if ($showQualifierPlayoffs && $playerSchemaReady) {
            $synced = PlayoffPathAssigner::syncDivision($league, $groupCard, $ageGroupKey);
            if ($synced > 0 && Schema::hasTable('playoff_matches')) {
                $syncStatus = PlayoffBracketBuilder::rebuild($league, $groupCard, $ageGroupKey);
            } elseif ($synced > 0) {
                $syncStatus = "Paths updated for {$synced} player(s) from group playoff format.";
            }
        }

        $standingsRows = ($showQualifierPlayoffs && $playerSchemaReady)
            ? LeagueStandingsBuilder::forSubGroup($league, $groupCard, $ageGroupKey, null)
            : [];

        $saved = $showQualifierPlayoffs
            ? PlayoffQualifier::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->where('age_group_key', $ageKeyDb)
                ->get()
                ->keyBy('user_id')
            : collect();

        $rows = [];
        foreach ($standingsRows as $standing) {
            $uid = (int) $standing['userId'];
            $rank = (int) $standing['rank'];
            $path = $playoffConfig->pathForRank($rank);
            $q = $saved->get($uid);
            $rows[] = array_merge($standing, [
                'path' => $path,
                'pathLabel' => PlayoffPathAssigner::pathLabel($path),
                'notes' => (string) ($q?->notes ?? ''),
            ]);
        }

        $hasSavedPaths = $showQualifierPlayoffs && collect($rows)->contains(fn (array $row) => ($row['path'] ?? '') !== ''
            && ($row['path'] ?? '') !== PlayoffQualifierPath::Eliminated->value);

        return view('admin.league-management.qualifier.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'groups' => $groups,
            'playerSchemaReady' => $playerSchemaReady,
            'qualifierRows' => $rows,
            'playoffConfig' => $playoffConfig,
            'hasSavedPaths' => $hasSavedPaths,
            'syncStatus' => $syncStatus,
            'showQualifierPlayoffs' => $showQualifierPlayoffs,
            'qualifierUnavailableMessage' => LeagueSeasonPhase::qualifierPlayoffsUnavailableMessage($league),
        ]);
    }

    public function update(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless(Schema::hasTable('playoff_qualifiers'), 404);

        if (! LeagueSeasonPhase::showQualifierAndPlayoffs($league, $groupCard->id)) {
            return back()->withErrors([
                'qualifier' => LeagueSeasonPhase::qualifierPlayoffsUnavailableMessage($league),
            ]);
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

        $validated = $request->validate([
            'players' => ['required', 'array'],
            'players.*.user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'players.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $rosterIds = $this->divisionRosterUserIds($league, $groupCard, $ageGroupKey);

        foreach ($validated['players'] as $row) {
            $userId = (int) $row['user_id'];
            if ($rosterIds->isNotEmpty() && ! $rosterIds->contains($userId)) {
                continue;
            }

            PlayoffQualifier::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->where('age_group_key', $ageKeyDb)
                ->where('user_id', $userId)
                ->update([
                    'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
                ]);
        }

        PlayoffPathAssigner::syncDivision($league, $groupCard, $ageGroupKey);
        $status = 'Notes saved. Playoff paths follow the group format and current standings.';
        if (Schema::hasTable('playoff_matches')) {
            $status .= ' '.PlayoffBracketBuilder::rebuild($league, $groupCard, $ageGroupKey);
        }

        return redirect()
            ->route('admin.league-management.qualifier.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', $status);
    }

    public function clearAll(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless(Schema::hasTable('playoff_qualifiers'), 404);

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

        PlayoffQualifier::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->delete();

        if (Schema::hasTable('playoff_matches')) {
            PlayoffBracketBuilder::clearBracket($league->id, $groupCard->id, $ageKeyDb);
        }

        return redirect()
            ->route('admin.league-management.qualifier.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', 'Playoff data cleared. Open Qualifier again to re-apply paths from group format.');
    }

    private function ageGroupKeyFromRequest(Request $request): ?string
    {
        $ageGroupKey = (string) $request->query('age_group_key', '');

        return $ageGroupKey !== '' ? $ageGroupKey : null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function divisionRosterUserIds(League $league, GroupCard $groupCard, ?string $ageGroupKey): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('league_registrations')) {
            return collect();
        }

        $q = \App\Models\LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id);

        if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
            $q->where('age_group_key', $ageGroupKey);
        }

        return $q->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
    }
}
