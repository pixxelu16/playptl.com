<?php

namespace App\Support;

use App\Models\GroupMatch;
use App\Models\GroupMatchPlayerUpload;
use App\Models\PlayoffMatchPlayerUpload;
use Illuminate\Support\Facades\Schema;

final class PlayerMatchWorkflow
{
    public static function playerHasUploadedPhoto(int $groupMatchId, int $userId): bool
    {
        if (! Schema::hasTable('group_match_player_uploads')) {
            return false;
        }

        return GroupMatchPlayerUpload::query()
            ->where('group_match_id', $groupMatchId)
            ->where('uploaded_by_user_id', $userId)
            ->exists();
    }

    public static function ensurePlayerUploadedPhoto(GroupMatch $match, int $userId): ?string
    {
        if (self::playerHasUploadedPhoto((int) $match->id, $userId)) {
            return null;
        }

        return 'Upload at least one match photo before viewing or entering the score.';
    }

    public static function playerHasUploadedPlayoffPhoto(int $playoffMatchId, int $userId): bool
    {
        if (! Schema::hasTable('playoff_match_player_uploads')) {
            return false;
        }

        return PlayoffMatchPlayerUpload::query()
            ->where('playoff_match_id', $playoffMatchId)
            ->where('uploaded_by_user_id', $userId)
            ->exists();
    }

    public static function ensurePlayerUploadedPlayoffPhoto(int $playoffMatchId, int $userId): ?string
    {
        if (self::playerHasUploadedPlayoffPhoto($playoffMatchId, $userId)) {
            return null;
        }

        return 'Upload at least one match photo before viewing or entering the score.';
    }

    public static function matchHasAnyUpload(int $groupMatchId): bool
    {
        return isset(self::matchIdsWithAnyUpload([$groupMatchId])[$groupMatchId]);
    }

    /**
     * @param  list<int>  $groupMatchIds
     * @return array<int, true>
     */
    public static function matchIdsWithAnyUpload(array $groupMatchIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $groupMatchIds))));
        if ($ids === [] || ! Schema::hasTable('group_match_player_uploads')) {
            return [];
        }

        $set = [];
        foreach (GroupMatchPlayerUpload::query()
            ->whereIn('group_match_id', $ids)
            ->distinct()
            ->pluck('group_match_id') as $id) {
            $set[(int) $id] = true;
        }

        return $set;
    }

    /**
     * Public schedule: do not show scores until at least one photo was uploaded for that match.
     *
     * @param  list<array{dateLabel: string, matches: list<array<string, mixed>>}>  $days
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    public static function maskScheduleScoresUntilMatchUpload(array $days): array
    {
        $matchIds = [];
        foreach ($days as $day) {
            foreach ($day['matches'] ?? [] as $row) {
                $gid = (int) ($row['groupMatchId'] ?? 0);
                if ($gid > 0) {
                    $matchIds[] = $gid;
                }
            }
        }

        $withUpload = self::matchIdsWithAnyUpload($matchIds);

        foreach ($days as $dayIndex => $day) {
            foreach ($day['matches'] ?? [] as $matchIndex => $row) {
                $gid = (int) ($row['groupMatchId'] ?? 0);
                if ($gid <= 0 || isset($withUpload[$gid]) || ! ($row['finished'] ?? false)) {
                    continue;
                }

                $days[$dayIndex]['matches'][$matchIndex]['finished'] = false;
                $days[$dayIndex]['matches'][$matchIndex]['score'] = null;
                $days[$dayIndex]['matches'][$matchIndex]['scoreRaw'] = '';
                $days[$dayIndex]['matches'][$matchIndex]['homeSideWon'] = null;
                $days[$dayIndex]['matches'][$matchIndex]['winnerLabel'] = null;
            }
        }

        return $days;
    }
}
