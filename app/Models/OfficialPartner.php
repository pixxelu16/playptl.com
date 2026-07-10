<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialPartner extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'website_url',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
