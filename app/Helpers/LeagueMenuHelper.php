<?php

namespace App\Helpers;

use App\Models\League;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LeagueMenuHelper
{
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

}
