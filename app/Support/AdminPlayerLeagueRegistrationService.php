<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AdminPlayerLeagueRegistrationService
{
    /**
     * @return list<string>
     */
    public static function skillLevelValues(): array
    {
        return ['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', 'not-sure'];
    }

    /**
     * @return Collection<string, string>
     */
    public static function ageBrackets(): Collection
    {
        return collect([
            'under-18' => 'Under 18',
            '18-21' => '18–21',
            '21-25' => '21–25',
            '26-30' => '26–30',
            '31-35' => '31–35',
            '36-40' => '36–40',
            '41-45' => '41–45',
            '46-50' => '46–50',
            'above-50' => 'Above 50',
        ]);
    }

    public static function registrationTabFor(User $player): string
    {
        return ($player->registration_type ?? 'singles') === 'doubles' ? 'doubles' : 'singles';
    }

    public static function createRegistration(
        User $player,
        int $leagueId,
        string $skillLevel,
        string $ageGroupKey,
        string $tab,
    ): LeagueRegistration {
        $league = League::query()->findOrFail($leagueId);
        $groupCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $tab, $skillLevel);

        if (! $groupCard instanceof GroupCard) {
            throw ValidationException::withMessages([
                'skill_level' => self::groupCardErrorMessage($skillLevel),
            ]);
        }

        $alreadyInSubGroup = LeagueRegistration::query()
            ->where('user_id', $player->id)
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->exists();

        if ($alreadyInSubGroup) {
            throw ValidationException::withMessages([
                'league_id' => 'This player is already registered in this league group.',
            ]);
        }

        if (LeagueRegistrationRoster::isInAnotherLeagueSubGroupForType(
            $player->id,
            $league->id,
            $groupCard->id,
            $tab,
        )) {
            $formatLabel = $tab === 'doubles' ? 'doubles' : 'singles';

            throw ValidationException::withMessages([
                'league_id' => "This player is already in another {$formatLabel} group for this league.",
            ]);
        }

        $groupId = LeagueRegistrationFlow::resolveGroupId($league->id, $groupCard, $tab, $ageGroupKey);

        $registration = LeagueRegistration::create([
            'user_id' => $player->id,
            'league_id' => $league->id,
            'group_card_id' => $groupCard->id,
            'group_id' => $groupId,
            'skill_level' => $skillLevel,
            'age_group_key' => $ageGroupKey,
            'registration_type' => $tab,
            'payment_status' => 'admin',
        ]);

        UserSkillLevel::syncToUser($player, $skillLevel);

        return $registration;
    }

    public static function syncRegistration(
        LeagueRegistration $registration,
        User $player,
        int $leagueId,
        string $skillLevel,
        string $ageGroupKey,
        string $tab,
    ): LeagueRegistration {
        $league = League::query()->findOrFail($leagueId);
        $groupCard = TournamentRegistrationOptions::resolveGroupCardBySkill($league, $tab, $skillLevel);

        if (! $groupCard instanceof GroupCard) {
            throw ValidationException::withMessages([
                'skill_level' => self::groupCardErrorMessage($skillLevel),
            ]);
        }

        $currentGroupCardId = (int) ($registration->group_card_id ?? 0);
        $targetGroupCardId = (int) $groupCard->id;

        if ($targetGroupCardId !== $currentGroupCardId) {
            if (LeagueRegistrationRoster::isInAnotherLeagueSubGroupForType(
                $player->id,
                $league->id,
                $targetGroupCardId,
                $tab,
            )) {
                $formatLabel = $tab === 'doubles' ? 'doubles' : 'singles';

                throw ValidationException::withMessages([
                    'league_id' => "This player is already in another {$formatLabel} group for this league.",
                ]);
            }
        }

        $groupId = LeagueRegistrationFlow::resolveGroupId($league->id, $groupCard, $tab, $ageGroupKey);

        $registration->update([
            'league_id' => $league->id,
            'group_card_id' => $groupCard->id,
            'group_id' => $groupId,
            'skill_level' => $skillLevel,
            'age_group_key' => $ageGroupKey,
            'registration_type' => $tab,
        ]);

        UserSkillLevel::syncToUser($player, $skillLevel);

        return $registration->fresh();
    }

    public static function groupCardErrorMessage(string $skillLevel): string
    {
        return $skillLevel === 'not-sure'
            ? 'No group with a skill level is assigned to this tournament yet.'
            : 'No group matches your skill level for this tournament.';
    }
}
