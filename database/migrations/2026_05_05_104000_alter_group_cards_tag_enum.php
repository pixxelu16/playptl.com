<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        DB::table('group_cards')->update([
            'tag' => DB::raw("CASE
                WHEN LOWER(tag) IN ('single', 'singles') THEN 'single'
                WHEN LOWER(tag) IN ('double', 'doubles') THEN 'doubles'
                WHEN LOWER(tag) = 'mixed' THEN 'mixed'
                WHEN LOWER(tag) = 'youth' THEN 'youth'
                ELSE 'single'
            END"),
        ]);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE group_cards MODIFY tag ENUM('single','doubles','mixed','youth') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE group_cards MODIFY tag VARCHAR(40) NOT NULL');
        }
    }
};
