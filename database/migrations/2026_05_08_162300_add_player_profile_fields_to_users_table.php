<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('avatar_path');
            }
            if (! Schema::hasColumn('users', 'home_court')) {
                $table->string('home_court')->nullable()->after('state');
            }
            if (! Schema::hasColumn('users', 'dominant_hand')) {
                $table->string('dominant_hand', 32)->nullable()->after('home_court');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['date_of_birth', 'home_court', 'dominant_hand'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
