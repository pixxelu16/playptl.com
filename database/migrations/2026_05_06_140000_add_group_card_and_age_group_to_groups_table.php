<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Groups belong to a GroupCard and optionally an age bracket key.
     * This enables admin hierarchy: League -> GroupCard -> Age bracket -> Groups.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('group_card_id')->nullable()->after('id')->constrained('group_cards')->nullOnDelete();
            $table->string('age_group_key', 32)->nullable()->after('group_card_id');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['group_card_id']);
            $table->dropColumn(['group_card_id', 'age_group_key']);
        });
    }
};

