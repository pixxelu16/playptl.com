<?php

namespace App\Enums;

enum PlayoffQualifierPath: string
{
    case PrePreQ = 'pre_pre_q';
    case RoundOf16 = 'r16';
    case Quarter = 'quarter';
    case Eliminated = 'eliminated';

    /** @deprecated DB legacy — same as {@see self::Quarter} */
    public const LEGACY_DIRECT_QF = 'direct_qf';

    /** @deprecated DB legacy — same as {@see self::RoundOf16} */
    public const LEGACY_PRE_Q = 'pre_q';

    public function label(): string
    {
        return match ($this) {
            self::PrePreQ => 'Pre-Pre-Q',
            self::RoundOf16 => 'Pre-Q / Round of 16',
            self::Quarter => 'Quarter',
            self::Eliminated => 'Eliminated',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        $order = [
            self::PrePreQ,
            self::RoundOf16,
            self::Quarter,
            self::Eliminated,
        ];

        return array_map(
            fn (self $p) => ['value' => $p->value, 'label' => $p->label()],
            $order
        );
    }

    public static function normalizeStored(?string $path): string
    {
        $path = trim((string) $path);

        return match ($path) {
            self::LEGACY_DIRECT_QF => self::Quarter->value,
            self::LEGACY_PRE_Q => self::RoundOf16->value,
            default => $path,
        };
    }

    /**
     * @return list<string>
     */
    public static function validValues(): array
    {
        $values = array_map(fn (self $p) => $p->value, self::cases());
        $values[] = self::LEGACY_DIRECT_QF;
        $values[] = self::LEGACY_PRE_Q;

        return $values;
    }

    public static function isQuarterPath(string $path): bool
    {
        return self::normalizeStored($path) === self::Quarter->value;
    }

    public static function isRoundOf16Path(string $path): bool
    {
        return self::normalizeStored($path) === self::RoundOf16->value;
    }
}
