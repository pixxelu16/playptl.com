<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('league_registrations', function (Blueprint $table) {
                $table->dropUnique('league_reg_user_league_subgroup_unique');
            });
        } catch (\Throwable $e) {
            // Index might already be dropped
        }

        try {
            Schema::table('league_registrations', function (Blueprint $table) {
                $table->dropUnique('league_registrations_user_id_league_id_unique');
            });
        } catch (\Throwable $e) {
            // Index might already be dropped
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('league_registrations', function (Blueprint $table) {
                $table->unique(['user_id', 'league_id', 'group_card_id'], 'league_reg_user_league_subgroup_unique');
            });
        } catch (\Throwable $e) {
            // Revert fallback
        }
    }
};
