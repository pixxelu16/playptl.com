<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed default categories
        \Illuminate\Support\Facades\DB::table('categories')->insert([
            ['name' => 'Singles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Doubles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mixed', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Youth', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
