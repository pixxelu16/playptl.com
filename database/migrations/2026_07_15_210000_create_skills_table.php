<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('value', 32)->unique();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $defaultSkills = ['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', 'not-sure'];
        
        foreach ($defaultSkills as $index => $skill) {
            DB::table('skills')->insert([
                'value' => $skill,
                'display_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasColumn('group_cards', 'skill_level_match')) {
            Schema::table('group_cards', function (Blueprint $table) {
                $table->string('skill_level_match', 255)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('group_cards', 'skill_level_match')) {
            Schema::table('group_cards', function (Blueprint $table) {
                $table->string('skill_level_match', 32)->nullable()->change();
            });
        }

        Schema::dropIfExists('skills');
    }
};
