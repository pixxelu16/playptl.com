<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $roles = collect(UserRole::cases())
            ->map(fn (UserRole $role) => "'{$role->value}'")
            ->implode(', ');

        DB::statement("ALTER TABLE users MODIFY role ENUM({$roles}) NOT NULL DEFAULT '".UserRole::Player->value."'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NOT NULL DEFAULT '".UserRole::Player->value."'");
    }
};
