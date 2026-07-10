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
        $now = now();

        DB::table('site_settings')->insert([
            [
                'key' => 'footer_logo_path',
                'value' => 'frontend/images/home-logo.png',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'footer_description',
                'value' => "The region's premier competitive tennis league. Forging champions, building community, raising funds for causes that matter.",
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'footer_logo_path',
            'footer_description',
        ])->delete();
    }
};
