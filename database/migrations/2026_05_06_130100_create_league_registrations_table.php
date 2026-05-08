<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who applied for which league, assigned group card (from skill), payment state.
     */
    public function up(): void
    {
        Schema::create('league_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_card_id')->nullable()->constrained('group_cards')->nullOnDelete();
            $table->string('skill_level', 32)->nullable();
            $table->string('registration_type', 16)->nullable(); // singles | doubles
            $table->string('payment_status', 32)->default('pending')->index();
            $table->timestamps();

            $table->unique(['user_id', 'league_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_registrations');
    }
};
