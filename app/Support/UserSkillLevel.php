<?php

namespace App\Support;

use App\Models\User;

final class UserSkillLevel
{
    public static function normalize(?string $skillLevel): ?string
    {
        $skill = trim((string) ($skillLevel ?? ''));

        return $skill !== '' ? $skill : null;
    }

    public static function resolvedFor(User $user): ?string
    {
        $fromUser = self::normalize($user->skill_level);
        if ($fromUser !== null) {
            return $fromUser;
        }

        return self::normalize(
            $user->leagueRegistrations()->latest('id')->value('skill_level')
        );
    }

    public static function syncToUser(User $user, ?string $skillLevel): void
    {
        $skill = self::normalize($skillLevel);
        if ($skill === null) {
            return;
        }

        if ($user->skill_level !== $skill) {
            $user->forceFill(['skill_level' => $skill])->save();
        }
    }
}
