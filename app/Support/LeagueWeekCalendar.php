<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use Illuminate\Support\Carbon;

/**
 * League play weeks: matches spread Mon–Sat. Dates may be moved until league match close date.
 */
final class LeagueWeekCalendar
{
    public static function roundRobinPlayWeekCount(int $participantCount): int
    {
        if ($participantCount < 2) {
            return 0;
        }

        $slots = $participantCount;
        if ($slots % 2 === 1) {
            $slots++;
        }

        return $slots - 1;
    }

    /**
     * @return array{playWeekSundays: list<string>}
     */
    public static function calendar(Carbon $leagueStart, int $playWeekCount): array
    {
        $firstSunday = self::firstPlayWeekDeadlineSunday($leagueStart);

        $playWeekSundays = [];
        for ($r = 0; $r < $playWeekCount; $r++) {
            $playWeekSundays[] = $firstSunday->copy()->addWeeks($r)->format('Y-m-d');
        }

        return [
            'playWeekSundays' => $playWeekSundays,
        ];
    }

    public static function weekEndSundayForRound(Carbon $leagueStart, int $roundNumber): Carbon
    {
        $firstSunday = self::firstPlayWeekDeadlineSunday($leagueStart);

        return $firstSunday->copy()->addWeeks(max(0, $roundNumber - 1))->startOfDay();
    }

    public static function isPlayDateInWeek(
        Carbon $date,
        Carbon $weekEndSunday,
        Carbon $leagueStart,
        bool $isFirstWeek,
    ): bool {
        $anchor = $leagueStart->copy()->startOfDay();
        $weekStartMonday = ($isFirstWeek && $anchor->isSunday())
            ? $anchor->copy()->addDay()
            : $weekEndSunday->copy()->subDays(6)->startOfDay();
        $check = $date->copy()->startOfDay();

        for ($offset = 0; $offset < 6; $offset++) {
            $day = $weekStartMonday->copy()->addDays($offset);
            if ($day->isSunday()) {
                continue;
            }
            if ($isFirstWeek && $day->lt($leagueStart->copy()->startOfDay())) {
                continue;
            }
            if ($day->isSameDay($check)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Spread pairing dates across Mon–Sat (same logic as auto-scheduler).
     *
     * @return list<string> Y-m-d
     */
    public static function spreadMatchDatesAcrossWeek(
        Carbon $weekEndSunday,
        Carbon $leagueStart,
        int $pairingCount,
        bool $isFirstRound,
    ): array {
        if ($pairingCount <= 0) {
            return [];
        }

        $playDays = [];
        $leagueStart = $leagueStart->copy()->startOfDay();

        if ($isFirstRound && $leagueStart->isSunday()) {
            $weekStartMonday = $leagueStart->copy()->addDay();
        } else {
            $weekStartMonday = $weekEndSunday->copy()->subDays(6)->startOfDay();
        }

        for ($offset = 0; $offset < 6; $offset++) {
            $day = $weekStartMonday->copy()->addDays($offset);
            if ($isFirstRound && $day->lt($leagueStart)) {
                continue;
            }
            if ($day->isSunday()) {
                continue;
            }
            $playDays[] = $day;
        }

        if ($playDays === []) {
            $playDays[] = self::firstPlayableDayOnOrAfter($leagueStart);
        }

        if ($pairingCount === 1) {
            $mid = $playDays[(int) floor((count($playDays) - 1) / 2)];

            return [$mid->format('Y-m-d')];
        }

        $dates = [];
        $lastIndex = count($playDays) - 1;

        for ($i = 0; $i < $pairingCount; $i++) {
            $slot = (int) round($i * $lastIndex / ($pairingCount - 1));
            $dates[] = $playDays[$slot]->format('Y-m-d');
        }

        return $dates;
    }

    public static function weekHeading(int $weekNumber): string
    {
        return 'Week '.$weekNumber;
    }

    /**
     * @return string|null Error message when invalid; null when OK.
     */
    public static function validateLeagueMatchDate(Carbon $newDate, League $league, ?GroupCard $groupCard = null): ?string
    {
        if ($groupCard instanceof GroupCard) {
            return DivisionScheduleWindow::validateMatchDate($newDate, $league, $groupCard);
        }

        $check = $newDate->copy()->startOfDay();

        if ($league->start_date !== null && $check->lt($league->start_date->copy()->startOfDay())) {
            return 'Match date cannot be before the tournament start date ('.$league->start_date->format('M j, Y').').';
        }

        if ($league->end_date !== null && $check->gt($league->end_date->copy()->startOfDay())) {
            return 'Match date cannot be after the tournament end date ('.$league->end_date->format('M j, Y').').';
        }

        return null;
    }

    public static function validatePendingMatchDate(
        Carbon $newDate,
        League $league,
        GroupMatch $match,
        int $playWeekCount,
        ?Carbon $today = null,
    ): ?string {
        if (! $match->isPending()) {
            return null;
        }

        $groupCard = GroupCard::query()->find($match->group_card_id);

        if ($groupCard instanceof GroupCard) {
            return DivisionScheduleWindow::validateMatchDate($newDate, $league, $groupCard);
        }

        return self::validateLeagueMatchDate($newDate, $league);
    }

    /**
     * Week deadline (Sunday). Play happens Mon–Sat before that deadline.
     * If the group starts on a Sunday, week 1 play is Mon–Sat after that Sunday (deadline the next Sunday).
     */
    public static function firstPlayWeekDeadlineSunday(Carbon $groupStart): Carbon
    {
        $anchor = $groupStart->copy()->startOfDay();
        $firstSunday = $anchor->isSunday() ? $anchor : $anchor->copy()->next(Carbon::SUNDAY);

        if ($anchor->isSunday() && $firstSunday->isSameDay($anchor)) {
            return $firstSunday->copy()->addWeek();
        }

        return $firstSunday;
    }

    /** Last Mon–Sat play day on or before the given date (Sunday moves to Saturday). */
    public static function lastPlayableDayOnOrBefore(Carbon $day): Carbon
    {
        $anchor = $day->copy()->startOfDay();

        while ($anchor->isSunday()) {
            $anchor = $anchor->copy()->subDay();
        }

        return $anchor;
    }

    /** First Mon–Sat play day on or after the group start anchor. */
    public static function firstPlayableDayOnOrAfter(Carbon $groupStart): Carbon
    {
        $anchor = $groupStart->copy()->startOfDay();

        if ($anchor->isSunday()) {
            return $anchor->copy()->addDay();
        }

        if ($anchor->isSaturday()) {
            return $anchor->copy()->addDays(2);
        }

        return $anchor;
    }

    private static function firstSundayOnOrAfter(Carbon $date): Carbon
    {
        $day = $date->copy()->startOfDay();

        return $day->isSunday() ? $day : $day->next(Carbon::SUNDAY);
    }
}
