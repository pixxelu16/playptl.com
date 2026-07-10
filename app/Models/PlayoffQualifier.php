<?php

namespace App\Models;

use App\Enums\PlayoffQualifierPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayoffQualifier extends Model
{
    protected $fillable = [
        'league_id',
        'group_card_id',
        'age_group_key',
        'user_id',
        'path',
        'needs_pre_match',
        'qf_slot',
        'r16_slot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'needs_pre_match' => 'boolean',
            'qf_slot' => 'integer',
            'r16_slot' => 'integer',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function groupCard(): BelongsTo
    {
        return $this->belongsTo(GroupCard::class, 'group_card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pathEnum(): ?PlayoffQualifierPath
    {
        $raw = trim((string) ($this->path ?? ''));

        return $raw !== '' ? PlayoffQualifierPath::tryFrom($raw) : null;
    }

    public function pathLabel(): string
    {
        return $this->pathEnum()?->label() ?? '—';
    }
}
