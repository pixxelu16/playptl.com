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
            if ($skill !== '' && ! str_contains(strtolower($label), strtolower($skill))) {
                $label .= ' ('.$skill.')';
            }

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
