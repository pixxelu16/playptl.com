<?php

namespace App\Helpers;

use App\Models\League;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LeagueMenuHelper
{
    /** @var list<string> */
    public const REGISTRATION_STATUSES = ['active', 'upcoming'];

    /**
     * @return Collection<int, League>
     */
    public static function activeLeagues(bool $latestFirst = false): Collection
    {
        if (! Schema::hasTable('leagues')) {
            return collect();
        }

        $query = League::query()
            ->select(['id', 'name', 'slug'])
            ->where('show_in_menu', true)
            ->where('stats', 'active')
            ->whereNull('finished_at');

        if ($latestFirst) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('name');
        }

        return $query->get();
    }

    /**
     * Tournaments open for player registration (active + upcoming and within date window).
     *
     * @return Collection<int, League>
     */
    public static function registrationLeagues(bool $latestFirst = false): Collection
    {
        if (! Schema::hasTable('leagues')) {
            return collect();
        }

        $today = now()->startOfDay()->toDateString();

        $query = League::query()
            ->select([
                'id',
                'name',
                'slug',
                'singles_entry_fee_cents',
                'doubles_entry_fee_cents',
                'start_date',
                'end_date',
                'registration_deadline',
                'stats',
                'finished_at',
            ])
            ->whereIn('stats', self::REGISTRATION_STATUSES)
            ->whereNull('finished_at')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('registration_deadline')
                    ->orWhereDate('registration_deadline', '>=', $today);
            });

        if ($latestFirst) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('name');
        }

        return $query->get();
    }

    /**
     * @return Collection<int, League>
     */
    public static function latestLeagues(int $limit = 5): Collection
    {
        if (! Schema::hasTable('leagues')) {
            return collect();
        }

        return League::query()
            ->select(['id', 'name', 'slug'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public static function acceptsRegistration(League $league, ?Carbon $today = null): bool
    {
        if ($league->finished_at !== null) {
            return false;
        }

        if (! in_array((string) ($league->stats ?? ''), self::REGISTRATION_STATUSES, true)) {
            return false;
        }

        $today = ($today ?? now())->copy()->startOfDay();

        if ($league->start_date !== null && $today->lt($league->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($league->end_date !== null && $today->gt($league->end_date->copy()->startOfDay())) {
            return false;
        }

        if ($league->registration_deadline !== null && $today->gt($league->registration_deadline->copy()->startOfDay())) {
            return false;
        }

        return true;
    }

}
