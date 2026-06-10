<?php

use App\Models\User;
use App\Support\UserSkillLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'skill_level')) {
            return;
        }

        User::query()
            ->where(function ($query) {
                $query->whereNull('skill_level')->orWhere('skill_level', '');
            })
            ->whereHas('leagueRegistrations', fn ($query) => $query->whereNotNull('skill_level')->where('skill_level', '!=', ''))
            ->with(['leagueRegistrations' => fn ($query) => $query->orderByDesc('id')])
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $skill = UserSkillLevel::normalize($user->leagueRegistrations->first()?->skill_level);
                    if ($skill !== null) {
                        $user->forceFill(['skill_level' => $skill])->save();
                    }
                }
            });
    }

    public function down(): void
    {
        // No rollback — user skill levels may have been edited after backfill.
    }
};
