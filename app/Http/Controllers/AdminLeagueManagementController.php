<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\League;
use App\Models\LeagueRegistration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminLeagueManagementController extends Controller
{
    /**
     * One place for admins to manage: League -> Group cards -> Age brackets -> Groups -> Players.
     */
    public function index(): View
    {
        return view('admin.league-management.index', [
            'leagues' => League::query()->latest('id')->paginate(10),
        ]);
    }

    public function show(League $league): View
    {
        $league->load(['groupCards' => fn ($q) => $q->orderBy('display_order')->orderBy('name')]);

        $cardStats = [];
        foreach ($league->groupCards as $card) {
            $groupsCount = Group::query()
                ->whereHas('groupCards', fn ($q) => $q->whereKey($card->id))
                ->count();

            $registrationsCount = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $card->id)
                ->count();

            $assignedCount = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $card->id)
                ->whereNotNull('group_id')
                ->count();

            $cardStats[$card->id] = [
                'groups_count' => $groupsCount,
                'registrations_count' => $registrationsCount,
                'assigned_count' => $assignedCount,
            ];
        }

        return view('admin.league-management.show', [
            'league' => $league,
            'ageBrackets' => $this->ageBrackets(),
            'tablesReady' => Schema::hasTable('league_registrations') && Schema::hasTable('groups'),
            'cardStats' => $cardStats,
        ]);
    }

    /**
     * Keep keys consistent with registration form.
     *
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

