<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Skill extends Model
{
    protected $fillable = ['value', 'display_order'];

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    public static function allSkills(): array
    {
        return Cache::remember('skills.all', 3600, function (): array {
            return static::query()
                ->orderBy('display_order')
                ->orderBy('value')
                ->pluck('value')
                ->toArray();
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget('skills.all');
    }
}
