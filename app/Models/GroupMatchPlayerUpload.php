<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMatchPlayerUpload extends Model
{
    protected $fillable = [
        'group_match_id',
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

    public function groupMatch(): BelongsTo
    {
        return $this->belongsTo(GroupMatch::class, 'group_match_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
