<?php

namespace App\Support;

use App\Enums\PlayoffQualifierPath;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\PlayoffQualifier;
use Illuminate\Support\Facades\Schema;

final class PlayoffPathAssigner
{
    public static function pathForRank(GroupCard $groupCard, int $rank): string
    {
        return GroupPlayoffConfig::fromGroupCard($groupCard)->pathForRank($rank);
    }

    public static function pathLabel(string $path): string
    {
        $normalized = PlayoffQualifierPath::normalizeStored($path);

        if ($normalized === '' || $normalized === PlayoffQualifierPath::Eliminated->value) {
            return '';
        }

        foreach (PlayoffQualifierPath::cases() as $case) {
            if ($case->value === $normalized) {
                return $case->label();
            }
        }

        return $normalized;
    }

    public static function pathForStorage(string $path): string
    {
        $normalized = PlayoffQualifierPath::normalizeStored($path);

        return $normalized === PlayoffQualifierPath::Eliminated->value ? '' : $normalized;
    }

    /**
     * Apply group playoff format to all players in division standings.
     */
    public static function syncDivision(League $league, GroupCard $groupCard, ?string $ageGroupKey): int
    {
        if (! Schema::hasTable('playoff_qualifiers') || ! Schema::hasTable('league_registrations')) {
            return 0;
        }

        $ageKeyDb = $ageGroupKey ?? '';

        $standings = LeagueStandingsBuilder::forSubGroup($league, $groupCard, $ageGroupKey, null);
        $synced = 0;

        foreach ($standings as $standing) {
            $userId = (int) $standing['userId'];
            $rank = (int) $standing['rank'];
            $path = self::pathForStorage(self::pathForRank($groupCard, $rank));

            $existing = PlayoffQualifier::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->where('age_group_key', $ageKeyDb)
                ->where('user_id', $userId)
                ->first();

            PlayoffQualifier::query()->updateOrCreate(
                [
                    'league_id' => $league->id,
                    'group_card_id' => $groupCard->id,
                    'age_group_key' => $ageKeyDb,
                    'user_id' => $userId,
                ],
                [
                    'path' => $path,
                    'needs_pre_match' => false,
                    'qf_slot' => null,
                    'r16_slot' => null,
                    'notes' => $existing?->notes,
                ],
            );
            $synced++;
        }

        return $synced;
    }
}
