<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Helpers\LeagueMenuHelper;
use App\Models\User;
use App\Support\AdminPlayerLeagueRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminPlayerLeagueRegistrationController extends Controller
{
    public function create(Request $request, User $player): View
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $leagues = LeagueMenuHelper::registrationLeagues(latestFirst: true);

        return view('admin.players.league-registrations.create', [
            'player' => $player,
            'leagues' => $leagues,
            'ageBrackets' => AdminPlayerLeagueRegistrationService::ageBrackets(),
        ]);
    }

    public function store(Request $request, User $player): RedirectResponse
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $validated = $request->validate([
            'league_id' => ['required', 'integer', Rule::exists('leagues', 'id')],
            'skill_level' => ['required', 'string', 'max:32', Rule::in(AdminPlayerLeagueRegistrationService::skillLevelValues())],
            'age_group_key' => ['required', 'string', 'max:32', Rule::in(AdminPlayerLeagueRegistrationService::ageBrackets()->keys()->all())],
        ]);

        $registrationType = AdminPlayerLeagueRegistrationService::registrationTabFor($player);

        AdminPlayerLeagueRegistrationService::createRegistration(
            $player,
            (int) $validated['league_id'],
            (string) $validated['skill_level'],
            (string) $validated['age_group_key'],
            $registrationType,
        );

        return redirect()
            ->route('admin.players.index', ['tab' => $registrationType])
            ->with('status', 'Player added to league successfully.');
    }
}
