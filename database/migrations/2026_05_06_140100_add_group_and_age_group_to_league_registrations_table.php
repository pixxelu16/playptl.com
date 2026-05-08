<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allows assigning a registration/player into a Group (for a league + group card),
     * and storing age bracket snapshot for filtering.
     */
    public function up(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('group_card_id')->constrained('groups')->nullOnDelete();
            $table->string('age_group_key', 32)->nullable()->after('skill_level');
        });
    }

    public function down(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id', 'age_group_key']);
        });
    }
};

