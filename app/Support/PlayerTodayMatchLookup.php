<?php

namespace App\Support;

use App\Models\GroupMatch;
use App\Models\PlayoffMatch;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Maps player user ids to the league name of a match scheduled on a given date (all leagues).
 */
final class PlayerTodayMatchLookup
{
    /**
     * @return array<int, string> user_id => league name
     */
    public static function leagueNamesByUserId(?CarbonInterface $date = null): array
    {
        $dateString = ($date ?? now())->toDateString();
        $map = [];

        if (Schema::hasTable('group_matches')) {
            $matches = GroupMatch::query()
                ->whereDate('match_date', $dateString)
                ->with('league:id,name')
                ->get([
                    'id',
                    'league_id',
                    'home_user_id',
                    'away_user_id',
                    'home_partner_user_id',
                    'away_partner_user_id',
                ]);

            foreach ($matches as $match) {
                $leagueName = (string) ($match->league?->name ?? 'Unknown league');
                foreach ([
                    $match->home_user_id,
                    $match->away_user_id,
                    $match->home_partner_user_id,
                    $match->away_partner_user_id,
                ] as $userId) {
                    if ($userId) {
                        $map[(int) $userId] = $leagueName;
                    }
                }
            }
        }

        if (Schema::hasTable('playoff_matches')) {
            $playoffs = PlayoffMatch::query()
                ->whereDate('match_date', $dateString)
                ->with('league:id,name')
                ->get(['id', 'league_id', 'home_user_id', 'away_user_id']);

            foreach ($playoffs as $match) {
                $leagueName = (string) ($match->league?->name ?? 'Unknown league');
                foreach ([$match->home_user_id, $match->away_user_id] as $userId) {
                    if ($userId) {
                        $map[(int) $userId] = $leagueName;
                    }
                }
            }
        }

        return $map;
    }
}
