<?php

namespace App\Enums;

enum GroupPlayoffFormat: string
{
    /** 16 in Pre-Pre-Q, top 8 direct Round of 16 (home). */
    case PrePreQR16 = 'pre_pre_q_r16';

    /** Top 4 direct to quarterfinals; ranks 5–12 play Round of 16. */
    case Top4QuarterRestR16 = 'top4_quarter_rest_r16';

    /** Top 16 all enter Round of 16 (Pre-Q pairings). */
    case RoundOf16 = 'round_of_16';

    /** Top N go straight to quarterfinals only (no Pre-Pre-Q, no Round of 16). */
    case DirectQuarter = 'direct_quarter';

    public function label(): string
    {
        return match ($this) {
            self::PrePreQR16 => 'Pre-Pre-Q + Round of 16',
            self::Top4QuarterRestR16 => 'Top 4 → Quarter, rest Round of 16',
            self::RoundOf16 => 'Round of 16 only',
            self::DirectQuarter => 'Direct Quarter only',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PrePreQR16 => 'Ranks 1–8: direct Round of 16 (home). Ranks 9–24: Pre-Pre-Q (16 players, 8 matches). Everyone else eliminated.',
            self::Top4QuarterRestR16 => 'Ranks 1–4: direct quarterfinals. Ranks 5–12: Round of 16 (8 players). Everyone else eliminated.',
            self::RoundOf16 => '16 players in Round of 16 (8 matches). Everyone else eliminated.',
            self::DirectQuarter => '8 players direct to quarterfinals (4 matches). No Round of 16 or Pre-Pre-Q.',
        };
    }

    /**
     * @return list<array{value: string, label: string, description: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $f) => [
                'value' => $f->value,
                'label' => $f->label(),
                'description' => $f->description(),
            ],
            self::cases(),
        );
    }

    public static function tryFromStored(?string $value): ?self
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return self::tryFrom($value);
    }

    public static function resolveOrDefault(?string $value): self
    {
        return self::tryFromStored($value) ?? self::RoundOf16;
    }
}
