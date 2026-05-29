<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharityDonation extends Model
{
    protected $fillable = [
        'user_id',
        'donor_name',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
