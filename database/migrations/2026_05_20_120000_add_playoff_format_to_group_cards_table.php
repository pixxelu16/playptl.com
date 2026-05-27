<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('group_cards')) {
            return;
        }

        Schema::table('group_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('group_cards', 'playoff_format')) {
                $table->string('playoff_format', 32)
                    ->nullable()
                    ->after('skill_level_match');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('group_cards')) {
            return;
        }

        Schema::table('group_cards', function (Blueprint $table) {
            if (Schema::hasColumn('group_cards', 'playoff_format')) {
                $table->dropColumn('playoff_format');
            }
        });
    }
};
