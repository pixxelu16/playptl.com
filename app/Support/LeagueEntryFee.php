<?php

namespace App\Support;

use App\Models\League;
use Illuminate\Support\Collection;

final class LeagueEntryFee
{
    public static function defaultSinglesCents(): int
    {
        return max(0, (int) config('services.stripe.singles_amount_cents', 3000));
    }

    public static function defaultDoublesCents(): int
    {
        return max(0, (int) config('services.stripe.doubles_amount_cents', 4500));
    }

    public static function singlesCents(?League $league): int
    {
        if ($league !== null && $league->singles_entry_fee_cents !== null) {
            return max(0, (int) $league->singles_entry_fee_cents);
        }

        return self::defaultSinglesCents();
    }

    public static function doublesCents(?League $league): int
    {
        if ($league !== null && $league->doubles_entry_fee_cents !== null) {
            return max(0, (int) $league->doubles_entry_fee_cents);
        }

        return self::defaultDoublesCents();
    }

    public static function centsForTab(League $league, string $tab): int
    {
        return $tab === 'doubles'
            ? self::doublesCents($league)
            : self::singlesCents($league);
    }

    public static function formatDollars(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public static function dollarsInputValue(?League $league, string $tab): string
    {
        $cents = $tab === 'doubles'
            ? ($league?->doubles_entry_fee_cents ?? self::defaultDoublesCents())
            : ($league?->singles_entry_fee_cents ?? self::defaultSinglesCents());

        return self::formatDollars((int) $cents);
    }

    /**
     * @param  iterable<int, League>  $leagues
     * @return array<string, array{singles: string, doubles: string}>
     */
    public static function mapForLeagues(iterable $leagues): array
    {
        $map = [];
        foreach ($leagues as $league) {
            if (! $league instanceof League) {
                continue;
            }
            $map[(string) $league->id] = [
                'singles' => self::formatDollars(self::singlesCents($league)),
                'doubles' => self::formatDollars(self::doublesCents($league)),
            ];
        }

        $map['default'] = [
            'singles' => self::formatDollars(self::defaultSinglesCents()),
            'doubles' => self::formatDollars(self::defaultDoublesCents()),
        ];

        return $map;
    }

    /**
     * @param  Collection<int, League>|iterable<int, League>  $leagues
     */
    public static function mapForLeaguesCollection(iterable $leagues): array
    {
        return self::mapForLeagues($leagues);
    }

    public static function centsFromDollarsInput(mixed $dollars): int
    {
        $normalized = is_string($dollars) ? str_replace(',', '', trim($dollars)) : $dollars;

        return max(0, (int) round(((float) $normalized) * 100));
    }
}
