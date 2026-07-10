<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\League;

final class TournamentRegistrationOptions
{
    /**
     * League divisions (group cards) for registration — filtered by singles/doubles tab only.
     *
     * @return list<array{
     *     id: int,
     *     name: string,
     *     skill_level: string|null,
     *     label: string,
     *     registration_open: bool,
     *     closed_reason: string|null
     * }>
     */
    public static function groupCardsFor(League $league, string $tab): array
    {
        $tags = $tab === 'singles' ? ['single', 'singles'] : ['double', 'doubles'];

        $cards = $league->groupCards()
            ->where('group_cards.status', 'active')
            ->whereIn('group_cards.tag', $tags)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $options = [];

        foreach ($cards as $card) {
            if (LeagueRegistrationRoster::registrationTypeForGroupCard($card) !== ($tab === 'singles' ? 'singles' : 'doubles')) {
                continue;
            }

            $name = trim((string) $card->name);
            $skill = trim((string) ($card->skill_level_match ?? ''));
            $label = $name !== '' ? $name : 'Group #'.$card->id;

            $closedReason = LeagueRegistrationGate::closedReason($league, $card, null);

            $options[] = [
                'id' => (int) $card->id,
                'name' => $name !== '' ? $name : $label,
                'skill_level' => $skill !== '' ? $skill : null,
                'label' => $label,
                'registration_open' => $closedReason === null,
                'closed_reason' => $closedReason,
            ];
        }

        return $options;
    }

    /**
     * Group card for a player skill: lowest tier whose ceiling is >= player skill,
     * or highest tier if the player is above all configured tiers ("not-sure" → lowest tier).
     */
    public static function resolveGroupCardBySkill(League $league, string $tab, string $skillLevel): ?GroupCard
    {
        $assigned = self::assignedGroupForSkill($league, $tab, $skillLevel);

        if ($assigned === null) {
            return null;
        }

        return self::resolveGroupCard($league, $tab, (int) $assigned['id']);
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     skill_level: string|null,
     *     label: string,
     *     registration_open: bool,
     *     closed_reason: string|null
     * }|null
     */
    public static function assignedGroupForSkill(League $league, string $tab, string $skillLevel): ?array
    {
        $skillLevel = trim($skillLevel);

        if ($skillLevel === 'not-sure') {
            return self::assignedGroupForLowestSkillTier($league, $tab);
        }

        if ($skillLevel === '' || ! is_numeric($skillLevel)) {
            return null;
        }

        $userSkill = (float) $skillLevel;

        $tiers = [];

        foreach (self::groupCardsFor($league, $tab) as $group) {
            $tierSkill = trim((string) ($group['skill_level'] ?? ''));
            if ($tierSkill === '' || ! is_numeric($tierSkill)) {
                continue;
            }

            $tiers[] = [
                'skill' => (float) $tierSkill,
                'group' => $group,
            ];
        }

        if ($tiers === []) {
            return null;
        }

        usort($tiers, fn (array $a, array $b): int => $a['skill'] <=> $b['skill']);

        foreach ($tiers as $tier) {
            if ($userSkill <= $tier['skill']) {
                return $tier['group'];
            }
        }

        return $tiers[array_key_last($tiers)]['group'];
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     skill_level: string|null,
     *     label: string,
     *     registration_open: bool,
     *     closed_reason: string|null
     * }|null
     */
    public static function assignedGroupForLowestSkillTier(League $league, string $tab): ?array
    {
        $lowest = null;
        $lowestSkill = null;

        foreach (self::groupCardsFor($league, $tab) as $group) {
            $tierSkill = trim((string) ($group['skill_level'] ?? ''));
            if ($tierSkill === '' || ! is_numeric($tierSkill)) {
                continue;
            }

            $skill = (float) $tierSkill;
            if ($lowestSkill === null || $skill < $lowestSkill) {
                $lowestSkill = $skill;
                $lowest = $group;
            }
        }

        return $lowest;
    }

    public static function averageSkillLevels(string $skillOne, string $skillTwo): ?string
    {
        $skillOne = trim($skillOne);
        $skillTwo = trim($skillTwo);

        if ($skillOne === '' || $skillTwo === '' || $skillOne === 'not-sure' || $skillTwo === 'not-sure') {
            return null;
        }

        if (! is_numeric($skillOne) || ! is_numeric($skillTwo)) {
            return null;
        }

        $average = (((float) $skillOne) + ((float) $skillTwo)) / 2;

        return rtrim(rtrim(number_format($average, 2, '.', ''), '0'), '.');
    }

    public static function resolveGroupCard(League $league, string $tab, int $groupCardId): ?GroupCard
    {
        if ($groupCardId <= 0) {
            return null;
        }

        $tags = $tab === 'singles' ? ['single', 'singles'] : ['double', 'doubles'];

        $card = $league->groupCards()
            ->where('group_cards.status', 'active')
            ->whereIn('group_cards.tag', $tags)
            ->whereKey($groupCardId)
            ->first();

        if (! $card instanceof GroupCard) {
            return null;
        }

        $expectedType = $tab === 'singles' ? 'singles' : 'doubles';
        if (LeagueRegistrationRoster::registrationTypeForGroupCard($card) !== $expectedType) {
            return null;
        }

        return $card;
    }
}
