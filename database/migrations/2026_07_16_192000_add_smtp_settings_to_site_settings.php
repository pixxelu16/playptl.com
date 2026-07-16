<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            'smtp_mailer'       => env('MAIL_MAILER', 'smtp'),
            'smtp_host'         => env('MAIL_HOST', '127.0.0.1'),
            'smtp_port'         => env('MAIL_PORT', '2525'),
            'smtp_encryption'   => env('MAIL_SCHEME', 'null'),
            'smtp_username'     => env('MAIL_USERNAME', ''),
            'smtp_password'     => env('MAIL_PASSWORD', ''),
            'smtp_from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'smtp_from_name'    => env('MAIL_FROM_NAME', config('app.name', 'playptl')),
        ];

        foreach ($defaults as $key => $value) {
            if (! DB::table('site_settings')->where('key', $key)->exists()) {
                DB::table('site_settings')->insert([
                    'key'        => $key,
                    'value'      => (string) ($value ?? ''),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->whereIn('key', [
                'smtp_mailer', 'smtp_host', 'smtp_port', 'smtp_encryption',
                'smtp_username', 'smtp_password', 'smtp_from_address', 'smtp_from_name',
            ])
            ->delete();
    }
};
