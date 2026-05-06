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
    public static function activeLeagues(): Collection
    {
        if (! Schema::hasTable('leagues')) {
            return collect();
        }

        return League::query()
            ->select(['id', 'name', 'slug'])
            ->where('stats', 'active')
            ->orderBy('name')
            ->get();
    }
}
