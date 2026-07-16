<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.website', function ($view): void {
            $view->with('contactSettings', SiteSetting::contact());
            $view->with('headerSettings', SiteSetting::header());
            $view->with('footerSettings', SiteSetting::footer());
        });

        // Implicitly grant "Super Admin" role all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Override Laravel mail config from DB-managed SMTP settings
        $this->configureMailFromDatabase();
    }

    /**
     * Read SMTP settings from the site_settings table and apply them
     * to Laravel's live mail configuration. Silently skips if the
     * table is not yet available (e.g. during fresh migrations).
     */
    protected function configureMailFromDatabase(): void
    {
        try {
            $smtp = SiteSetting::smtp();

            $mailer = $smtp['mailer'] ?: 'smtp';

            // Override the default mailer
            config(['mail.default' => $mailer]);

            // Override the smtp mailer transport settings
            config([
                "mail.mailers.{$mailer}.transport"   => $mailer,
                "mail.mailers.{$mailer}.host"         => $smtp['host'],
                "mail.mailers.{$mailer}.port"         => (int) $smtp['port'],
                "mail.mailers.{$mailer}.encryption"   => ($smtp['encryption'] === 'null' || $smtp['encryption'] === '') ? null : $smtp['encryption'],
                "mail.mailers.{$mailer}.username"     => $smtp['username'] ?: null,
                "mail.mailers.{$mailer}.password"     => $smtp['password'] ?: null,
            ]);

            // Override the global from address
            config([
                'mail.from.address' => $smtp['from_address'],
                'mail.from.name'    => $smtp['from_name'],
            ]);
        } catch (\Throwable) {
            // Table may not exist yet during migrations — fail silently
        }
    }
}
