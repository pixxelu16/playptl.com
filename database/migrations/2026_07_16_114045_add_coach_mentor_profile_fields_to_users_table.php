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
        Schema::table('users', function (Blueprint $table) {
            $table->text('profile_title_ad')->nullable();
            $table->text('profile_lessons')->nullable();
            $table->text('profile_bio')->nullable();
            $table->json('profile_locations')->nullable();
            $table->decimal('profile_rate', 10, 2)->unsigned()->nullable();
            $table->text('profile_rate_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_title_ad',
                'profile_lessons',
                'profile_bio',
                'profile_locations',
                'profile_rate',
                'profile_rate_details',
            ]);
        });
    }
};
