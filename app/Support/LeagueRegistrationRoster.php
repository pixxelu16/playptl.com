<?php

namespace App\Support;

use App\Models\Group;
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
        $teamName = $regs->pluck('team_name')->filter()->map(fn ($n) => trim((string) $n))->first(fn ($n) => $n !== '');
        $names = $regs
            ->map(fn (LeagueRegistration $r) => trim((string) ($r->user?->name ?? '')))
            ->filter()
            ->values();

        $playerNames = $names->isNotEmpty() ? $names->implode(' / ') : '';

        if ($teamName) {
            return $playerNames !== '' ? $teamName.' ('.$playerNames.')' : $teamName;
        }

        if ($names->isEmpty()) {
            return '—';
        }

        return $playerNames;
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
            ->filter(fn (LeagueRegistration $r) => $r->user !== null && strtolower((string) ($r->user->status ?? 'active')) === 'active')
            ->groupBy(fn (LeagueRegistration $r) => self::rosterKey($r))
            ->map(function (Collection $teamRegs) {
                $teamRegs = $teamRegs->sortBy(function (LeagueRegistration $r) {
                    if (filled($r->team_key)) {
                        $role = \App\Models\TeamPlayer::whereHas('team', fn ($q) => $q->where('team_key', $r->team_key))
                            ->where('user_id', $r->user_id)
                            ->value('role');
                        if ($role === 'captain') {
                            return 0;
                        }
                        if ($role === 'member') {
                            return 1;
                        }
                    }
                    return (int) $r->id;
                })->values();

                /** @var LeagueRegistration $primary */
                $primary = $teamRegs->first();
                $maxId = (int) $teamRegs->max('id');

                $teamName = $teamRegs->pluck('team_name')->filter()->map(fn ($n) => trim((string) $n))->first(fn ($n) => $n !== '') ?? '';

                $names = $teamRegs
                    ->map(fn (LeagueRegistration $r) => trim((string) ($r->user?->name ?? '')))
                    ->filter()
                    ->values();
                $playerNames = $names->isNotEmpty() ? $names->implode(' / ') : (trim((string) ($primary->user?->name ?? '')) ?: '—');

                $displayName = $teamRegs->count() > 1 || self::isDoublesTeam($primary)
                    ? self::teamDisplayName($teamRegs)
                    : (trim((string) ($primary->user?->name ?? '')) ?: '—');

                $emails = $teamRegs
                    ->map(fn (LeagueRegistration $r) => (string) ($r->user?->email ?? ''))
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'registration' => $primary,
                    'registrations' => $teamRegs,
                    'latest_id' => $maxId,
                    'display_name' => $displayName,
                    'player_names' => $playerNames,
                    'team_name' => $teamName,
                    'display_subtitle' => $emails->implode(' · '),
                    'user' => $primary->user,
                    'partner_user' => $teamRegs->get(1)?->user,
                ];
            })
            ->sortByDesc(fn (array $entry) => (int) ($entry['latest_id'] ?? $entry['registration']?->id ?? 0))
            ->values();
    }

    public static function countSlots(Builder $query): int
    {
        $regs = (clone $query)->with('user')->get();

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
     * A player may only belong to one sub group per league for each format (singles or doubles) and category.
     */
    public static function isInAnotherLeagueSubGroupForType(
        int $userId,
        int $leagueId,
        int $targetGroupCardId,
        string $registrationType,
        ?string $category = null,
    ): bool {
        $query = LeagueRegistration::query()
            ->where('user_id', $userId)
            ->where('league_id', $leagueId)
            ->where('registration_type', $registrationType)
            ->where('group_card_id', '!=', $targetGroupCardId);

        if ($category !== null) {
            $query->where('category', $category);
        }

        return $query->exists();
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
     * Move registration(s) to another league sub group; preserves/maps subgroup assignment.
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

        $targetGroupId = null;
        if ($registration->group_id !== null) {
            $currentGroup = Group::query()->find($registration->group_id);
            if ($currentGroup) {
                $isValidInTarget = Group::query()
                    ->whereKey($currentGroup->id)
                    ->where(function ($q) use ($targetGroupCard) {
                        $q->where('group_card_id', $targetGroupCard->id)
                            ->orWhereHas('groupCards', fn ($qq) => $qq->whereKey($targetGroupCard->id));
                    })
                    ->exists();

                if ($isValidInTarget) {
                    $targetGroupId = $currentGroup->id;
                } else {
                    $targetGroupId = Group::query()
                        ->where(function ($q) use ($targetGroupCard) {
                            $q->where('group_card_id', $targetGroupCard->id)
                                ->orWhereHas('groupCards', fn ($qq) => $qq->whereKey($targetGroupCard->id));
                        })
                        ->where('name', $currentGroup->name)
                        ->value('id');
                }
            }
        }

        $attributes = [
            'group_card_id' => $targetGroupCard->id,
            'group_id' => $targetGroupId,
            'registration_type' => $registrationType,
        ];

        if ($registrationType === 'singles') {
            $attributes['team_key'] = null;
        }

        if (Schema::hasColumn('group_cards', 'skill_level_match')
            && filled($targetGroupCard->skill_level_match ?? null)
            && ! str_contains($targetGroupCard->skill_level_match, ',')) {
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

    /**
     * @return list<int>
     */
    public static function teamMemberUserIds(LeagueRegistration $registration): array
    {
        if (! filled($registration->team_key)) {
            return [(int) $registration->user_id];
        }

        return LeagueRegistration::query()
            ->where('league_id', $registration->league_id)
            ->where('team_key', $registration->team_key)
            ->where(function ($query) use ($registration) {
                if ($registration->group_card_id !== null) {
                    $query->where('group_card_id', $registration->group_card_id);
                } else {
                    $query->whereNull('group_card_id');
                }
            })
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function partnerUserIdFor(LeagueRegistration $registration): ?int
    {
        $selfId = (int) $registration->user_id;

        foreach (self::teamMemberUserIds($registration) as $userId) {
            if ($userId !== $selfId) {
                return $userId;
            }
        }

        return null;
    }

    public static function partnerRegistrationIdFor(LeagueRegistration $registration): ?int
    {
        $partnerUserId = self::partnerUserIdFor($registration);
        if ($partnerUserId === null) {
            return null;
        }

        $partnerId = LeagueRegistration::query()
            ->where('league_id', $registration->league_id)
            ->where('user_id', $partnerUserId)
            ->where(function ($query) use ($registration) {
                if ($registration->group_card_id !== null) {
                    $query->where('group_card_id', $registration->group_card_id);
                } else {
                    $query->whereNull('group_card_id');
                }
            })
            ->value('id');

        return $partnerId !== null ? (int) $partnerId : null;
    }

    public static function isAvailableAsPartner(LeagueRegistration $candidate, int $forUserId): bool
    {
        if ((int) $candidate->user_id === $forUserId) {
            return false;
        }

        $existingPartnerId = self::partnerUserIdFor($candidate);

        return $existingPartnerId === null || $existingPartnerId === $forUserId;
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $candidateRegs
     * @return Collection<int, array{registration_id: int, user_id: int, label: string}>
     */
    public static function partnerOptionsFor(
        LeagueRegistration $registration,
        Collection $candidateRegs,
    ): Collection {
        $selfUserId = (int) $registration->user_id;
        $currentPartnerRegId = self::partnerRegistrationIdFor($registration);

        // Pre-build team_key to user names map for fast lookup
        $teamMembersByTeamKey = [];
        foreach ($candidateRegs as $cr) {
            if (filled($cr->team_key) && $cr->user) {
                $teamMembersByTeamKey[$cr->team_key][$cr->user_id] = trim((string) $cr->user->name);
            }
        }

        return $candidateRegs
            ->filter(function (LeagueRegistration $candidate) use ($selfUserId) {
                if ((int) $candidate->user_id === $selfUserId) {
                    return false;
                }
                $user = $candidate->user;
                if (! $user) {
                    return false;
                }
                $status = strtolower((string) ($user->status ?? 'active'));

                return $status === 'active';
            })
            ->groupBy('user_id')
            ->map(function (Collection $userRegs, $userId) use ($registration, $currentPartnerRegId, $teamMembersByTeamKey) {
                // Pick the candidate registration matching the current partner registration or group card
                $candidate = null;
                if ($currentPartnerRegId !== null) {
                    $candidate = $userRegs->firstWhere('id', $currentPartnerRegId);
                }
                if (! $candidate && filled($registration->team_key)) {
                    $candidate = $userRegs->firstWhere('team_key', $registration->team_key);
                }
                if (! $candidate && $registration->group_card_id !== null) {
                    $candidate = $userRegs->firstWhere('group_card_id', $registration->group_card_id);
                }
                if (! $candidate) {
                    $candidate = $userRegs->first();
                }

                $name = trim((string) ($candidate->user?->name ?? ''));
                $email = trim((string) ($candidate->user?->email ?? ''));
                $baseName = $name !== '' ? $name : ($email !== '' ? $email : 'Player');

                // Determine partner status badge/label
                $partnerName = null;
                if (filled($candidate->team_key) && isset($teamMembersByTeamKey[$candidate->team_key])) {
                    foreach ($teamMembersByTeamKey[$candidate->team_key] as $tUserId => $tName) {
                        if ((int) $tUserId !== (int) $userId) {
                            $partnerName = $tName;
                            break;
                        }
                    }
                }

                $statusBadge = $partnerName ? " (Partner: {$partnerName})" : ' (No Partner)';

                return [
                    'registration_id' => (int) $candidate->id,
                    'user_id' => (int) $userId,
                    'label' => $baseName . $statusBadge,
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public static function linkPartners(LeagueRegistration $primary, LeagueRegistration $partner): void
    {
        self::assertSamePartnerContext($primary, $partner);

        // Find former partner of primary
        $primaryOldPartnerId = self::partnerRegistrationIdFor($primary);
        $partnerOldPartnerId = self::partnerRegistrationIdFor($partner);

        // If primary had an existing partner, delete the replaced partner registration so they don't linger under unassigned
        if ($primaryOldPartnerId !== null && (int) $primaryOldPartnerId !== (int) $primary->id && (int) $primaryOldPartnerId !== (int) $partner->id) {
            LeagueRegistration::query()->where('id', $primaryOldPartnerId)->delete();
        }

        // If the new partner was previously partnered with someone else, unpair that player left behind so they remain as a solo player in their group (preserving their team name)
        if ($partnerOldPartnerId !== null && (int) $partnerOldPartnerId !== (int) $primary->id && (int) $partnerOldPartnerId !== (int) $partner->id) {
            LeagueRegistration::query()->where('id', $partnerOldPartnerId)->update([
                'team_key' => null,
            ]);
        }

        foreach ([$primary, $partner] as $registration) {
            if (! filled($registration->team_key)) {
                continue;
            }

            LeagueRegistration::query()
                ->where('league_id', $registration->league_id)
                ->where('team_key', $registration->team_key)
                ->where(function ($query) use ($registration) {
                    if ($registration->group_card_id !== null) {
                        $query->where('group_card_id', $registration->group_card_id);
                    } else {
                        $query->whereNull('group_card_id');
                    }
                })
                ->update(['team_key' => null]);
        }

        $teamKey = LeagueRegistrationFlow::newDoublesTeamKey();
        $teamName = trim((string) ($primary->team_name ?: $partner->team_name));
        $targetGroupId = $primary->group_id ?? $partner->group_id;
        $targetGroupCardId = $primary->group_card_id ?? $partner->group_card_id;

        LeagueRegistration::query()
            ->whereIn('id', [(int) $primary->id, (int) $partner->id])
            ->update([
                'group_card_id' => $targetGroupCardId,
                'group_id' => $targetGroupId,
                'team_key' => $teamKey,
                'team_name' => $teamName !== '' ? $teamName : null,
                'registration_type' => 'doubles',
            ]);

        // Ensure Team and TeamPlayer relationships are tracked with primary as captain and partner as member
        try {
            $team = \App\Models\Team::updateOrCreate(
                ['team_key' => $teamKey],
                [
                    'name' => $teamName !== '' ? $teamName : null,
                    'league_id' => $primary->league_id,
                ]
            );
            $team->teamPlayers()->delete();
            $team->addPlayer($primary->user_id, 'captain');
            $team->addPlayer($partner->user_id, 'member');
        } catch (\Throwable $e) {
            // Ignore if teams table is unavailable
        }
    }

    public static function unlinkPartner(LeagueRegistration $registration): void
    {
        if (! filled($registration->team_key)) {
            return;
        }

        $partnerRegId = self::partnerRegistrationIdFor($registration);

        // Unlink primary registration (preserve team_name)
        LeagueRegistration::query()
            ->where('id', $registration->id)
            ->update([
                'team_key' => null,
            ]);

        // Remove the unlinked partner registration so it doesn't linger under unassigned
        if ($partnerRegId !== null && (int) $partnerRegId !== (int) $registration->id) {
            LeagueRegistration::query()
                ->where('id', $partnerRegId)
                ->delete();
        }

        try {
            \App\Models\Team::query()->where('team_key', $registration->team_key)->delete();
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public static function syncSubgroupForPartners(LeagueRegistration $registration, ?int $groupId): void
    {
        if (filled($registration->team_key)) {
            LeagueRegistration::query()
                ->where('league_id', $registration->league_id)
                ->where('team_key', $registration->team_key)
                ->update(['group_id' => $groupId]);
        } else {
            self::updateGroupForEntry($registration, $groupId);
        }
    }

    private static function assertSamePartnerContext(LeagueRegistration $primary, LeagueRegistration $partner): void
    {
        if ((int) $primary->league_id !== (int) $partner->league_id) {
            throw new \InvalidArgumentException('Partners must be in the same tournament.');
        }

        if ((int) ($primary->group_card_id ?? 0) !== (int) ($partner->group_card_id ?? 0)) {
            throw new \InvalidArgumentException('Partners must be in the same group.');
        }

        if ((int) $primary->user_id === (int) $partner->user_id) {
            throw new \InvalidArgumentException('A player cannot partner with themselves.');
        }
    }
}
