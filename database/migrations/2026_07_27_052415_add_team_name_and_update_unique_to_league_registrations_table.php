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
        Schema::table('league_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('league_registrations', 'team_name')) {
                $table->string('team_name')->nullable()->after('category');
            }
        });

        // Drop existing unique constraint if it exists safely
        try {
            Schema::table('league_registrations', function (Blueprint $table) {
                $table->dropUnique('league_registrations_user_id_league_id_unique');
            });
        } catch (\Throwable $e) {
            // Index might not exist or already dropped
        }

        try {
            Schema::table('league_registrations', function (Blueprint $table) {
                $table->unique(['user_id', 'league_id', 'registration_type', 'category'], 'league_reg_user_league_type_cat_unique');
            });
        } catch (\Throwable $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            try {
                $table->dropUnique('league_reg_user_league_type_cat_unique');
            } catch (\Throwable $e) {}
            if (Schema::hasColumn('league_registrations', 'team_name')) {
                $table->dropColumn('team_name');
            }
        });
    }
};
