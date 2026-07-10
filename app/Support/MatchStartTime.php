<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Normalizes match start_time for HTML time inputs (H:i) and human-readable display.
 */
final class MatchStartTime
{
    /**
     * Value for <input type="time"> (24h HH:MM). Empty string if unknown.
     */
    public static function toInputValue(?string $stored): string
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return '';
        }

        if (preg_match('/^\d{1,2}$/', $stored) && (int) $stored >= 0 && (int) $stored <= 23) {
            return sprintf('%02d:00', (int) $stored);
        }

        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $stored, $m)) {
            return sprintf('%02d:%s', (int) $m[1], $m[2]);
        }

        try {
            return Carbon::parse($stored)->format('H:i');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Normalize request value to H:i for persistence, or empty if invalid.
     */
    public static function normalizeFromRequest(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^\d{1,2}$/', $raw) && (int) $raw >= 0 && (int) $raw <= 23) {
            return sprintf('%02d:00', (int) $raw);
        }

        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $raw, $m)) {
            return sprintf('%02d:%s', (int) $m[1], $m[2]);
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Friendly display (e.g. "10:00 AM") for cards and emails.
     */
    public static function formatDisplay(?string $stored): string
    {
        $hi = self::toInputValue($stored);
        if ($hi === '') {
            return trim((string) $stored) !== '' ? trim((string) $stored) : '';
        }

        try {
            return Carbon::createFromFormat('H:i', $hi)->format('g:i A');
        } catch (\Throwable) {
            return (string) $stored;
        }
    }
}
