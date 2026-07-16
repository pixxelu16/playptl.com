<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Booking extends Model
{
    const STATUS_PENDING   = 'pending';
    const STATUS_ACCEPTED  = 'accepted';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    const PAYOUT_UNPAID = 'unpaid';
    const PAYOUT_PAID   = 'paid';

    protected $fillable = [
        'student_id',
        'provider_id',
        'provider_type',
        'message',
        'student_location',
        'student_phone',
        'from_date',
        'to_date',
        'booking_time',
        'hours_per_day',
        'total_days',
        'total_hours',
        'hourly_rate',
        'total_amount',
        'commission_rate',
        'commission_amount',
        'provider_amount',
        'status',
        'stripe_charge_id',
        'stripe_refund_id',
        'payout_status',
        'payout_paid_at',
        'notes',
    ];

    protected $casts = [
        'from_date'      => 'date',
        'to_date'        => 'date',
        'payout_paid_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForStudent(Builder $query, int $userId): Builder
    {
        return $query->where('student_id', $userId);
    }

    public function scopeForProvider(Builder $query, int $userId): Builder
    {
        return $query->where('provider_id', $userId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaid(): bool
    {
        return $this->total_amount > 0 && $this->stripe_charge_id !== null;
    }

    public function canBeCancelledByStudent(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Pending',
            self::STATUS_ACCEPTED  => 'Accepted',
            self::STATUS_REJECTED  => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
            default                => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'yellow',
            self::STATUS_ACCEPTED  => 'green',
            self::STATUS_REJECTED  => 'red',
            self::STATUS_CANCELLED => 'gray',
            self::STATUS_COMPLETED => 'blue',
            default                => 'gray',
        };
    }

    /**
     * Calculate booking totals from given inputs.
     *
     * @return array{total_days: int, total_hours: float, total_amount: float, commission_amount: float, provider_amount: float}
     */
    public static function calculateTotals(
        string $fromDate,
        string $toDate,
        float $hoursPerDay,
        float $hourlyRate,
        float $commissionRate
    ): array {
        $from      = \Carbon\Carbon::parse($fromDate);
        $to        = \Carbon\Carbon::parse($toDate);
        $totalDays = (int) $from->diffInDays($to) + 1; // inclusive
        $totalHours  = $totalDays * $hoursPerDay;
        $totalAmount = round($totalHours * $hourlyRate, 2);
        $providerAmount   = round($totalAmount * ($commissionRate / 100), 2);
        $commissionAmount = round($totalAmount - $providerAmount, 2);

        return [
            'total_days'       => $totalDays,
            'total_hours'      => $totalHours,
            'total_amount'     => $totalAmount,
            'commission_amount'=> $commissionAmount,
            'provider_amount'  => $providerAmount,
        ];
    }
}
