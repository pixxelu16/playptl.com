<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Support\LeagueRegistrationRoster;
use App\Support\SubgroupRoundRobinScheduler;
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

        $allRegistrations = $registrationsQuery->get();
        $rosterEntries = LeagueRegistrationRoster::collapseForDisplay($allRegistrations);
        $isDoublesGroupCard = LeagueRegistrationRoster::isDoublesSubGroup($groupCard);
        $partnerOptionsByRegId = [];
        $currentPartnerRegIdByRegId = [];

        if ($isDoublesGroupCard) {
            foreach ($rosterEntries as $entry) {
                /** @var LeagueRegistration $registration */
                $registration = $entry['registration'];
                $partnerOptionsByRegId[(int) $registration->id] = LeagueRegistrationRoster::partnerOptionsFor(
                    $registration,
                    $allRegistrations,
                )->all();
                $currentPartnerRegIdByRegId[(int) $registration->id] = LeagueRegistrationRoster::partnerRegistrationIdFor($registration);
            }
        }

        $perPage = 25;
        $page = max(1, (int) $request->query('page', 1));
        $rosterPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $rosterEntries->forPage($page, $perPage)->values(),
            $rosterEntries->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.league-management.players.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'rosterEntries' => $rosterPaginator,
            'groups' => $groups,
            'schemaReady' => Schema::hasTable('league_registrations')
                && Schema::hasColumn('league_registrations', 'group_id')
                && Schema::hasColumn('league_registrations', 'age_group_key'),
            'isDoublesGroupCard' => $isDoublesGroupCard,
            'partnerOptionsByRegId' => $partnerOptionsByRegId,
            'currentPartnerRegIdByRegId' => $currentPartnerRegIdByRegId,
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

        $groupId = isset($validated['group_id']) ? (int) $validated['group_id'] : null;

        LeagueRegistrationRoster::syncSubgroupForPartners($registration, $groupId);

        $scheduleNote = '';
        if ($groupId !== null) {
            $group = Group::query()->find($groupId);
            if ($group instanceof Group) {
                $ageKey = Schema::hasColumn('league_registrations', 'age_group_key')
                    ? ($registration->age_group_key ?: null)
                    : null;
                $result = SubgroupRoundRobinScheduler::sync($league, $groupCard, $group, $ageKey);
                if ($result['created'] > 0) {
                    $scheduleNote = sprintf(
                        ' %d match%s scheduled for this subgroup.',
                        $result['created'],
                        $result['created'] === 1 ? '' : 'es',
                    );
                }
            }
        }

        return back()->with('status', 'Player subgroup updated.'.$scheduleNote);
    }

    public function updatePartner(Request $request, League $league, GroupCard $groupCard, LeagueRegistration $registration): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless($registration->league_id === $league->id && $registration->group_card_id === $groupCard->id, 404);
        abort_unless(LeagueRegistrationRoster::isDoublesSubGroup($groupCard), 404);

        $partnerRegistrationId = $request->input('partner_registration_id');
        if ($partnerRegistrationId === null || $partnerRegistrationId === '') {
            LeagueRegistrationRoster::unlinkPartner($registration);

            return back()->with('status', 'Partner removed.');
        }

        $validated = $request->validate([
            'partner_registration_id' => ['required', 'integer', Rule::exists('league_registrations', 'id')],
        ]);

        $partnerRegistrationId = (int) $validated['partner_registration_id'];
        $partnerRegistration = LeagueRegistration::query()->findOrFail($partnerRegistrationId);
        abort_unless($partnerRegistration->league_id === $league->id && $partnerRegistration->group_card_id === $groupCard->id, 422);

        if (! LeagueRegistrationRoster::isAvailableAsPartner($partnerRegistration, (int) $registration->user_id)) {
            return back()->withErrors([
                'partner_registration_id' => 'That player already has a partner in this group.',
            ]);
        }

        $targetGroupId = $registration->group_id ?? $partnerRegistration->group_id;
        if ($targetGroupId !== null) {
            if ($registration->group_id !== null && $partnerRegistration->group_id !== null
                && (int) $registration->group_id !== (int) $partnerRegistration->group_id) {
                return back()->withErrors([
                    'partner_registration_id' => 'Partner must be in the same subgroup or unassigned.',
                ]);
            }

            LeagueRegistrationRoster::updateGroupForEntry($registration, (int) $targetGroupId);
            LeagueRegistrationRoster::updateGroupForEntry($partnerRegistration, (int) $targetGroupId);
        }

        try {
            LeagueRegistrationRoster::linkPartners($registration, $partnerRegistration);
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors([
                'partner_registration_id' => $exception->getMessage(),
            ]);
        }

        if ($targetGroupId !== null) {
            $group = Group::query()->find($targetGroupId);
            if ($group instanceof Group) {
                $ageKey = Schema::hasColumn('league_registrations', 'age_group_key')
                    ? ($registration->age_group_key ?: null)
                    : null;
                SubgroupRoundRobinScheduler::sync($league, $groupCard, $group, $ageKey);
            }
        }

        return back()->with('status', 'Doubles partner updated.');
    }

    public function updateSubGroup(Request $request, League $league, GroupCard $groupCard, LeagueRegistration $registration): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless($registration->league_id === $league->id && $registration->group_card_id === $groupCard->id, 404);

        $validated = $request->validate([
            'target_group_card_id' => [
                'required',
                'integer',
                Rule::exists('group_cards', 'id'),
                Rule::notIn([$groupCard->id]),
            ],
        ]);

        $targetCard = GroupCard::query()->findOrFail((int) $validated['target_group_card_id']);
        abort_unless($league->groupCards()->whereKey($targetCard->id)->exists(), 422);

        $movingIds = LeagueRegistrationRoster::registrationIdsForEntry($registration);
        $movingRegs = LeagueRegistration::query()->whereIn('id', $movingIds)->get();

        foreach ($movingRegs as $reg) {
            $alreadyInTarget = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $targetCard->id)
                ->where('user_id', $reg->user_id)
                ->whereNotIn('id', $movingIds)
                ->exists();

            if ($alreadyInTarget) {
                $name = (string) ($reg->user?->name ?? 'Player');

                return back()->withErrors([
                    'target_group_card_id' => $name.' is already registered in '.$targetCard->name.'.',
                ]);
            }
        }

        LeagueRegistrationRoster::moveToSubGroup($registration, $targetCard, $league->id);

        $label = LeagueRegistrationRoster::collapseForDisplay($movingRegs)->first()['display_name'] ?? 'Player';

        return back()->with('status', $label.' moved to '.$targetCard->name.'. Assign a group there when ready.');
    }
}

