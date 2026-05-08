<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop old pivot table group_card_group if it still exists.
     * New code uses the standard group_group_card pivot instead.
     */
    public function up(): void
    {
        if (Schema::hasTable('group_card_group')) {
            Schema::drop('group_card_group');
        }
    }

    public function down(): void
    {
        // No need to recreate the legacy table; group_group_card is the canonical pivot.
    }
};

