<?php

namespace App\Helpers;

use App\Models\League;
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
     * Tournaments open for player registration (active + upcoming).
     *
     * @return Collection<int, League>
     */
    public static function registrationLeagues(bool $latestFirst = false): Collection
    {
        if (! Schema::hasTable('leagues')) {
            return collect();
        }

        $query = League::query()
            ->select(['id', 'name', 'slug', 'singles_entry_fee_cents', 'doubles_entry_fee_cents'])
            ->whereIn('stats', self::REGISTRATION_STATUSES)
            ->whereNull('finished_at');

        if ($latestFirst) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('name');
        }

        return $query->get();
    }

    public static function acceptsRegistration(League $league): bool
    {
        return $league->finished_at === null
            && in_array((string) ($league->stats ?? ''), self::REGISTRATION_STATUSES, true);
    }

}
