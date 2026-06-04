<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\League;
use Illuminate\Support\Carbon;

final class LeaguePlayoffCalendar
{
    public static function validatePlayoffStartDate(Carbon $playoffStart, League $league, GroupCard $groupCard): ?string
    {
        if (! DivisionScheduleWindow::tournamentDatesConfigured($league)) {
            return 'Set the tournament start and end dates on Edit Tournament before scheduling playoffs.';
        }

        $groupClose = DivisionScheduleWindow::endDate($league, $groupCard);
        if ($groupClose === null) {
            return 'Set the group end date on the Matches page after scheduling group matches.';
        }

        $tournamentStart = $league->start_date->copy()->startOfDay();
        $tournamentEnd = $league->end_date->copy()->startOfDay();
        $start = $playoffStart->copy()->startOfDay();

        if ($start->lte($groupClose)) {
            return 'Playoff start must be after group matches close ('.$groupClose->format('M j, Y').').';
        }

        if ($start->lt($tournamentStart)) {
            return 'Playoff start must be on or after the tournament start date ('.$tournamentStart->format('M j, Y').').';
        }

        if ($start->gt($tournamentEnd)) {
            return 'Playoff start must be on or before the tournament end date ('.$tournamentEnd->format('M j, Y').').';
        }

        return null;
    }

    public static function validatePlayoffEndDate(Carbon $playoffStart, Carbon $playoffEnd, League $league, GroupCard $groupCard): ?string
    {
        if ($playoffEnd->copy()->startOfDay()->lt($playoffStart->copy()->startOfDay())) {
            return 'Playoff end date cannot be before the playoff start date.';
        }

        if (! DivisionScheduleWindow::tournamentDatesConfigured($league)) {
            return 'Set the tournament start and end dates on Edit Tournament before scheduling playoffs.';
        }

        $tournamentStart = $league->start_date->copy()->startOfDay();
        $tournamentEnd = $league->end_date->copy()->startOfDay();
        $end = $playoffEnd->copy()->startOfDay();

        if ($end->lt($tournamentStart)) {
            return 'Playoff end must be on or after the tournament start date ('.$tournamentStart->format('M j, Y').').';
        }

        if ($end->gt($tournamentEnd)) {
            return 'Playoff end must be on or before the tournament end date ('.$tournamentEnd->format('M j, Y').').';
        }

        $startError = self::validatePlayoffStartDate($playoffStart, $league, $groupCard);
        if ($startError !== null) {
            return $startError;
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

    public static function playoffDatesAreValid(League $league, GroupCard $groupCard): bool
    {
        if (! self::playoffDatesConfigured($league)) {
            return false;
        }

        return self::validatePlayoffStartDate($league->playoff_start_date->copy()->startOfDay(), $league, $groupCard) === null
            && self::validatePlayoffEndDate(
                $league->playoff_start_date->copy()->startOfDay(),
                $league->playoff_end_date->copy()->startOfDay(),
                $league,
                $groupCard,
            ) === null;
    }
}
