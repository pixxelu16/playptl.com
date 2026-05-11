<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional preferred date/time for play location scheduling on the profile.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'preferred_play_date')) {
                $table->date('preferred_play_date')->nullable()->after('home_court');
            }
            if (! Schema::hasColumn('users', 'preferred_play_time')) {
                $table->string('preferred_play_time', 5)->nullable()->after('preferred_play_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['preferred_play_time', 'preferred_play_date'],
                fn ($c) => Schema::hasColumn('users', $c)
            ));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
