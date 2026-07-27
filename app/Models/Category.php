<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'menu_order', 'type'];

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
