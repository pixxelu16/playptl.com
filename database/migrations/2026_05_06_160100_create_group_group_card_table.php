<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure pivot table exists for GroupCard<->Group belongsToMany.
     *
     * Why: the earlier migration file was edited after being marked as run, so
     * the expected table may be missing on some databases.
     */
    public function up(): void
    {
        if (! Schema::hasTable('group_group_card')) {
            Schema::create('group_group_card', function (Blueprint $table) {
                $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
                $table->foreignId('group_card_id')->constrained('group_cards')->cascadeOnDelete();
                $table->primary(['group_id', 'group_card_id']);
            });
        }

        // If an old pivot table exists, migrate data forward.
        if (Schema::hasTable('group_card_group')) {
            $rows = DB::table('group_card_group')->select(['group_id', 'group_card_id'])->get();
            foreach ($rows as $row) {
                DB::table('group_group_card')->updateOrInsert(
                    ['group_id' => $row->group_id, 'group_card_id' => $row->group_card_id],
                    []
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_group_card');
    }
};

