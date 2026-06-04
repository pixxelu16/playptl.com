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
            'skill_level' => ['nullable', 'string', 'max:32'],
            'skill_level_2' => ['nullable', 'string', 'max:32'],
        ]);

        $league = League::query()->findOrFail((int) $validated['league_id']);
        $tab = (string) $validated['tab'];
        $skill = isset($validated['skill_level']) ? trim((string) $validated['skill_level']) : '';
        $skillTwo = isset($validated['skill_level_2']) ? trim((string) $validated['skill_level_2']) : '';

        $groups = TournamentRegistrationOptions::groupCardsFor($league, $tab);
        $assignedGroup = null;
        $averageSkill = null;

        if ($tab === 'singles' && $skill !== '') {
            $assignedGroup = TournamentRegistrationOptions::assignedGroupForSkill($league, $tab, $skill);
        } elseif ($tab === 'doubles' && $skill !== '' && $skillTwo !== '') {
            $averageSkill = TournamentRegistrationOptions::averageSkillLevels($skill, $skillTwo);
            if ($averageSkill !== null) {
                $assignedGroup = TournamentRegistrationOptions::assignedGroupForSkill($league, $tab, $averageSkill);
            }
        }

        return response()->json([
            'groups' => $groups,
            'assigned_group' => $assignedGroup,
            'average_skill' => $averageSkill,
            'tab' => $tab,
            'format_label' => $tab === 'singles' ? 'Singles' : 'Doubles',
        ]);
    }
}
