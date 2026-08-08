<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_number',
        'last_updated',
        'changelog',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];
}
