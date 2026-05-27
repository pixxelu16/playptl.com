<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Support\LeagueRegistrationRoster;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminPlayerLeagueRegistrationController extends Controller
{
    public function create(Request $request, User $player): View
    {
        // Route model is `User` so keep it safe.
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $leagues = League::query()
            ->select(['id', 'name', 'stats', 'start_date', 'end_date'])
            ->orderByDesc('id')
            ->get();

        return view('admin.players.league-registrations.create', [
            'player' => $player,
            'leagues' => $leagues,
            'ageBrackets' => $this->ageBrackets(),
        ]);
    }

    public function store(Request $request, User $player): RedirectResponse
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $validated = $request->validate([
            'league_id' => ['required', 'integer', Rule::exists('leagues', 'id')],
            'skill_level' => ['required', 'string', 'max:32'],
            'age_group_key' => ['required', 'string', 'max:32'],
        ]);

        $league = League::query()->findOrFail((int) $validated['league_id']);

        $registrationType = (string) ($player->registration_type ?? 'singles');

        $tagCandidates = $registrationType === 'doubles'
            ? ['double', 'doubles']
            : ['single', 'singles'];

        $groupCard = $league->groupCards()
            ->where('group_cards.status', 'active')
            ->whereIn('group_cards.tag', $tagCandidates)
            ->where('group_cards.skill_level_match', (string) $validated['skill_level'])
            ->first();

        if (! $groupCard instanceof GroupCard) {
            return back()
                ->withErrors([
                    'skill_level' => 'No matching group found for this league + skill level. Please ensure a group is assigned to the league with the same Skill Level Match.',
                ])
                ->withInput();
        }

        $alreadyInSubGroup = LeagueRegistration::query()
            ->where('user_id', $player->id)
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->exists();
        if ($alreadyInSubGroup) {
            return back()
                ->withErrors([
                    'league_id' => 'This player is already registered in this league group.',
                ])
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
                ->withErrors([
                    'league_id' => "This player is already in another {$formatLabel} group for this league.",
                ])
                ->withInput();
        }

        $groupId = null;
        if (Schema::hasTable('groups')) {
            $groupsQuery = Group::query()
                ->where('status', 'active')
                ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id));

            if (Schema::hasColumn('groups', 'age_group_key')) {
                $ageGroup = (string) $validated['age_group_key'];
                $groupsQuery->where(function ($q) use ($ageGroup) {
                    $q->whereNull('age_group_key')->orWhere('age_group_key', $ageGroup);
                });
            }

            $candidateGroups = $groupsQuery->orderBy('id')->get();
            if ($candidateGroups->isNotEmpty()) {
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

                if ($bestGroup) {
                    $groupId = $bestGroup->id;
                }
            }
        }

        LeagueRegistration::create([
            'user_id' => $player->id,
            'league_id' => $league->id,
            'group_card_id' => $groupCard->id,
            'group_id' => $groupId,
            'skill_level' => (string) $validated['skill_level'],
            'age_group_key' => (string) $validated['age_group_key'],
            'registration_type' => $registrationType,
            'payment_status' => 'admin',
        ]);

        return redirect()
            ->route('admin.players.index', ['tab' => $registrationType])
            ->with('status', 'Player added to league successfully.');
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

