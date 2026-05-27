<?php

namespace App\Support;

use App\Models\League;
use Illuminate\Support\Carbon;

final class LeaguePlayoffCalendar
{
    public static function validatePlayoffStartDate(Carbon $playoffStart, League $league): ?string
    {
        if ($league->end_date === null) {
            return 'Set the league match close date on the Matches page before scheduling playoffs.';
        }

        $leagueEnd = $league->end_date->copy()->startOfDay();
        $start = $playoffStart->copy()->startOfDay();

        if ($start->lte($leagueEnd)) {
            return 'Playoff start date must be after the league match close date ('.$league->end_date->format('M j, Y').').';
        }

        return null;
    }

    public static function validatePlayoffEndDate(Carbon $playoffStart, Carbon $playoffEnd, League $league): ?string
    {
        if ($playoffEnd->copy()->startOfDay()->lt($playoffStart->copy()->startOfDay())) {
            return 'Playoff end date cannot be before the playoff start date.';
        }

        if ($league->end_date === null) {
            return 'Set the league match close date on the Matches page before scheduling playoffs.';
        }

        if ($playoffEnd->copy()->startOfDay()->lte($league->end_date->copy()->startOfDay())) {
            return 'Playoff end date must be after the league match close date ('.$league->end_date->format('M j, Y').').';
        }

        return null;
    }

    public static function validatePlayoffMatchDate(?Carbon $matchDate, League $league): ?string
    {
        if ($matchDate === null) {
            return null;
        }

        if ($league->playoff_start_date === null || $league->playoff_end_date === null) {
            return 'Set playoff start and end dates on the Playoffs page before scheduling match dates.';
        }

        $day = $matchDate->copy()->startOfDay();
        $windowStart = $league->playoff_start_date->copy()->startOfDay();
        $windowEnd = $league->playoff_end_date->copy()->startOfDay();

        if ($day->lt($windowStart) || $day->gt($windowEnd)) {
            return 'Match date must be between '.$league->playoff_start_date->format('M j, Y')
                .' and '.$league->playoff_end_date->format('M j, Y').'.';
        }

        return null;
    }

    public static function playoffDatesConfigured(League $league): bool
    {
        return $league->playoff_start_date !== null && $league->playoff_end_date !== null;
    }

    public static function playoffDatesAreValid(League $league): bool
    {
        if (! self::playoffDatesConfigured($league)) {
            return false;
        }

        return self::validatePlayoffStartDate($league->playoff_start_date->copy()->startOfDay(), $league) === null
            && self::validatePlayoffEndDate(
                $league->playoff_start_date->copy()->startOfDay(),
                $league->playoff_end_date->copy()->startOfDay(),
                $league,
            ) === null;
    }
}
