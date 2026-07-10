<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
    /**
     * @return array{phone: string, email: string, address: string}
     */
    public static function contact(): array
    {
        return Cache::remember('site_settings.contact', 3600, function (): array {
            $settings = static::query()
                ->whereIn('key', ['contact_phone', 'contact_email', 'contact_address'])
                ->pluck('value', 'key');

            return [
                'phone' => (string) ($settings['contact_phone'] ?? '+91 98765 43210'),
                'email' => (string) ($settings['contact_email'] ?? 'player.one@example.com'),
                'address' => (string) ($settings['contact_address'] ?? '18 Sector 22, Chandigarh, India'),
            ];
        });
    }

    /**
     * @return array{logo_path: string}
     */
    public static function header(): array
    {
        return Cache::remember('site_settings.header', 3600, function (): array {
            $logoPath = static::query()->where('key', 'header_logo_path')->value('value');

            return [
                'logo_path' => (string) ($logoPath ?? 'frontend/images/home-logo.png'),
            ];
        });
    }

    /**
     * @return array{logo_path: string, description: string}
     */
    public static function footer(): array
    {
        return Cache::remember('site_settings.footer', 3600, function (): array {
            $settings = static::query()
                ->whereIn('key', ['footer_logo_path', 'footer_description'])
                ->pluck('value', 'key');

            return [
                'logo_path' => (string) ($settings['footer_logo_path'] ?? 'frontend/images/home-logo.png'),
                'description' => (string) ($settings['footer_description'] ?? "The region's premier competitive tennis league. Forging champions, building community, raising funds for causes that matter."),
            ];
        });
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        static::forgetCaches();
    }

    protected static function forgetCaches(): void
    {
        Cache::forget('site_settings.contact');
        Cache::forget('site_settings.header');
        Cache::forget('site_settings.footer');
    }
}
