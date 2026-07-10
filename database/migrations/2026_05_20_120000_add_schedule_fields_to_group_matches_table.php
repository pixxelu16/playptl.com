<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_matches', function (Blueprint $table) {
            $table->unsignedTinyInteger('round_number')->nullable()->after('sort_order');
            $table->boolean('auto_scheduled')->default(false)->after('round_number');
            $table->date('buffer_until')->nullable()->after('auto_scheduled');
        });
    }

    public function down(): void
    {
        Schema::table('group_matches', function (Blueprint $table) {
            $table->dropColumn(['round_number', 'auto_scheduled', 'buffer_until']);
        });
    }
};
