<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Support\LeagueRegistrationRoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeagueGroupCardGroupController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        $groupSearch = trim((string) $request->query('q', ''));

        $groupsQuery = Group::query()
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            );
        if ($groupSearch !== '') {
            $groupsQuery->where('name', 'like', '%'.$groupSearch.'%');
        }

        $playerSchemaReady = Schema::hasTable('league_registrations')
            && Schema::hasColumn('league_registrations', 'group_id')
            && Schema::hasColumn('league_registrations', 'group_card_id');

        $groups = $groupsQuery
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        if ($playerSchemaReady) {
            foreach ($groups as $group) {
                $countQuery = LeagueRegistration::query()
                    ->where('league_id', $league->id)
                    ->where('group_id', $group->id)
                    ->where(function ($qq) use ($groupCard) {
                        $qq->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
                    });

                $group->roster_count = LeagueRegistrationRoster::countSlots($countQuery);
            }
        }

        $activeGroupId = (int) $request->query('group', 0);
        if ($activeGroupId === 0) {
            $first = $groups->items()[0] ?? null;
            $activeGroupId = $first ? (int) $first->id : 0;
        }

        $activeGroup = null;
        if ($playerSchemaReady && $activeGroupId > 0) {
            $activeGroup = Group::query()
                ->whereKey($activeGroupId)
                ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
                ->when(
                    Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                    fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                        $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                    })
                )
                ->with([
                    'leagueRegistrations' => fn ($q) => $q
                        ->where('league_id', $league->id)
                        ->where(function ($qq) use ($groupCard) {
                            $qq->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
                        })
                        ->with('user')
                        ->latest('id'),
                ])
                ->first();

            if ($activeGroup) {
                $activeGroup->roster_count = LeagueRegistrationRoster::countSlots(
                    LeagueRegistration::query()
                        ->where('league_id', $league->id)
                        ->where('group_id', $activeGroup->id)
                        ->where(function ($qq) use ($groupCard) {
                            $qq->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
                        })
                );
            }
        }

        $activeGroupRoster = collect();

        $allGroupsQuery = Group::query()
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            );

        $allGroups = $allGroupsQuery->orderBy('name')->get();

        if ($activeGroup) {
            $activeGroupRoster = LeagueRegistrationRoster::collapseForDisplay(
                $activeGroup->leagueRegistrations
            );
        }

        $unassignedRegistrations = collect();
        $unassignedRoster = collect();
        if ($playerSchemaReady) {
            $unassignedQuery = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->whereNull('group_id')
                ->where(function ($q) use ($groupCard) {
                    $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
                })
                ->with('user')
                ->latest('id');

            if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
                $unassignedQuery->where('age_group_key', $ageGroupKey);
            }

            $unassignedRegistrations = $unassignedQuery->get();
            $unassignedRoster = LeagueRegistrationRoster::collapseForDisplay($unassignedRegistrations);
        }

        $otherGroupCards = $league->groupCards()
            ->where('group_cards.id', '!=', $groupCard->id)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['group_cards.id', 'group_cards.name', 'group_cards.tag']);

        $isDoublesGroupCard = LeagueRegistrationRoster::isDoublesSubGroup($groupCard);
        $partnerOptionsByRegId = [];
        $currentPartnerRegIdByRegId = [];

        if ($isDoublesGroupCard && $playerSchemaReady) {
            $allGroupCardRegistrations = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where(function ($query) use ($groupCard) {
                    $query->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
                })
                ->when(
                    $ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key'),
                    fn ($query) => $query->where('age_group_key', $ageGroupKey)
                )
                ->with('user')
                ->get();

            $activePool = $activeGroup
                ? $activeGroup->leagueRegistrations
                : collect();

            [$partnerOptionsByRegId, $currentPartnerRegIdByRegId] = $this->partnerFieldMaps(
                $activeGroupRoster->merge($unassignedRoster),
                $allGroupCardRegistrations,
                $activePool,
            );
        }

        return view('admin.league-management.groups.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'otherGroupCards' => $otherGroupCards,
            'ageGroupKey' => $ageGroupKey,
            'groupSearch' => $groupSearch,
            'activeGroupId' => $activeGroupId,
            'activeGroup' => $activeGroup,
            'activeGroupRoster' => $activeGroupRoster,
            'groups' => $groups,
            'allGroups' => $allGroups,
            'unassignedRegistrations' => $unassignedRegistrations,
            'unassignedRoster' => $unassignedRoster,
            'schemaReady' => Schema::hasTable('groups'),
            'playerSchemaReady' => $playerSchemaReady,
            'isDoublesGroupCard' => $isDoublesGroupCard,
            'partnerOptionsByRegId' => $partnerOptionsByRegId,
            'currentPartnerRegIdByRegId' => $currentPartnerRegIdByRegId,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $rosterEntries
     * @param  \Illuminate\Support\Collection<int, LeagueRegistration>  $allGroupCardRegistrations
     * @param  \Illuminate\Support\Collection<int, LeagueRegistration>  $activeSubgroupPool
     * @return array{array<int, list<array{registration_id: int, user_id: int, label: string}>>, array<int, int|null>}
     */
    private function partnerFieldMaps(
        \Illuminate\Support\Collection $rosterEntries,
        \Illuminate\Support\Collection $allGroupCardRegistrations,
        \Illuminate\Support\Collection $activeSubgroupPool,
    ): array {
        $options = [];
        $current = [];

        foreach ($rosterEntries as $entry) {
            /** @var LeagueRegistration $registration */
            $registration = $entry['registration'];
            $pool = ($registration->group_id ?? null) !== null
                ? $activeSubgroupPool
                : $allGroupCardRegistrations;

            $options[(int) $registration->id] = LeagueRegistrationRoster::partnerOptionsFor(
                $registration,
                $pool,
            )->all();
            $current[(int) $registration->id] = LeagueRegistrationRoster::partnerRegistrationIdFor($registration);
        }

        return [$options, $current];
    }

    public function create(Request $request, League $league, GroupCard $groupCard): View
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        return view('admin.league-management.groups.create', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'group' => new Group(['status' => 'active']),
            'schemaReady' => Schema::hasColumn('groups', 'group_card_id') && Schema::hasColumn('groups', 'age_group_key'),
        ]);
    }

    public function store(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'deactive'])],
            'age_group_key' => ['nullable', 'string', 'max:32'],
        ]);

        if (Schema::hasColumn('groups', 'group_card_id')) {
            $validated['group_card_id'] = $groupCard->id;
        }
        if (Schema::hasColumn('groups', 'age_group_key')) {
            $validated['age_group_key'] = $validated['age_group_key'] ?? null;
        } else {
            unset($validated['age_group_key']);
        }

        $group = Group::create($validated);

        // Ensure pivot relation exists as canonical mapping.
        try {
            $group->groupCards()->syncWithoutDetaching([$groupCard->id]);
        } catch (\Throwable $e) {
            // If pivot table doesn't exist, fallback is groups.group_card_id (handled above when column exists).
        }

        return redirect()
            ->route('admin.league-management.groups.index', [$league, $groupCard, 'age_group_key' => $request->input('age_group_key')])
            ->with('status', 'Subgroup created successfully.');
    }
}
