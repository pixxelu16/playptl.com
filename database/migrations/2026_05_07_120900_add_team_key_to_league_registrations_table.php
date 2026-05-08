<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('league_registrations', 'team_key')) {
                $table->string('team_key', 64)->nullable()->after('registration_type')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('league_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('league_registrations', 'team_key')) {
                $table->dropColumn('team_key');
            }
        });
    }
};

