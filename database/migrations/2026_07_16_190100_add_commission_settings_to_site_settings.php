<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $keys = ['mentor_commission_percent', 'coach_commission_percent'];

        foreach ($keys as $key) {
            if (! DB::table('site_settings')->where('key', $key)->exists()) {
                DB::table('site_settings')->insert([
                    'key'        => $key,
                    'value'      => '20',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->whereIn('key', ['mentor_commission_percent', 'coach_commission_percent'])
            ->delete();
    }
};
