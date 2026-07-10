<?php

namespace App\Support;

use App\Models\GroupMatch;
use App\Models\League;
use App\Models\PlayoffMatch;
use App\Models\PlayoffQualifier;
use Illuminate\Support\Facades\Schema;

/**
 * When qualifier paths or a playoff bracket exist for a division, group-stage scheduling is locked.
 */
final class DivisionPlayoffPhase
{
    public static function locksGroupMatchScheduling(int $leagueId, int $groupCardId, ?string $ageGroupKey): bool
    {
        self::reconcileDivisionAfterGroupMatchesChanged($leagueId, $groupCardId);

        $league = League::query()->find($leagueId);
        if ($league instanceof League && LeagueSeasonPhase::locksGroupMatchScheduling($league)) {
            return true;
        }

        return self::divisionLocksGroupMatchScheduling($leagueId, $groupCardId, $ageGroupKey);
    }

    /**
     * When all group-stage matches for a division are gone, drop stale playoff/qualifier rows and league playoff flags.
     */
    public static function reconcileDivisionAfterGroupMatchesChanged(int $leagueId, int $groupCardId): void
    {
        if (self::divisionHasGroupMatches($leagueId, $groupCardId)) {
            return;
        }

        PlayoffBracketBuilder::clearAllDivisionPlayoffData($leagueId, $groupCardId);

        $league = League::query()->find($leagueId);
        if ($league instanceof League) {
            LeagueSeasonPhase::resetLeaguePlayoffFlagsIfNoMatches($league);
        }
    }

    public static function divisionHasGroupMatches(int $leagueId, int $groupCardId): bool
    {
        if (! Schema::hasTable('group_matches')) {
            return false;
        }

        return GroupMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->exists();
    }

    public static function divisionLocksGroupMatchScheduling(int $leagueId, int $groupCardId, ?string $ageGroupKey): bool
    {
        if (! self::divisionHasGroupMatches($leagueId, $groupCardId)) {
            return false;
        }

        $ageKeyDb = $ageGroupKey ?? '';

        if (self::hasSavedQualifierPaths($leagueId, $groupCardId, $ageKeyDb)) {
            return true;
        }

        if (! Schema::hasTable('playoff_matches')) {
            return false;
        }

        return PlayoffMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->exists();
    }

    public static function hasSavedQualifierPaths(int $leagueId, int $groupCardId, string $ageKeyDb): bool
    {
        if (! Schema::hasTable('playoff_qualifiers')) {
            return false;
        }

        return PlayoffQualifier::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->whereNotNull('path')
            ->where('path', '!=', '')
            ->exists();
    }

    public static function lockMessage(int $leagueId, int $groupCardId, ?string $ageGroupKey): string
    {
        $league = League::query()->find($leagueId);
        if ($league instanceof League) {
            $leagueMsg = LeagueSeasonPhase::groupSchedulingLockMessage($league);
            if ($leagueMsg !== null) {
                return $leagueMsg;
            }
        }

        return 'Qualifier paths or the playoff bracket are set for this group. Group-stage match scheduling is closed — use Playoffs to manage knockout matches. You can still record results on matches already scheduled.';
    }
}
