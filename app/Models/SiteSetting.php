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

    /**
     * @return array{mode: string, currency: string, test_publishable_key: string, test_secret_key: string, live_publishable_key: string, live_secret_key: string}
     */
    public static function stripe(): array
    {
        return Cache::remember('site_settings.stripe', 3600, function (): array {
            $settings = static::query()
                ->whereIn('key', [
                    'stripe_mode',
                    'stripe_currency',
                    'stripe_test_publishable_key',
                    'stripe_test_secret_key',
                    'stripe_live_publishable_key',
                    'stripe_live_secret_key',
                ])
                ->pluck('value', 'key');

            return [
                'mode' => (string) ($settings['stripe_mode'] ?? 'test'),
                'currency' => (string) ($settings['stripe_currency'] ?? 'USD'),
                'test_publishable_key' => (string) ($settings['stripe_test_publishable_key'] ?? ''),
                'test_secret_key' => (string) ($settings['stripe_test_secret_key'] ?? ''),
                'live_publishable_key' => (string) ($settings['stripe_live_publishable_key'] ?? ''),
                'live_secret_key' => (string) ($settings['stripe_live_secret_key'] ?? ''),
            ];
        });
    }

    public static function stripePublishableKey(): string
    {
        $stripe = static::stripe();
        return $stripe['mode'] === 'live' ? $stripe['live_publishable_key'] : $stripe['test_publishable_key'];
    }

    public static function stripeSecretKey(): string
    {
        $stripe = static::stripe();
        return $stripe['mode'] === 'live' ? $stripe['live_secret_key'] : $stripe['test_secret_key'];
    }

    public static function stripeCurrency(): string
    {
        $stripe = static::stripe();
        return $stripe['currency'];
    }

    /**
     * Return the symbol for the configured Stripe currency.
     * Falls back to the currency code itself if unmapped.
     */
    public static function currencySymbol(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'CA$',
            'AUD' => 'A$',
            'INR' => '₹',
        ];

        $code = strtoupper(static::stripeCurrency());
        return $symbols[$code] ?? $code;
    }

    public static function mentorCommissionPercent(): float
    {
        return (float) Cache::remember('site_settings.mentor_commission', 3600, function (): string {
            return (string) (static::query()->where('key', 'mentor_commission_percent')->value('value') ?? '20');
        });
    }

    public static function coachCommissionPercent(): float
    {
        return (float) Cache::remember('site_settings.coach_commission', 3600, function (): string {
            return (string) (static::query()->where('key', 'coach_commission_percent')->value('value') ?? '20');
        });
    }

    /**
     * Return all SMTP settings as an associative array.
     */
    public static function smtp(): array
    {
        return Cache::remember('site_settings.smtp', 3600, function (): array {
            $rows = static::query()
                ->whereIn('key', [
                    'smtp_mailer', 'smtp_host', 'smtp_port', 'smtp_encryption',
                    'smtp_username', 'smtp_password', 'smtp_from_address', 'smtp_from_name',
                ])
                ->pluck('value', 'key')
                ->toArray();

            return [
                'mailer'       => $rows['smtp_mailer']       ?? env('MAIL_MAILER', 'smtp'),
                'host'         => $rows['smtp_host']         ?? env('MAIL_HOST', '127.0.0.1'),
                'port'         => $rows['smtp_port']         ?? env('MAIL_PORT', '587'),
                'encryption'   => $rows['smtp_encryption']   ?? env('MAIL_SCHEME', 'tls'),
                'username'     => $rows['smtp_username']     ?? env('MAIL_USERNAME', ''),
                'password'     => $rows['smtp_password']     ?? env('MAIL_PASSWORD', ''),
                'from_address' => $rows['smtp_from_address'] ?? env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'from_name'    => $rows['smtp_from_name']    ?? env('MAIL_FROM_NAME', config('app.name')),
            ];
        });
    }

    protected static function forgetCaches(): void
    {
        Cache::forget('site_settings.contact');
        Cache::forget('site_settings.header');
        Cache::forget('site_settings.footer');
        Cache::forget('site_settings.stripe');
        Cache::forget('site_settings.mentor_commission');
        Cache::forget('site_settings.coach_commission');
        Cache::forget('site_settings.smtp');
    }
}
