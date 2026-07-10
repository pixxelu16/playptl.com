<?php

namespace App\Support;

class MailBranding
{
    public static function appName(): string
    {
        return (string) config('app.name', 'Premier Tennis League');
    }

    public static function siteUrl(): string
    {
        return rtrim((string) config('app.url', ''), '/');
    }

    public static function logoUrl(): string
    {
        return asset('frontend/images/logo-2.png');
    }

    public static function loginUrl(): string
    {
        return route('login');
    }

    /** Primary brand green used across the site. */
    public static function colorPrimary(): string
    {
        return '#66A157';
    }

    public static function colorPrimaryDark(): string
    {
        return '#5a9048';
    }
}
