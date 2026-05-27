<?php

namespace App\Support;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\User;
use App\Support\LeagueRegistrationRoster;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeagueRegistrationFlow
{
    public static function resolveGroupCard(League $league, string $tab, string $skillLevel): ?GroupCard
    {
        return $league->groupCards()
            ->where('group_cards.status', 'active')
            ->whereIn('group_cards.tag', $tab === 'singles' ? ['single', 'singles'] : ['double', 'doubles'])
            ->where('group_cards.skill_level_match', $skillLevel)
            ->first();
    }

    public static function resolveGroupId(int $leagueId, GroupCard $groupCard, string $tab, ?string $ageGroup): ?int
    {
        if (! Schema::hasTable('groups')) {
            return null;
        }

        $groupsQuery = Group::query()
            ->where('status', 'active')
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id));

        if (Schema::hasColumn('groups', 'age_group_key') && $ageGroup !== null && $ageGroup !== '') {
            $groupsQuery->where(function ($q) use ($ageGroup) {
                $q->whereNull('age_group_key')
                    ->orWhere('age_group_key', $ageGroup);
            });
        }

        $candidateGroups = $groupsQuery->orderBy('id')->get();
        if ($candidateGroups->isEmpty()) {
            return null;
        }

        $registrationType = $tab === 'singles' ? 'singles' : 'doubles';
        $bestGroup = null;
        $bestCount = null;

        foreach ($candidateGroups as $candidate) {
            $countQuery = LeagueRegistration::query()
                ->where('league_id', $leagueId)
                ->where('group_card_id', $groupCard->id)
                ->where('group_id', $candidate->id)
                ->where('registration_type', $registrationType);

            $currentCount = $registrationType === 'doubles'
                ? LeagueRegistrationRoster::countSlots($countQuery)
                : $countQuery->count();

            if ($bestCount === null || $currentCount < $bestCount) {
                $bestCount = $currentCount;
                $bestGroup = $candidate;
            }
        }

        return $bestGroup?->id;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function registerUser(User $user, int $leagueId, array $attributes): LeagueRegistration
    {
        $registrationType = (string) ($attributes['registration_type'] ?? 'singles');
        $groupCardId = isset($attributes['group_card_id']) ? (int) $attributes['group_card_id'] : null;

        if ($groupCardId) {
            $league = League::query()->find($leagueId);
            $groupCard = GroupCard::query()->find($groupCardId);
            $skillLevel = (string) ($attributes['skill_level'] ?? '');
            if ($league instanceof League && $groupCard instanceof GroupCard && $skillLevel !== '') {
                $closed = LeagueRegistrationGate::closedReasonForSelection(
                    $league,
                    $registrationType === 'doubles' ? 'doubles' : 'singles',
                    $skillLevel,
                    isset($attributes['age_group_key']) ? (string) $attributes['age_group_key'] : null,
                );
                if ($closed !== null) {
                    throw new \InvalidArgumentException($closed);
                }
            }
        }

        if ($groupCardId
            && LeagueRegistrationRoster::isInAnotherLeagueSubGroupForType($user->id, $leagueId, $groupCardId, $registrationType)) {
            $formatLabel = $registrationType === 'doubles' ? 'doubles' : 'singles';
            throw new \InvalidArgumentException("Already registered in another {$formatLabel} group for this league.");
        }

        return LeagueRegistration::updateOrCreate(
            [
                'user_id' => $user->id,
                'league_id' => $leagueId,
                'group_card_id' => $attributes['group_card_id'] ?? null,
            ],
            [
                'group_id' => $attributes['group_id'] ?? null,
                'skill_level' => $attributes['skill_level'] ?? null,
                'age_group_key' => $attributes['age_group_key'] ?? null,
                'registration_type' => $attributes['registration_type'] ?? 'singles',
                'team_key' => $attributes['team_key'] ?? null,
                'payment_status' => $attributes['payment_status'] ?? 'completed',
            ],
        );
    }

    public static function newDoublesTeamKey(): string
    {
        return (string) Str::uuid();
    }
}
