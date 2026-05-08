<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assign Groups to Group Cards (admin-managed).
     */
    public function up(): void
    {
        // Use Laravel's default alphabetical pivot naming: group_group_card
        Schema::create('group_group_card', function (Blueprint $table) {
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('group_card_id')->constrained('group_cards')->cascadeOnDelete();
            $table->primary(['group_id', 'group_card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_group_card');
    }
};

