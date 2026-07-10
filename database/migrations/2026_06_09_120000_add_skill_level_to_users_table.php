<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'skill_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('skill_level', 32)->nullable()->after('sex');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'skill_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('skill_level');
            });
        }
    }
};
