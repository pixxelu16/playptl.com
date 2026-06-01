<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharityDonation extends Model
{
    protected $fillable = [
        'user_id',
        'charity_cause_id',
        'donation_type',
        'donor_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'amount',
        'quantity',
        'material_detail',
        'currency',
        'status',
        'transaction_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'quantity' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function charityCause(): BelongsTo
    {
        return $this->belongsTo(CharityCause::class);
    }
}
