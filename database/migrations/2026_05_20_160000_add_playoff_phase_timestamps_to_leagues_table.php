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
            if (! Schema::hasColumn('leagues', 'playoffs_started_at')) {
                $table->timestamp('playoffs_started_at')->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('leagues', 'playoffs_closed_at')) {
                $table->timestamp('playoffs_closed_at')->nullable()->after('playoffs_started_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leagues')) {
            return;
        }

        Schema::table('leagues', function (Blueprint $table) {
            foreach (['playoffs_closed_at', 'playoffs_started_at'] as $col) {
                if (Schema::hasColumn('leagues', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
