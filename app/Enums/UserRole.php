<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Organiser = 'organiser';
    case Player = 'player';
    case Mentor = 'mentor';
    case Coach = 'coach';
    case Student = 'student';

    public function dashboardRouteName(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Organiser => 'organiser.dashboard',
            self::Player => 'player.dashboard',
            self::Mentor => 'mentor.dashboard',
            self::Coach => 'coach.dashboard',
            self::Student => 'student.dashboard',
        };
    }
}
