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
            if (! Schema::hasColumn('group_cards', 'playoff_quarter_spots')) {
                $table->unsignedSmallInteger('playoff_quarter_spots')->nullable()->after('playoff_format');
            }
            if (! Schema::hasColumn('group_cards', 'playoff_r16_spots')) {
                $table->unsignedSmallInteger('playoff_r16_spots')->nullable()->after('playoff_quarter_spots');
            }
            if (! Schema::hasColumn('group_cards', 'playoff_ppq_spots')) {
                $table->unsignedSmallInteger('playoff_ppq_spots')->nullable()->after('playoff_r16_spots');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('group_cards')) {
            return;
        }

        Schema::table('group_cards', function (Blueprint $table) {
            foreach (['playoff_ppq_spots', 'playoff_r16_spots', 'playoff_quarter_spots'] as $col) {
                if (Schema::hasColumn('group_cards', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
