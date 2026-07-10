<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            if (! Schema::hasColumn('leagues', 'playoff_start_date')) {
                $table->date('playoff_start_date')->nullable()->after('playoffs_closed_at');
            }
            if (! Schema::hasColumn('leagues', 'playoff_end_date')) {
                $table->date('playoff_end_date')->nullable()->after('playoff_start_date');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            foreach (['playoff_end_date', 'playoff_start_date'] as $col) {
                if (Schema::hasColumn('leagues', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
