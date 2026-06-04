<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\League;
use App\Models\PlayoffMatch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Assign playoff match dates between playoff start and end; the final is always on the end date.
 */
final class PlayoffMatchScheduler
{
    /** @var list<string> */
    private const ROUND_ORDER = [
        PlayoffMatch::ROUND_PRE_PRE_Q,
        PlayoffMatch::ROUND_PRE_Q,
        PlayoffMatch::ROUND_QF,
        PlayoffMatch::ROUND_SF,
        PlayoffMatch::ROUND_F,
    ];

    /**
     * @return array{updated: int, rounds: int}
     */
    public static function syncDivision(
        League $league,
        GroupCard $groupCard,
        ?string $ageGroupKey = null,
        bool $reschedulePending = false,
    ): array {
        if (
            ! Schema::hasTable('playoff_matches')
            || $league->playoff_start_date === null
            || $league->playoff_end_date === null
        ) {
            return ['updated' => 0, 'rounds' => 0];
        }

        MatchScheduleMailQueue::beginBulkScheduling();

        $ageKeyDb = $ageGroupKey ?? '';

        $matches = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->orderByRaw("FIELD(round, 'ppq', 'pq', 'qf', 'sf', 'f')")
            ->orderBy('slot')
            ->get();

        if ($matches->isEmpty()) {
            return ['updated' => 0, 'rounds' => 0];
        }

        $windowStart = LeagueWeekCalendar::firstPlayableDayOnOrAfter(
            $league->playoff_start_date->copy()->startOfDay(),
        );
        $windowEnd = LeagueWeekCalendar::lastPlayableDayOnOrBefore(
            $league->playoff_end_date->copy()->startOfDay(),
        );

        if ($windowEnd->lt($windowStart)) {
            return ['updated' => 0, 'rounds' => 0];
        }

        $byRound = $matches->groupBy('round');

        /** @var list<array{round: string, matches: Collection<int, PlayoffMatch>}> $activeRounds */
        $activeRounds = [];
        foreach (self::ROUND_ORDER as $round) {
            $roundMatches = $byRound->get($round, collect());
            if ($roundMatches->isNotEmpty()) {
                $activeRounds[] = [
                    'round' => $round,
                    'matches' => $roundMatches->values(),
                ];
            }
        }

        $hasFinalRound = collect($activeRounds)->contains(
            fn (array $entry): bool => $entry['round'] === PlayoffMatch::ROUND_F,
        );

        $earlierWindowEnd = $hasFinalRound
            ? self::earlierRoundsWindowEnd($windowStart, $windowEnd)
            : $windowEnd;

        $nonFinalRounds = collect($activeRounds)
            ->filter(fn (array $entry): bool => $entry['round'] !== PlayoffMatch::ROUND_F)
            ->values();
        $nonFinalCount = $nonFinalRounds->count();

        $updated = 0;
        $roundsUsed = 0;

        foreach ($activeRounds as $roundIndex => $entry) {
            $round = $entry['round'];
            $roundMatches = $entry['matches'];
            $matchCount = $roundMatches->count();
            $roundsUsed++;

            $isFinalRound = $round === PlayoffMatch::ROUND_F;
            $nonFinalIndex = $nonFinalRounds->search(
                fn (array $item): bool => $item['round'] === $round,
            );

            $dates = $isFinalRound
                ? self::datesForFinalRound($matchCount, $windowStart, $windowEnd)
                : self::datesForEarlierRound(
                    $matchCount,
                    $windowStart,
                    $earlierWindowEnd,
                    is_int($nonFinalIndex) ? $nonFinalIndex : 0,
                    max(1, $nonFinalCount),
                    $roundIndex === 0,
                );

            foreach ($roundMatches as $pairIndex => $match) {
                if (! self::shouldAssignDate($match, $reschedulePending, $windowStart, $windowEnd)) {
                    continue;
                }

                $matchDate = $dates[$pairIndex] ?? $dates[array_key_last($dates)] ?? $windowEnd->format('Y-m-d');
                $oldDate = $match->match_date?->format('Y-m-d');
                if ($oldDate === $matchDate) {
                    continue;
                }

                $match->update(['match_date' => $matchDate]);
                $updated++;
                $match->refresh();
                PlayoffMatchScheduleNotifier::notifyParticipants($match);
            }
        }

        return ['updated' => $updated, 'rounds' => $roundsUsed];
    }

    public static function latestCompletedMatchDate(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): ?Carbon
    {
        if (! Schema::hasTable('playoff_matches')) {
            return null;
        }

        $raw = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageGroupKey ?? '')
            ->whereNotNull('match_date')
            ->whereNotNull('winner_side')
            ->max('match_date');

        return $raw !== null ? Carbon::parse($raw)->startOfDay() : null;
    }

    public static function earliestCompletedMatchDate(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): ?Carbon
    {
        if (! Schema::hasTable('playoff_matches')) {
            return null;
        }

        $earliest = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageGroupKey ?? '')
            ->whereNotNull('match_date')
            ->whereNotNull('winner_side')
            ->min('match_date');

        return $earliest !== null ? Carbon::parse($earliest)->startOfDay() : null;
    }

    public static function validateStartDateAgainstCompletedMatches(
        League $league,
        GroupCard $groupCard,
        string $startDateYmd,
        ?string $ageGroupKey = null,
    ): ?string {
        $earliestCompleted = self::earliestCompletedMatchDate($league, $groupCard, $ageGroupKey);

        if ($earliestCompleted === null) {
            return null;
        }

        $proposedStart = Carbon::parse($startDateYmd)->startOfDay();

        if ($proposedStart->gt($earliestCompleted)) {
            return 'Playoff start cannot be after the earliest completed playoff match ('.$earliestCompleted->format('M j, Y').').';
        }

        return null;
    }

    public static function validateEndDateAgainstScheduledMatches(
        League $league,
        GroupCard $groupCard,
        string $endDateYmd,
        ?string $ageGroupKey = null,
    ): ?string {
        $proposedEnd = Carbon::parse($endDateYmd)->startOfDay();
        $latestMatchDay = self::latestCompletedMatchDate($league, $groupCard, $ageGroupKey);

        if ($latestMatchDay !== null && $proposedEnd->lt($latestMatchDay)) {
            return 'Playoff end date cannot be before the latest scheduled playoff match ('.$latestMatchDay->format('M j, Y').').';
        }

        return null;
    }

    /**
     * @param  array{updated: int, rounds: int}  $totals
     */
    public static function formatSyncSummary(array $totals, string $baseMessage): string
    {
        if ($totals['updated'] <= 0) {
            return $baseMessage;
        }

        return $baseMessage.' '.$totals['updated'].' playoff match date(s) updated. Notification emails are queued.';
    }

    private static function shouldAssignDate(
        PlayoffMatch $match,
        bool $reschedulePending,
        Carbon $windowStart,
        Carbon $windowEnd,
    ): bool {
        if ($match->match_date === null) {
            return true;
        }

        if ($reschedulePending && $match->isPending()) {
            return true;
        }

        if (! $match->isPending()) {
            return false;
        }

        $current = $match->match_date->copy()->startOfDay();

        return $current->lt($windowStart) || $current->gt($windowEnd);
    }

    private static function earlierRoundsWindowEnd(Carbon $windowStart, Carbon $windowEnd): Carbon
    {
        $candidate = LeagueWeekCalendar::lastPlayableDayOnOrBefore($windowEnd->copy()->subDays(6));

        return $candidate->lt($windowStart) ? $windowEnd : $candidate;
    }

    /**
     * @return list<string> Y-m-d
     */
    private static function datesForFinalRound(int $matchCount, Carbon $windowStart, Carbon $windowEnd): array
    {
        if ($matchCount <= 0) {
            return [];
        }

        if ($matchCount === 1) {
            return [$windowEnd->format('Y-m-d')];
        }

        $weekEndSunday = self::weekEndSundayForDate($windowEnd);
        $dates = LeagueWeekCalendar::spreadMatchDatesAcrossWeek(
            $weekEndSunday,
            $windowStart,
            $matchCount,
            false,
        );

        $dates = self::clampDateStrings($dates, $windowStart, $windowEnd);
        $dates[array_key_last($dates)] = $windowEnd->format('Y-m-d');

        return $dates;
    }

    /**
     * @return list<string> Y-m-d
     */
    private static function datesForEarlierRound(
        int $matchCount,
        Carbon $windowStart,
        Carbon $windowEnd,
        int $roundIndex,
        int $roundCount,
        bool $isFirstPlayoffRound,
    ): array {
        if ($matchCount <= 0) {
            return [];
        }

        $anchor = self::roundAnchor($roundIndex, $roundCount, $windowStart, $windowEnd);
        $weekEndSunday = self::weekEndSundayForDate($anchor);
        $dates = LeagueWeekCalendar::spreadMatchDatesAcrossWeek(
            $weekEndSunday,
            $windowStart,
            $matchCount,
            $isFirstPlayoffRound,
        );

        return self::clampDateStrings($dates, $windowStart, $windowEnd);
    }

    private static function roundAnchor(int $roundIndex, int $roundCount, Carbon $windowStart, Carbon $windowEnd): Carbon
    {
        if ($roundCount <= 1) {
            return $windowEnd->copy();
        }

        if ($roundIndex <= 0) {
            return $windowStart->copy();
        }

        if ($roundIndex >= $roundCount - 1) {
            return $windowEnd->copy();
        }

        $totalDays = max(0, $windowStart->diffInDays($windowEnd));
        $offsetDays = (int) round($totalDays * ($roundIndex / ($roundCount - 1)));
        $anchor = $windowStart->copy()->addDays($offsetDays);

        return self::clampPlayableDay($anchor, $windowStart, $windowEnd);
    }

    private static function clampPlayableDay(Carbon $day, Carbon $min, Carbon $max): Carbon
    {
        $d = LeagueWeekCalendar::lastPlayableDayOnOrBefore($day);

        if ($d->lt($min)) {
            return $min->copy();
        }

        if ($d->gt($max)) {
            return $max->copy();
        }

        return $d;
    }

    /**
     * @param  list<string>  $dates
     * @return list<string>
     */
    private static function clampDateStrings(array $dates, Carbon $windowStart, Carbon $windowEnd): array
    {
        return array_map(
            fn (string $ymd): string => self::clampPlayableDay(
                Carbon::parse($ymd)->startOfDay(),
                $windowStart,
                $windowEnd,
            )->format('Y-m-d'),
            $dates,
        );
    }

    private static function weekEndSundayForDate(Carbon $day): Carbon
    {
        $anchor = $day->copy()->startOfDay();

        return $anchor->isSunday() ? $anchor : $anchor->copy()->next(Carbon::SUNDAY);
    }
}
