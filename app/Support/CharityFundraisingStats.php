<?php

namespace App\Support;

use App\Models\CharityDonation;

class CharityFundraisingStats
{
    /**
     * @return array{
     *     total_raised: float,
     *     total_raised_formatted: string,
     *     bar_scale_max: float,
     *     bar_scale_max_formatted: string,
     *     progress_percent: int,
     *     progress_label: string
     * }
     */
    public static function current(): array
    {
        $totalRaised = (float) CharityDonation::query()
            ->where('donation_type', 'money')
            ->where('status', 'completed')
            ->sum('amount');

        $barScaleMax = self::barScaleMax($totalRaised);
        $progressPercent = $barScaleMax > 0
            ? (int) min(100, round(($totalRaised / $barScaleMax) * 100))
            : 0;

        return [
            'total_raised' => $totalRaised,
            'total_raised_formatted' => self::formatMoney($totalRaised),
            'bar_scale_max' => $barScaleMax,
            'bar_scale_max_formatted' => self::formatCompactMoney($barScaleMax),
            'progress_percent' => $progressPercent,
            'progress_label' => self::formatMoney($totalRaised).' raised',
        ];
    }

    public static function barScaleMax(float $totalRaised): float
    {
        $minimum = max($totalRaised, 1.0);

        $milestones = [100, 250, 500, 1000, 2500, 5000, 10000, 25000, 50000, 100000, 250000, 500000, 1000000];

        foreach ($milestones as $milestone) {
            if ($milestone >= $minimum) {
                return (float) $milestone;
            }
        }

        return (float) (ceil($minimum / 1000000) * 1000000);
    }

    public static function formatMoney(float $amount): string
    {
        return '$'.number_format($amount, $amount == floor($amount) ? 0 : 2);
    }

    public static function formatCompactMoney(float $amount): string
    {
        if ($amount >= 1000000) {
            $value = $amount / 1000000;

            return '$'.rtrim(rtrim(number_format($value, 1), '0'), '.').'M';
        }

        if ($amount >= 1000) {
            $value = $amount / 1000;

            return '$'.rtrim(rtrim(number_format($value, $value >= 10000 ? 0 : 1), '0'), '.').'K';
        }

        return self::formatMoney($amount);
    }
}
