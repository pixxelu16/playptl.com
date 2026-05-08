<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'league_id',
        'group_card_id',
        'group_id',
        'skill_level',
        'age_group_key',
        'registration_type',
        'team_key',
        'payment_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function groupCard(): BelongsTo
    {
        return $this->belongsTo(GroupCard::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
