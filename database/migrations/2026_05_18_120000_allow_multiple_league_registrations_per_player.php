<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow one registration per player per league sub group (multiple leagues + multiple sub groups per league).
     */
    public function up(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['league_id']);
        });

        Schema::table('league_registrations', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'league_id']);
            $table->unique(['user_id', 'league_id', 'group_card_id'], 'league_reg_user_league_subgroup_unique');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('league_id')->references('id')->on('leagues')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['league_id']);
        });

        Schema::table('league_registrations', function (Blueprint $table) {
            $table->dropUnique('league_reg_user_league_subgroup_unique');
            $table->unique(['user_id', 'league_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('league_id')->references('id')->on('leagues')->cascadeOnDelete();
        });
    }
};
