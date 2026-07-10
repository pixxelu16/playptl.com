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
        Schema::create('official_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path');
            $table->string('website_url')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $rows = [];

        for ($i = 1; $i <= 9; $i++) {
            $rows[] = [
                'name' => 'Partner '.$i,
                'logo_path' => 'frontend/images/partner-'.$i.'.png',
                'website_url' => null,
                'display_order' => $i,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('official_partners')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_partners');
    }
};
