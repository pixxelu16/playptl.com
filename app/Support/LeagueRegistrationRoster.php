<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\LeagueRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LeagueRegistrationRoster
{
    public static function isDoublesTeam(LeagueRegistration $reg): bool
    {
        return ($reg->registration_type ?? '') === 'doubles'
            && filled($reg->team_key);
    }

    public static function rosterKey(LeagueRegistration $reg): string
    {
        if (self::isDoublesTeam($reg)) {
            return 'team:'.$reg->team_key;
        }

        return 'user:'.(int) $reg->user_id;
    }

    /**
     * First name token for display (handles "Player A & Player B" style names).
     */
    public static function nameToken(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s*&\s*/', $name);
        $primary = trim((string) ($parts[0] ?? $name));
        $words = preg_split('/\s+/', $primary);

        return trim((string) ($words[0] ?? $primary));
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $regs
     */
    public static function teamDisplayName(Collection $regs): string
    {
        $tokens = $regs
            ->map(fn (LeagueRegistration $r) => self::nameToken((string) ($r->user?->name ?? '')))
            ->filter()
            ->values();

        if ($tokens->isEmpty()) {
            return '—';
        }

        return $tokens->implode('/');
    }

    /**
     * Collapse doubles partners (same team_key) into one roster row for admin lists.
     *
     * @param  Collection<int, LeagueRegistration>  $regs
     * @return Collection<int, array{
     *     registration: LeagueRegistration,
     *     registrations: Collection<int, LeagueRegistration>,
     *     display_name: string,
     *     display_subtitle: string,
     *     user: mixed,
     *     partner_user: mixed
     * }>
     */
    public static function collapseForDisplay(Collection $regs): Collection
    {
        return $regs
            ->groupBy(fn (LeagueRegistration $r) => self::rosterKey($r))
            ->map(function (Collection $teamRegs) {
                $teamRegs = $teamRegs->sortBy('id')->values();
                /** @var LeagueRegistration $primary */
                $primary = $teamRegs->first();

                $displayName = $teamRegs->count() > 1 || self::isDoublesTeam($primary)
                    ? self::teamDisplayName($teamRegs)
                    : (self::nameToken((string) ($primary->user?->name ?? '')) ?: '—');

                $emails = $teamRegs
                    ->map(fn (LeagueRegistration $r) => (string) ($r->user?->email ?? ''))
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'registration' => $primary,
                    'registrations' => $teamRegs,
                    'display_name' => $displayName,
                    'display_subtitle' => $emails->implode(' · '),
                    'user' => $primary->user,
                    'partner_user' => $teamRegs->get(1)?->user,
                ];
            })
            ->sortBy(fn (array $entry) => strtolower($entry['display_name']))
            ->values();
    }

    public static function countSlots(Builder $query): int
    {
        $regs = (clone $query)->get(['id', 'user_id', 'registration_type', 'team_key']);

        return self::collapseForDisplay($regs)->count();
    }

    /**
     * @return list<int>
     */
    public static function registrationIdsForEntry(LeagueRegistration $registration): array
    {
        if (! self::isDoublesTeam($registration)) {
            return [(int) $registration->id];
        }

        return LeagueRegistration::query()
            ->where('league_id', $registration->league_id)
            ->where('team_key', $registration->team_key)
            ->where(function ($q) use ($registration) {
                if ($registration->group_card_id !== null) {
                    $q->where('group_card_id', $registration->group_card_id);
                } else {
                    $q->whereNull('group_card_id');
                }
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function updateGroupForEntry(LeagueRegistration $registration, ?int $groupId): void
    {
        LeagueRegistration::query()
            ->whereIn('id', self::registrationIdsForEntry($registration))
            ->update(['group_id' => $groupId]);
    }

    public static function registrationTypeForGroupCard(GroupCard $groupCard): string
    {
        $tag = strtolower((string) ($groupCard->tag ?? ''));

        return in_array($tag, ['double', 'doubles'], true) ? 'doubles' : 'singles';
    }

    public static function isSinglesSubGroup(GroupCard $groupCard): bool
    {
        return self::registrationTypeForGroupCard($groupCard) === 'singles';
    }

    public static function isDoublesSubGroup(GroupCard $groupCard): bool
    {
        return self::registrationTypeForGroupCard($groupCard) === 'doubles';
    }

    /**
     * A player may only belong to one sub group per league for each format (singles or doubles).
     */
    public static function isInAnotherLeagueSubGroupForType(
        int $userId,
        int $leagueId,
        int $targetGroupCardId,
        string $registrationType,
    ): bool {
        return LeagueRegistration::query()
            ->where('user_id', $userId)
            ->where('league_id', $leagueId)
            ->where('registration_type', $registrationType)
            ->where('group_card_id', '!=', $targetGroupCardId)
            ->exists();
    }

    /**
     * @return list<int>
     */
    public static function userIdsInLeagueSubGroupsForType(
        int $leagueId,
        string $registrationType,
        ?int $exceptGroupCardId = null,
    ): array {
        $query = LeagueRegistration::query()
            ->where('league_id', $leagueId)
            ->where('registration_type', $registrationType);

        if ($exceptGroupCardId !== null) {
            $query->where('group_card_id', '!=', $exceptGroupCardId);
        }

        return $query
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Move registration(s) to another league sub group; clears group assignment.
     *
     * @return list<int> moved registration ids
     */
    public static function moveToSubGroup(
        LeagueRegistration $registration,
        GroupCard $targetGroupCard,
        int $leagueId,
    ): array {
        $ids = self::registrationIdsForEntry($registration);
        $registrationType = self::registrationTypeForGroupCard($targetGroupCard);
        $attributes = [
            'group_card_id' => $targetGroupCard->id,
            'group_id' => null,
            'registration_type' => $registrationType,
        ];

        if ($registrationType === 'singles') {
            $attributes['team_key'] = null;
        }

        if (Schema::hasColumn('group_cards', 'skill_level_match')
            && filled($targetGroupCard->skill_level_match ?? null)) {
            $attributes['skill_level'] = (string) $targetGroupCard->skill_level_match;
        }

        LeagueRegistration::query()
            ->whereIn('id', $ids)
            ->where('league_id', $leagueId)
            ->update($attributes);

        return $ids;
    }

    /**
     * Options for scheduling doubles matches (one row per team).
     *
     * @param  Collection<int, LeagueRegistration>  $regs
     * @param  array<int, int>  $seedByUserId
     * @return Collection<int, array{
     *     key: string,
     *     display_name: string,
     *     primary_user_id: int,
     *     partner_user_id: int|null,
     *     seed_label: string|null,
     *     is_complete: bool
     * }>
     */
    public static function teamOptionsForMatch(Collection $regs, array $seedByUserId = []): Collection
    {
        return self::collapseForDisplay($regs)
            ->map(function (array $entry) use ($seedByUserId) {
                $primaryId = (int) ($entry['user']?->id ?? 0);
                $partnerId = $entry['partner_user'] ? (int) $entry['partner_user']->id : null;

                $seeds = array_values(array_filter([
                    $seedByUserId[$primaryId] ?? null,
                    $partnerId ? ($seedByUserId[$partnerId] ?? null) : null,
                ]));
                $seedLabel = $seeds !== []
                    ? 'Seed #'.implode(' & #', $seeds)
                    : null;

                return [
                    'key' => $partnerId ? $primaryId.':'.$partnerId : (string) $primaryId,
                    'display_name' => $entry['display_name'],
                    'primary_user_id' => $primaryId,
                    'partner_user_id' => $partnerId,
                    'seed_label' => $seedLabel,
                    'is_complete' => $partnerId !== null,
                ];
            })
            ->filter(fn (array $team) => $team['primary_user_id'] > 0)
            ->values();
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $rosterRegs
     * @return array<int, string>
     */
    public static function displayNamesByUserId(Collection $rosterRegs): array
    {
        $out = [];

        foreach (self::collapseForDisplay($rosterRegs) as $entry) {
            $name = $entry['display_name'];
            foreach ($entry['registrations'] as $reg) {
                if ($reg->user_id) {
                    $out[(int) $reg->user_id] = $name;
                }
            }
        }

        return $out;
    }
}
