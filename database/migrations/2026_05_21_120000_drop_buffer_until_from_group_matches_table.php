<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('group_matches') || ! Schema::hasColumn('group_matches', 'buffer_until')) {
            return;
        }

        Schema::table('group_matches', function (Blueprint $table) {
            $table->dropColumn('buffer_until');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('group_matches') || Schema::hasColumn('group_matches', 'buffer_until')) {
            return;
        }

        Schema::table('group_matches', function (Blueprint $table) {
            $table->date('buffer_until')->nullable()->after('auto_scheduled');
        });
    }
};
