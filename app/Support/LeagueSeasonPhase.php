<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use Illuminate\Support\Facades\Schema;

/**
 * League group-stage vs playoff season windows (league-level dates and flags).
 */
final class LeagueSeasonPhase
{
    public static function hasGroupMatchesStarted(League $league): bool
    {
        if (! Schema::hasTable('group_matches')) {
            return false;
        }

        return GroupMatch::query()->where('league_id', $league->id)->exists();
    }

    /**
     * Qualifier paths and playoff bracket only after league matches have started and at least one result exists.
     */
    public static function showQualifierAndPlayoffs(League $league, int $groupCardId): bool
    {
        if (! self::hasGroupMatchesStarted($league)) {
            return false;
        }

        return self::divisionHasCompletedGroupMatches($league->id, $groupCardId);
    }

    public static function divisionHasCompletedGroupMatches(int $leagueId, int $groupCardId): bool
    {
        if (! Schema::hasTable('group_matches')) {
            return false;
        }

        return GroupMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->whereNotNull('winner_side')
            ->exists();
    }

    public static function qualifierPlayoffsUnavailableMessage(League $league): string
    {
        if (! self::hasGroupMatchesStarted($league)) {
            return 'Tournament matches have not started yet. Set the start date on the Matches page and schedule group matches first.';
        }

        return 'Playoff paths appear after at least one group match has a result. Enter scores on the Matches page, then return here.';
    }

    public static function groupMatchesClosed(League $league): bool
    {
        if ($league->end_date === null) {
            return false;
        }

        return now()->startOfDay()->gt($league->end_date->copy()->startOfDay());
    }

    public static function playoffsStarted(League $league): bool
    {
        return $league->playoffs_started_at !== null;
    }

    public static function playoffsClosed(League $league): bool
    {
        return $league->playoffs_closed_at !== null;
    }

    public static function canStartPlayoffs(League $league, GroupCard $groupCard): bool
    {
        return self::hasGroupMatchesStarted($league)
            && LeaguePlayoffCalendar::playoffDatesAreValid($league, $groupCard)
            && ! self::playoffsStarted($league)
            && ! self::playoffsClosed($league);
    }

    public static function canClosePlayoffs(League $league): bool
    {
        return self::playoffsStarted($league) && ! self::playoffsClosed($league);
    }

    /**
     * No new group-stage scheduling after match close date or once playoffs have started.
     */
    public static function locksGroupMatchScheduling(League $league): bool
    {
        if (self::groupMatchesClosed($league)) {
            return true;
        }

        if (self::playoffsStarted($league)) {
            return true;
        }

        return false;
    }

    public static function groupSchedulingLockMessage(League $league): ?string
    {
        if (self::playoffsStarted($league)) {
            return 'Playoffs have started for this tournament. Group-stage match scheduling is closed — use the Playoffs page.';
        }

        if (self::groupMatchesClosed($league) && $league->end_date) {
            return 'Tournament matches closed on '.$league->end_date->format('M j, Y').'. Group-stage scheduling is closed — you can still update results on existing matches.';
        }

        return null;
    }

    public static function playoffsLockMessage(League $league, GroupCard $groupCard): ?string
    {
        if (self::playoffsClosed($league)) {
            return 'Playoffs were closed on '.$league->playoffs_closed_at->format('M j, Y g:i A').'.';
        }

        if (! self::playoffsStarted($league)) {
            $groupClose = DivisionScheduleWindow::groupCloseDateForPlayoffs($league, $groupCard);
            $closeLabel = $groupClose?->format('M j, Y') ?? 'the group end date';
            $earliestStart = DivisionScheduleWindow::earliestPlayoffStartDate($league, $groupCard);
            $startLabel = $earliestStart?->format('M j, Y') ?? 'after group matches close';

            if (! LeaguePlayoffCalendar::playoffDatesConfigured($league)) {
                return 'Set playoff start and end dates below, then click Schedule matches (start on or after '.$startLabel.', once group matches close on '.$closeLabel.').';
            }

            return 'Pick playoff dates and click Schedule matches when Qualifier paths are ready.';
        }

        return null;
    }

    /** Clears playoffs_started_at / playoffs_closed_at when the league has no group matches left. */
    public static function resetLeaguePlayoffFlagsIfNoMatches(League $league): void
    {
        if (self::hasGroupMatchesStarted($league)) {
            return;
        }

        if ($league->playoffs_started_at === null && $league->playoffs_closed_at === null) {
            return;
        }

        $league->update([
            'playoffs_started_at' => null,
            'playoffs_closed_at' => null,
        ]);
    }
}
