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
        if (Schema::hasTable('leagues') && ! Schema::hasColumn('leagues', 'show_in_menu')) {
            Schema::table('leagues', function (Blueprint $table) {
                $table->boolean('show_in_menu')->default(false)->after('stats');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leagues') && Schema::hasColumn('leagues', 'show_in_menu')) {
            Schema::table('leagues', function (Blueprint $table) {
                $table->dropColumn('show_in_menu');
            });
        }
    }
};
