<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayoffMatchPlayerUpload extends Model
{
    protected $fillable = [
        'playoff_match_id',
        'uploaded_by_user_id',
        'upload_date',
        'image_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'upload_date' => 'date',
        ];
    }

    public function playoffMatch(): BelongsTo
    {
        return $this->belongsTo(PlayoffMatch::class, 'playoff_match_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
