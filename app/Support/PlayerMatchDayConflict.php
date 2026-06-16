<?php

namespace App\Support;

use App\Models\GroupMatch;
use App\Models\PlayoffMatch;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Players may play multiple matches per day (any league) when start times are spaced apart.
 */
final class PlayerMatchDayConflict
{
    public static function minimumGapHours(): int
    {
        return max(1, (int) config('services.match_schedule.minimum_gap_hours', 4));
    }

    /**
     * Bump start time so every participant is at least minimumGapHours after any other match that day.
     */
    public static function resolveStartTimeForDay(
        string $dateYmd,
        array $playerIds,
        string $preferredHi,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): string {
        $preferredHi = MatchStartTime::normalizeFromRequest($preferredHi);
        if ($preferredHi === '') {
            $preferredHi = '10:00';
        }

        $preferredMinutes = self::timeToMinutes($preferredHi) ?? 600;
        $gapMinutes = self::minimumGapHours() * 60;
        $requiredStart = $preferredMinutes;

        $index = self::scheduleIndexForPlayerIds($playerIds, $ignoreGroupMatchId, $ignorePlayoffMatchId);
        foreach ($playerIds as $userId) {
            $slots = self::filteredSlotsForDay(
                $index[$userId][$dateYmd] ?? [],
                $ignoreGroupMatchId,
                $ignorePlayoffMatchId,
            );

            foreach ($slots as $slot) {
                $existingMinutes = self::timeToMinutes((string) ($slot['time'] ?? ''));
                if ($existingMinutes === null) {
                    $existingMinutes = 600;
                }

                if ($preferredMinutes < $existingMinutes + $gapMinutes) {
                    $requiredStart = max($requiredStart, $existingMinutes + $gapMinutes);
                }
            }
        }

        return self::minutesToHi($requiredStart);
    }

    /**
     * Same-day schedule info (date only — before a time is chosen).
     *
     * @param  array<int>  $playerIds
     * @return list<string>
     */
    public static function infoLinesForDate(
        string $dateYmd,
        array $playerIds,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): array {
        return self::warningLinesForDate($dateYmd, $playerIds, $ignoreGroupMatchId, $ignorePlayoffMatchId);
    }

    /**
     * Info lines when a specific start time is chosen (manual scheduling UI).
     *
     * @param  array<int>  $playerIds
     * @return list<string>
     */
    public static function infoLinesForDateTime(
        string $dateYmd,
        string $timeHi,
        array $playerIds,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): array {
        $playerIds = array_values(array_unique(array_filter($playerIds)));
        $timeHi = MatchStartTime::normalizeFromRequest($timeHi);
        if ($dateYmd === '' || $timeHi === '' || $playerIds === []) {
            return [];
        }

        $proposedMinutes = self::timeToMinutes($timeHi);
        if ($proposedMinutes === null) {
            return [];
        }

        $gapMinutes = self::minimumGapHours() * 60;
        $index = self::scheduleIndexForPlayerIds($playerIds, $ignoreGroupMatchId, $ignorePlayoffMatchId);
        $namesById = User::query()->whereIn('id', $playerIds)->pluck('name', 'id')->all();
        $lines = [];

        foreach ($playerIds as $userId) {
            $slots = self::filteredSlotsForDay(
                $index[$userId][$dateYmd] ?? [],
                $ignoreGroupMatchId,
                $ignorePlayoffMatchId,
            );

            foreach ($slots as $slot) {
                $timeLabel = trim((string) ($slot['time_label'] ?? '')) ?: 'Time TBA';
                $leagueName = trim((string) ($slot['league'] ?? 'another tournament')) ?: 'another tournament';
                $name = trim((string) ($namesById[$userId] ?? 'Player'));
                $baseLine = $name.' already has a match on this date at '.$timeLabel.' in '.$leagueName.'.';

                $existingMinutes = self::timeToMinutes((string) ($slot['time'] ?? ''));
                if ($existingMinutes === null) {
                    $lines[] = $baseLine;

                    continue;
                }

                if (abs($proposedMinutes - $existingMinutes) < $gapMinutes) {
                    $suggested = self::resolveStartTimeForDay(
                        $dateYmd,
                        [$userId],
                        $timeHi,
                        $ignoreGroupMatchId,
                        $ignorePlayoffMatchId,
                    );
                    $suggestedLabel = MatchStartTime::formatDisplay($suggested) ?: $suggested;
                    $lines[] = $baseLine.' Leave at least '.self::minimumGapHours()
                        .' hours between matches (suggested start: '.$suggestedLabel.').';
                } else {
                    $lines[] = $baseLine;
                }
            }
        }

        return array_values(array_unique($lines));
    }

    public static function timeAdjustmentNotice(string $originalHi, string $resolvedHi): ?string
    {
        $originalHi = MatchStartTime::normalizeFromRequest($originalHi);
        $resolvedHi = MatchStartTime::normalizeFromRequest($resolvedHi);
        if ($originalHi === '' || $resolvedHi === '' || $originalHi === $resolvedHi) {
            return null;
        }

        $originalLabel = MatchStartTime::formatDisplay($originalHi) ?: $originalHi;
        $resolvedLabel = MatchStartTime::formatDisplay($resolvedHi) ?: $resolvedHi;

        return 'Start time adjusted from '.$originalLabel.' to '.$resolvedLabel
            .' ('.self::minimumGapHours().'+ hour gap between matches on the same day).';
    }

    private static function timeToMinutes(string $hi): ?int
    {
        $hi = MatchStartTime::toInputValue($hi);
        if ($hi === '' || ! preg_match('/^(\d{2}):(\d{2})$/', $hi, $m)) {
            return null;
        }

        return ((int) $m[1] * 60) + (int) $m[2];
    }

    private static function minutesToHi(int $minutes): string
    {
        $minutes = max(0, min((23 * 60) + 55, $minutes));

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * @param  array<int>  $playerIds
     * @return array<int, array<string, list<array{time: string, time_label: string, league: string, group_match_id: int|null, playoff_match_id: int|null}>>>
     */
    public static function scheduleIndexForPlayerIds(
        array $playerIds,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): array {
        $playerIds = array_values(array_unique(array_filter($playerIds)));
        if ($playerIds === []) {
            return [];
        }

        $index = [];
        foreach ($playerIds as $userId) {
            $index[$userId] = [];
        }

        if (Schema::hasTable('group_matches')) {
            $q = GroupMatch::query()
                ->where(function ($query) use ($playerIds) {
                    $query->whereIn('home_user_id', $playerIds)
                        ->orWhereIn('away_user_id', $playerIds)
                        ->orWhereIn('home_partner_user_id', $playerIds)
                        ->orWhereIn('away_partner_user_id', $playerIds);
                })
                ->with('league:id,name');

            if ($ignoreGroupMatchId !== null) {
                $q->where('id', '!=', $ignoreGroupMatchId);
            }

            foreach ($q->get() as $match) {
                self::addMatchToIndex($index, $match->match_date->format('Y-m-d'), self::participantIdsFromGroupMatch($match), [
                    'time' => MatchStartTime::toInputValue((string) ($match->start_time ?? '')),
                    'time_label' => MatchStartTime::formatDisplay((string) ($match->start_time ?? '')) ?: 'Time TBA',
                    'league' => (string) ($match->league?->name ?? 'League'),
                    'group_match_id' => (int) $match->id,
                    'playoff_match_id' => null,
                ]);
            }
        }

        if (Schema::hasTable('playoff_matches')) {
            $q = PlayoffMatch::query()
                ->where(function ($query) use ($playerIds) {
                    $query->whereIn('home_user_id', $playerIds)
                        ->orWhereIn('away_user_id', $playerIds);
                })
                ->with('league:id,name');

            if ($ignorePlayoffMatchId !== null) {
                $q->where('id', '!=', $ignorePlayoffMatchId);
            }

            foreach ($q->get() as $match) {
                if (! $match->match_date) {
                    continue;
                }
                $participantIds = array_values(array_filter([
                    $match->home_user_id ? (int) $match->home_user_id : null,
                    $match->away_user_id ? (int) $match->away_user_id : null,
                ]));
                self::addMatchToIndex($index, $match->match_date->format('Y-m-d'), $participantIds, [
                    'time' => MatchStartTime::toInputValue((string) ($match->start_time ?? '')),
                    'time_label' => MatchStartTime::formatDisplay((string) ($match->start_time ?? '')) ?: 'Time TBA',
                    'league' => (string) ($match->league?->name ?? 'League'),
                    'group_match_id' => null,
                    'playoff_match_id' => (int) $match->id,
                ]);
            }
        }

        return $index;
    }

    /**
     * @param  array<int>  $playerIds
     * @return list<string>
     */
    public static function warningLinesForDate(
        string $dateYmd,
        array $playerIds,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): array {
        $playerIds = array_values(array_unique(array_filter($playerIds)));
        if ($playerIds === []) {
            return [];
        }

        $index = self::scheduleIndexForPlayerIds($playerIds, $ignoreGroupMatchId, $ignorePlayoffMatchId);
        $namesById = User::query()->whereIn('id', $playerIds)->pluck('name', 'id')->all();

        $lines = [];
        foreach ($playerIds as $userId) {
            $slots = $index[$userId][$dateYmd] ?? [];
            if ($slots === []) {
                continue;
            }

            $name = trim((string) ($namesById[$userId] ?? 'Player'));
            $slotParts = array_map(
                fn (array $slot) => $slot['time_label'].' ('.$slot['league'].')',
                $slots,
            );

            $lines[] = $name.' already has a match on this date at '.implode(', ', $slotParts);
        }

        return $lines;
    }

    /**
     * @param  array<int, array<string, list<array{time: string, time_label: string, league: string}>>>  $index
     * @param  list<int>  $participantIds
     * @param  array{time: string, time_label: string, league: string, group_match_id: int|null, playoff_match_id: int|null}  $slot
     */
    private static function addMatchToIndex(array &$index, string $dateYmd, array $participantIds, array $slot): void
    {
        foreach ($participantIds as $userId) {
            if (! isset($index[$userId])) {
                continue;
            }
            $index[$userId][$dateYmd] ??= [];
            $index[$userId][$dateYmd][] = $slot;
        }
    }

    /**
     * @param  array<int>  $candidatePlayerIds
     * @return array<int>
     */
    public static function conflictingPlayerIds(
        string $dateYmd,
        array $candidatePlayerIds,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): array {
        $busy = [];
        foreach (array_values(array_unique(array_filter($candidatePlayerIds))) as $userId) {
            if (self::warningLinesForDate($dateYmd, [$userId], $ignoreGroupMatchId, $ignorePlayoffMatchId) !== []) {
                $busy[] = $userId;
            }
        }

        return $busy;
    }

    /**
     * @param  array<int>  $userIds
     */
    public static function messageFor(string $dateYmd, array $userIds): string
    {
        $lines = self::warningLinesForDate($dateYmd, $userIds);

        return $lines !== []
            ? implode(' ', $lines)
            : 'This player already has a match on this date.';
    }

    /**
     * Red notice lines for match cards (admin + player), using a pre-built schedule index.
     *
     * @param  array<int, array<string, list<array{time: string, time_label: string, league: string, group_match_id: int|null, playoff_match_id: int|null}>>>  $index
     * @param  array<int, string>  $namesById
     * @return list<string>
     */
    public static function cardNoticeLinesFromIndex(
        array $index,
        string $dateYmd,
        array $playerIds,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
        array $namesById = [],
    ): array {
        $dateYmd = trim($dateYmd);
        if ($dateYmd === '') {
            return [];
        }

        $playerIds = array_values(array_unique(array_filter($playerIds)));
        if ($playerIds === []) {
            return [];
        }

        if ($namesById === []) {
            $namesById = User::query()->whereIn('id', $playerIds)->pluck('name', 'id')->all();
        }

        $lines = [];
        foreach ($playerIds as $userId) {
            $slots = self::filteredSlotsForDay(
                $index[$userId][$dateYmd] ?? [],
                $ignoreGroupMatchId,
                $ignorePlayoffMatchId,
            );
            if ($slots === []) {
                continue;
            }

            $name = trim((string) ($namesById[$userId] ?? 'Player'));
            foreach ($slots as $slot) {
                $timeLabel = trim((string) ($slot['time_label'] ?? 'Time TBA')) ?: 'Time TBA';
                $leagueName = trim((string) ($slot['league'] ?? 'another tournament')) ?: 'another tournament';
                $lines[] = $name.' also has a match on this date at '.$timeLabel.' in '.$leagueName.'. Change date or time here or in that league.';
            }
        }

        return $lines;
    }

    /**
     * @param  array<int, array<string, list<array{time: string, time_label: string, league: string, group_match_id: int|null, playoff_match_id: int|null}>>>  $index
     * @return list<string>
     */
    public static function viewerNoticeLinesFromIndex(
        array $index,
        int $viewerUserId,
        string $dateYmd,
        ?int $ignoreGroupMatchId = null,
        ?int $ignorePlayoffMatchId = null,
    ): array {
        $dateYmd = trim($dateYmd);
        if ($dateYmd === '' || $viewerUserId <= 0) {
            return [];
        }

        $slots = self::filteredSlotsForDay(
            $index[$viewerUserId][$dateYmd] ?? [],
            $ignoreGroupMatchId,
            $ignorePlayoffMatchId,
        );

        $lines = [];
        foreach ($slots as $slot) {
            $timeLabel = trim((string) ($slot['time_label'] ?? 'Time TBA')) ?: 'Time TBA';
            $leagueName = trim((string) ($slot['league'] ?? 'another tournament')) ?: 'another tournament';
            $lines[] = 'You also have a match on this date at '.$timeLabel.' in '.$leagueName.'. Change date or time below or in that league.';
        }

        return $lines;
    }

    /**
     * @param  list<array{time: string, time_label: string, league: string, group_match_id: int|null, playoff_match_id: int|null}>  $slots
     * @return list<array{time: string, time_label: string, league: string, group_match_id: int|null, playoff_match_id: int|null}>
     */
    private static function filteredSlotsForDay(
        array $slots,
        ?int $ignoreGroupMatchId,
        ?int $ignorePlayoffMatchId,
    ): array {
        return array_values(array_filter($slots, function (array $slot) use ($ignoreGroupMatchId, $ignorePlayoffMatchId): bool {
            if ($ignoreGroupMatchId !== null && (int) ($slot['group_match_id'] ?? 0) === $ignoreGroupMatchId) {
                return false;
            }
            if ($ignorePlayoffMatchId !== null && (int) ($slot['playoff_match_id'] ?? 0) === $ignorePlayoffMatchId) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @return list<int>
     */
    private static function participantIdsFromGroupMatch(GroupMatch $match): array
    {
        return array_values(array_filter([
            (int) $match->home_user_id,
            (int) $match->away_user_id,
            $match->home_partner_user_id ? (int) $match->home_partner_user_id : null,
            $match->away_partner_user_id ? (int) $match->away_partner_user_id : null,
        ]));
    }
}
