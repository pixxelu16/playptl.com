<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Division (group card) match schedule window for a tournament — shared by all subgroups A/B/C.
 */
final class DivisionScheduleWindow
{
    public static function tournamentDatesConfigured(League $league): bool
    {
        return $league->start_date !== null && $league->end_date !== null;
    }

    public static function startDate(League $league, GroupCard $groupCard): ?Carbon
    {
        $raw = self::pivotValue($league, $groupCard, 'start_date');

        return $raw !== null && $raw !== '' ? Carbon::parse($raw)->startOfDay() : null;
    }

    public static function endDate(League $league, GroupCard $groupCard): ?Carbon
    {
        $raw = self::pivotValue($league, $groupCard, 'end_date');

        return $raw !== null && $raw !== '' ? Carbon::parse($raw)->startOfDay() : null;
    }

    /**
     * @return string|null Validation error for start_date / end_date fields.
     */
    public static function validateDivisionDatesAgainstTournament(
        League $league,
        ?string $startDate,
        ?string $endDate,
    ): ?string {
        if (! self::tournamentDatesConfigured($league)) {
            return 'Set the tournament start and end dates on Edit Tournament before scheduling this group.';
        }

        $tournamentStart = $league->start_date->copy()->startOfDay();
        $tournamentEnd = $league->end_date->copy()->startOfDay();

        if ($endDate !== null && $startDate === null) {
            return 'Set a group start date before setting the end date.';
        }

        if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
            return 'Group end date must be on or after the group start date.';
        }

        if ($startDate !== null) {
            $groupStart = Carbon::parse($startDate)->startOfDay();
            if ($groupStart->lt($tournamentStart)) {
                return 'Group start date must be on or after the tournament start date ('.$tournamentStart->format('M j, Y').').';
            }
            if ($groupStart->gt($tournamentEnd)) {
                return 'Group start date must be on or before the tournament end date ('.$tournamentEnd->format('M j, Y').').';
            }
        }

        if ($endDate !== null) {
            $groupEnd = Carbon::parse($endDate)->startOfDay();
            if ($groupEnd->gt($tournamentEnd)) {
                return 'Group end date cannot be later than the tournament end date ('.$tournamentEnd->format('M j, Y').'). Extend the tournament dates on Edit Tournament first.';
            }
            if ($groupEnd->lt($tournamentStart)) {
                return 'Group end date must be on or after the tournament start date ('.$tournamentStart->format('M j, Y').').';
            }
        }

        return null;
    }

    /**
     * Group end date must be on or after every scheduled match in this division.
     *
     * @return string|null Validation error for end_date field.
     */
    public static function latestScheduledMatchDate(League $league, GroupCard $groupCard): ?Carbon
    {
        if (! Schema::hasTable('group_matches')) {
            return null;
        }

        $latest = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->max('match_date');

        return $latest !== null ? Carbon::parse($latest)->startOfDay() : null;
    }

    public static function earliestCompletedMatchDate(League $league, GroupCard $groupCard): ?Carbon
    {
        if (! Schema::hasTable('group_matches')) {
            return null;
        }

        $earliest = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->whereNotNull('match_date')
            ->whereNotNull('winner_side')
            ->min('match_date');

        return $earliest !== null ? Carbon::parse($earliest)->startOfDay() : null;
    }

    public static function validateStartDateAgainstCompletedMatches(
        League $league,
        GroupCard $groupCard,
        string $startDate,
    ): ?string {
        $earliestCompleted = self::earliestCompletedMatchDate($league, $groupCard);

        if ($earliestCompleted === null) {
            return null;
        }

        $proposedStart = Carbon::parse($startDate)->startOfDay();

        if ($proposedStart->gt($earliestCompleted)) {
            return 'Group start date cannot be after the earliest completed match ('.$earliestCompleted->format('M j, Y').').';
        }

        return null;
    }

    public static function validateEndDateAgainstScheduledMatches(
        League $league,
        GroupCard $groupCard,
        ?string $endDate,
    ): ?string {
        if (! Schema::hasTable('group_matches')) {
            return null;
        }

        $latestMatchDay = self::latestScheduledMatchDate($league, $groupCard);

        if ($latestMatchDay === null) {
            return null;
        }

        if ($endDate === null || $endDate === '') {
            return null;
        }

        $exceedsTournament = TournamentDateWindowConflict::groupMatchesExceedTournamentEnd($league, $groupCard);
        if ($exceedsTournament !== null) {
            return $exceedsTournament;
        }

        $proposedEnd = Carbon::parse($endDate)->startOfDay();

        if ($proposedEnd->lt($latestMatchDay)) {
            return 'Group end date cannot be before the latest scheduled match in this group ('.$latestMatchDay->format('M j, Y').'). Choose that date or later.';
        }

        return null;
    }

    public static function hasDivisionMatchesStarted(League $league, GroupCard $groupCard): bool
    {
        if (! Schema::hasTable('group_matches')) {
            return false;
        }

        return GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->exists();
    }

    public static function divisionMatchesClosed(League $league, GroupCard $groupCard): bool
    {
        $end = self::endDate($league, $groupCard);

        if ($end === null) {
            return false;
        }

        return now()->startOfDay()->gt($end);
    }

    public static function divisionHasScheduleDates(League $league, GroupCard $groupCard): bool
    {
        return self::startDate($league, $groupCard) !== null;
    }

    /**
     * When group matches are considered closed for playoff scheduling (pivot end date, or latest match if unset).
     */
    public static function groupCloseDateForPlayoffs(League $league, GroupCard $groupCard): ?Carbon
    {
        $groupEnd = self::endDate($league, $groupCard);
        $latestMatch = self::latestScheduledMatchDate($league, $groupCard);

        if ($groupEnd !== null && $latestMatch !== null) {
            return $groupEnd->gte($latestMatch) ? $groupEnd->copy()->startOfDay() : $latestMatch->copy()->startOfDay();
        }

        return ($groupEnd ?? $latestMatch)?->copy()->startOfDay();
    }

    /** First calendar day playoffs may start — the day after group matches close. */
    public static function earliestPlayoffStartDate(League $league, GroupCard $groupCard): ?Carbon
    {
        $groupClose = self::groupCloseDateForPlayoffs($league, $groupCard);

        return $groupClose?->copy()->addDay()->startOfDay();
    }

    public static function updateDivisionDates(
        League $league,
        GroupCard $groupCard,
        ?string $startDate,
        ?string $endDate,
    ): void {
        DB::table('group_card_league')
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->update([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

        $league->unsetRelation('groupCards');
        $groupCard->unsetRelation('leagues');
    }

    /**
     * @return string|null Error message when invalid; null when OK.
     */
    public static function validateMatchDate(Carbon $newDate, League $league, GroupCard $groupCard): ?string
    {
        $check = $newDate->copy()->startOfDay();
        $divisionStart = self::startDate($league, $groupCard);
        $divisionEnd = self::endDate($league, $groupCard);

        if ($check->isSunday()) {
            return 'Matches cannot be scheduled on Sunday. Choose Monday through Saturday.';
        }

        if ($league->start_date !== null && $check->lt($league->start_date->copy()->startOfDay())) {
            return 'Match date cannot be before the tournament start date ('.$league->start_date->format('M j, Y').').';
        }

        if ($league->end_date !== null && $check->gt($league->end_date->copy()->startOfDay())) {
            return 'Match date cannot be after the tournament end date ('.$league->end_date->format('M j, Y').').';
        }

        if ($divisionStart !== null && $check->lt($divisionStart)) {
            return 'Match date cannot be before the group start date ('.$divisionStart->format('M j, Y').').';
        }

        if ($divisionEnd !== null && $check->gt($divisionEnd)) {
            return 'Match date cannot be after the group end date ('.$divisionEnd->format('M j, Y').'). Extend the end date above if needed.';
        }

        if ($divisionStart === null) {
            return 'Set the group start date and schedule matches before adding games.';
        }

        return null;
    }

    private static function pivotValue(League $league, GroupCard $groupCard, string $column): mixed
    {
        if (! Schema::hasTable('group_card_league') || ! Schema::hasColumn('group_card_league', $column)) {
            return null;
        }

        $row = DB::table('group_card_league')
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->value($column);

        return $row;
    }
}
