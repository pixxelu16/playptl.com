<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playoff_qualifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_card_id')->constrained('group_cards')->cascadeOnDelete();
            $table->string('age_group_key', 64)->default('');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path', 32)->default('');
            $table->boolean('needs_pre_match')->default(false);
            $table->unsignedTinyInteger('qf_slot')->nullable();
            $table->unsignedTinyInteger('r16_slot')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(
                ['league_id', 'group_card_id', 'age_group_key', 'user_id'],
                'playoff_qualifiers_division_user_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_qualifiers');
    }
};
