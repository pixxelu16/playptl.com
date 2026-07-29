<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'menu_order', 'type'];

    public function groupCards(): HasMany
    {
        return $this->hasMany(GroupCard::class);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->where('type', $type)
                ->orWhere('type', 'like', $type.',%')
                ->orWhere('type', 'like', '%,'.$type)
                ->orWhere('type', 'like', '%,'.$type.',%')
                ->orWhere('type', 'like', '%, '.$type)
                ->orWhere('type', 'like', '%, '.$type.',%');
        });
    }
}
