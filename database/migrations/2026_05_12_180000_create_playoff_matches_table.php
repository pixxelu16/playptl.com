<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-elimination playoff bracket for a league sub group (8 players: QF → SF → F).
     */
    public function up(): void
    {
        Schema::create('playoff_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_card_id')->constrained('group_cards')->cascadeOnDelete();
            $table->string('age_group_key', 64)->default('');
            $table->string('round', 8);
            $table->unsignedTinyInteger('slot');
            $table->foreignId('home_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('away_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('score', 64)->nullable();
            $table->string('winner_side', 8)->nullable();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('match_date')->nullable();
            $table->string('start_time', 32)->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'group_card_id', 'age_group_key', 'round', 'slot'], 'playoff_matches_bracket_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_matches');
    }
};
