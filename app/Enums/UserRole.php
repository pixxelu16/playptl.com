<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Organiser = 'organiser';
    case Player = 'player';

    public function dashboardRouteName(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Organiser => 'organiser.dashboard',
            self::Player => 'player.dashboard',
        };
    }
}
