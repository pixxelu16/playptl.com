<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scheduled fixtures for a roster group (singles or doubles), date/time/venue.
     */
    public function up(): void
    {
        Schema::create('group_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_card_id')->constrained('group_cards')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('format', 16)->default('singles'); // singles | doubles

            $table->foreignId('home_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('away_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('home_partner_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('away_partner_user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('home_seed')->nullable();
            $table->unsignedTinyInteger('away_seed')->nullable();

            $table->date('match_date');
            $table->string('start_time', 32)->nullable();
            $table->string('venue', 255)->nullable();
            $table->string('court', 64)->nullable();
            $table->string('score', 64)->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['league_id', 'group_card_id', 'group_id', 'match_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_matches');
    }
};
