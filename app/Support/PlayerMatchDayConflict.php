<?php

namespace App\Support;

use App\Models\GroupMatch;
use App\Models\PlayoffMatch;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Players may play multiple matches per day (any league), but admins get a confirm prompt first.
 */
final class PlayerMatchDayConflict
{
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
