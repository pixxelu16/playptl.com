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
    }
}
