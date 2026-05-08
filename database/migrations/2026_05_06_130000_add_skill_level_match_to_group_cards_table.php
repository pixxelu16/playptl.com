<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional exact match key for group card selection (e.g. 3, 3.25).
     */
    public function up(): void
    {
        Schema::table('group_cards', function (Blueprint $table) {
            $table->string('skill_level_match', 32)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('group_cards', function (Blueprint $table) {
            $table->dropColumn('skill_level_match');
        });
    }
};
