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
     * Pick the skill-tier group card for a player skill (e.g. 3.25 → 3.5 group, 3.75 → 4.0 group).
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
        if ($skillLevel === '' || $skillLevel === 'not-sure' || ! is_numeric($skillLevel)) {
            return null;
        }

        $userSkill = (float) $skillLevel;
        $groups = self::groupCardsFor($league, $tab);

        $tiered = [];
        foreach ($groups as $group) {
            $tierSkill = trim((string) ($group['skill_level'] ?? ''));
            if ($tierSkill === '' || ! is_numeric($tierSkill)) {
                continue;
            }

            $tiered[] = [
                'group' => $group,
                'skill' => (float) $tierSkill,
            ];
        }

        if ($tiered === []) {
            return null;
        }

        usort($tiered, fn (array $a, array $b): int => $a['skill'] <=> $b['skill']);

        foreach ($tiered as $tier) {
            if ($userSkill <= $tier['skill']) {
                return $tier['group'];
            }
        }

        return $tiered[array_key_last($tiered)]['group'];
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
