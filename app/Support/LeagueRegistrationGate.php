<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use Illuminate\Support\Facades\Schema;

/**
 * Blocks new player sign-ups when a specific division (league + format + skill group card) has started.
 */
final class LeagueRegistrationGate
{
    public static function isOpen(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): bool
    {
        return self::closedReason($league, $groupCard, $ageGroupKey) === null;
    }

    public static function closedReasonForSelection(
        League $league,
        string $tab,
        string $skillLevel,
        ?string $ageGroupKey = null,
    ): ?string {
        $groupCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $tab, $skillLevel);

        if (! $groupCard instanceof GroupCard) {
            return null;
        }

        return self::closedReason($league, $groupCard, $ageGroupKey);
    }

    public static function closedReason(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): ?string
    {
        if ($league->isFinished()) {
            return 'This league has finished. Registration is closed.';
        }

        if (self::divisionHasScheduledMatches($league->id, $groupCard->id)) {
            $groupName = trim((string) $groupCard->name);

            return $groupName !== ''
                ? "{$groupName} has started. Registration is closed for this skill level."
                : 'This group has started. Registration is closed for this skill level.';
        }

        if (LeagueSeasonPhase::playoffsStarted($league)) {
            return 'Playoffs have started for this league. Registration is closed.';
        }

        if (DivisionPlayoffPhase::divisionLocksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
            return 'Playoffs have begun for this group. Registration is closed.';
        }

        return null;
    }

    /**
     * Keys for client-side hints: "{leagueId}:{tab}:{skillLevel}".
     *
     * @return list<string>
     */
    public static function closedSelectionKeys(): array
    {
        if (! Schema::hasTable('group_matches')) {
            return [];
        }

        $keys = [];

        $pairs = GroupMatch::query()
            ->select(['league_id', 'group_card_id'])
            ->distinct()
            ->get();

        foreach ($pairs as $row) {
            $card = GroupCard::query()->find($row->group_card_id);
            if (! $card instanceof GroupCard) {
                continue;
            }

            $skill = (string) ($card->skill_level_match ?? '');
            if ($skill === '') {
                continue;
            }

            $tab = self::tabForGroupCardTag((string) $card->tag);
            $keys[] = self::selectionKey((int) $row->league_id, $tab, $skill);
        }

        return array_values(array_unique($keys));
    }

    public static function selectionKey(int $leagueId, string $tab, string $skillLevel): string
    {
        return $leagueId.':'.($tab === 'doubles' ? 'doubles' : 'singles').':'.$skillLevel;
    }

    /**
     * Keys for client-side hints: "{leagueId}:{groupCardId}".
     *
     * @return list<string>
     */
    public static function closedGroupCardKeys(): array
    {
        if (! Schema::hasTable('group_matches')) {
            return [];
        }

        $keys = [];

        $pairs = GroupMatch::query()
            ->select(['league_id', 'group_card_id'])
            ->distinct()
            ->get();

        foreach ($pairs as $row) {
            $keys[] = self::groupCardKey((int) $row->league_id, (int) $row->group_card_id);
        }

        return array_values(array_unique($keys));
    }

    public static function groupCardKey(int $leagueId, int $groupCardId): string
    {
        return $leagueId.':'.$groupCardId;
    }

    private static function tabForGroupCardTag(string $tag): string
    {
        return in_array(strtolower($tag), ['double', 'doubles'], true) ? 'doubles' : 'singles';
    }

    private static function divisionHasScheduledMatches(int $leagueId, int $groupCardId): bool
    {
        if (! Schema::hasTable('group_matches')) {
            return false;
        }

        return GroupMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->exists();
    }
}
