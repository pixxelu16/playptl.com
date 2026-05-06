<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('group_cards')) {
            return;
        }

        $driver = DB::getDriverName();

        try {
            if ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS group_cards_slug_unique');
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE group_cards DROP INDEX group_cards_slug_unique');
            }
        } catch (Throwable) {
            // Unique index may already be absent.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('group_cards')) {
            return;
        }

        $driver = DB::getDriverName();

        try {
            if ($driver === 'sqlite') {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS group_cards_slug_unique ON group_cards (slug)');
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE group_cards ADD UNIQUE INDEX group_cards_slug_unique (slug)');
            }
        } catch (Throwable) {
            // Unique index may already exist.
        }
    }
};
