<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\UserRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = collect(UserRole::cases())
            ->map(fn (UserRole $role) => "'{$role->value}'")
            ->implode(',');

        DB::statement("ALTER TABLE users MODIFY role ENUM({$roles}) NOT NULL DEFAULT '".UserRole::Player->value."'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NOT NULL DEFAULT '".UserRole::Player->value."'");
    }
};
