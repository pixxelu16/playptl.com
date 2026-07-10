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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('site_settings')->insert([
            [
                'key' => 'contact_phone',
                'value' => '+91 98765 43210',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'contact_email',
                'value' => 'player.one@example.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'contact_address',
                'value' => '18 Sector 22, Chandigarh, India',
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
        Schema::dropIfExists('site_settings');
    }
};
