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
        if (Schema::hasTable('leagues') && ! Schema::hasColumn('leagues', 'realize_tournament')) {
            Schema::table('leagues', function (Blueprint $table) {
                $table->boolean('realize_tournament')->default(false)->after('show_in_menu');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leagues') && Schema::hasColumn('leagues', 'realize_tournament')) {
            Schema::table('leagues', function (Blueprint $table) {
                $table->dropColumn('realize_tournament');
            });
        }
    }
};
