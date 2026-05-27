<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\User;
use App\Support\LeagueRegistrationRoster;
use App\Support\PlayerTodayMatchLookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeagueGroupCardAssignPlayerController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View
    {
        $this->ensureGroupCardBelongsToLeague($league, $groupCard);

        $registrationType = $this->registrationTypeForGroupCard($groupCard);

        $registeredInSubGroupUserIds = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $playersQuery = User::query()
            ->where('role', UserRole::Player)
            ->where('registration_type', $registrationType);

        $excludeUserIds = $registeredInSubGroupUserIds;

        $excludeUserIds = array_values(array_unique(array_merge(
            $excludeUserIds,
            LeagueRegistrationRoster::userIdsInLeagueSubGroupsForType($league->id, $registrationType, $groupCard->id),
        )));

        if ($excludeUserIds !== []) {
            $playersQuery->whereNotIn('id', $excludeUserIds);
        }

        $players = $playersQuery
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $playerLeagueNames = LeagueRegistration::query()
            ->whereIn('user_id', $players->getCollection()->pluck('id'))
            ->with('league:id,name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($regs) => $regs
                ->map(fn ($reg) => (string) ($reg->league?->name ?? ''))
                ->filter()
                ->unique()
                ->values()
                ->all());

        return view('admin.league-management.assign-players.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'registrationType' => $registrationType,
            'players' => $players,
            'todayMatchLeagues' => PlayerTodayMatchLookup::leagueNamesByUserId(),
            'playerLeagueNames' => $playerLeagueNames,
            'ageBrackets' => $this->ageBrackets(),
            'schemaReady' => Schema::hasTable('league_registrations'),
        ]);
    }

    public function store(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        $this->ensureGroupCardBelongsToLeague($league, $groupCard);

        $registrationType = $this->registrationTypeForGroupCard($groupCard);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'age_group_key' => ['required', 'string', 'max:32'],
        ]);

        $player = User::query()->findOrFail((int) $validated['user_id']);
        abort_unless($player->role === UserRole::Player, 404);
        abort_unless((string) ($player->registration_type ?? 'singles') === $registrationType, 422);

        $alreadyInSubGroup = LeagueRegistration::query()
            ->where('user_id', $player->id)
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->exists();

        if ($alreadyInSubGroup) {
            return back()
                ->withErrors(['user_id' => 'This player is already assigned to this group.'])
                ->withInput();
        }

        if (LeagueRegistrationRoster::isInAnotherLeagueSubGroupForType(
            $player->id,
            $league->id,
            $groupCard->id,
            $registrationType,
        )) {
            $formatLabel = $registrationType === 'doubles' ? 'doubles' : 'singles';

            return back()
                ->withErrors(['user_id' => "This player is already in another {$formatLabel} group for this league."])
                ->withInput();
        }

        $groupId = $this->pickGroupId($league, $groupCard, (string) $validated['age_group_key'], $registrationType);

        LeagueRegistration::create([
            'user_id' => $player->id,
            'league_id' => $league->id,
            'group_card_id' => $groupCard->id,
            'group_id' => $groupId,
            'skill_level' => (string) ($groupCard->skill_level_match ?? ''),
            'age_group_key' => (string) $validated['age_group_key'],
            'registration_type' => $registrationType,
            'payment_status' => 'admin',
        ]);

        return redirect()
            ->route('admin.league-management.assign-players.index', [$league, $groupCard])
            ->with('status', $player->name.' added to '.$groupCard->name.' successfully.');
    }

    protected function ensureGroupCardBelongsToLeague(League $league, GroupCard $groupCard): void
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
    }

    protected function registrationTypeForGroupCard(GroupCard $groupCard): string
    {
        $tag = strtolower((string) ($groupCard->tag ?? 'singles'));

        return in_array($tag, ['double', 'doubles'], true) ? 'doubles' : 'singles';
    }

    protected function pickGroupId(League $league, GroupCard $groupCard, string $ageGroupKey, string $registrationType): ?int
    {
        if (! Schema::hasTable('groups')) {
            return null;
        }

        $groupsQuery = Group::query()
            ->where('status', 'active')
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id));

        if (Schema::hasColumn('groups', 'age_group_key')) {
            $groupsQuery->where(function ($q) use ($ageGroupKey) {
                $q->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
            });
        }

        $candidateGroups = $groupsQuery->orderBy('id')->get();
        if ($candidateGroups->isEmpty()) {
            return null;
        }

        $bestGroup = null;
        $bestCount = null;

        foreach ($candidateGroups as $candidate) {
            $countQuery = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->where('group_id', $candidate->id)
                ->where('registration_type', $registrationType);

            $currentCount = $registrationType === 'doubles'
                ? LeagueRegistrationRoster::countSlots($countQuery)
                : $countQuery->count();

            if ($bestCount === null || $currentCount < $bestCount) {
                $bestCount = $currentCount;
                $bestGroup = $candidate;
            }
        }

        return $bestGroup?->id;
    }

    /**
     * @return Collection<string, string>
     */
    protected function ageBrackets(): Collection
    {
        return collect([
            'under-18' => 'Under 18',
            '18-21' => '18–21',
            '21-25' => '21–25',
            '26-30' => '26–30',
            '31-35' => '31–35',
            '36-40' => '36–40',
            '41-45' => '41–45',
            '46-50' => '46–50',
            'above-50' => 'Above 50',
        ]);
    }
}
