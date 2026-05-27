<?php

namespace App\Support;

/**
 * Normalize admin match result fields (score + winner) including walkover.
 */
final class MatchResultInput
{
    public const RESULT_NORMAL = 'normal';

    public const RESULT_WALKOVER = 'walkover';

    public const WALKOVER_SCORE_LABEL = 'Walkover';

    public const MAX_SET_BOXES = 3;

    /**
     * @return array<string, list<string>>
     */
    public static function setFieldValidationRules(): array
    {
        $rules = [];
        for ($n = 1; $n <= self::MAX_SET_BOXES; $n++) {
            $rules["set_{$n}_home"] = ['nullable', 'integer', 'min:0', 'max:99'];
            $rules["set_{$n}_away"] = ['nullable', 'integer', 'min:0', 'max:99'];
        }

        return $rules;
    }

    /**
     * Build stored score string from set_1_home / set_1_away … fields.
     *
     * @param  array<string, mixed>  $input
     */
    public static function scoreFromSetFields(array $input): string
    {
        $parts = [];
        for ($n = 1; $n <= self::MAX_SET_BOXES; $n++) {
            $home = $input["set_{$n}_home"] ?? null;
            $away = $input["set_{$n}_away"] ?? null;
            $homeEmpty = $home === null || $home === '';
            $awayEmpty = $away === null || $away === '';
            if ($homeEmpty && $awayEmpty) {
                continue;
            }
            if (! $homeEmpty && ! $awayEmpty) {
                $parts[] = (int) $home.'-'.(int) $away;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function validateSetPairs(array $validated): ?string
    {
        for ($n = 1; $n <= self::MAX_SET_BOXES; $n++) {
            $home = $validated["set_{$n}_home"] ?? null;
            $away = $validated["set_{$n}_away"] ?? null;
            $homeEmpty = $home === null || $home === '';
            $awayEmpty = $away === null || $away === '';
            if ($homeEmpty !== $awayEmpty) {
                return "Set {$n}: enter both Home and Away games, or leave both empty.";
            }
        }

        return null;
    }

    /**
     * Prefer set boxes; fall back to legacy score text field.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function resolveScoreRaw(array $validated, ?string $resultType): string
    {
        if ($resultType === self::RESULT_WALKOVER) {
            return trim((string) ($validated['score'] ?? ''));
        }

        $fromSets = self::scoreFromSetFields($validated);
        if ($fromSets !== '') {
            return $fromSets;
        }

        return trim((string) ($validated['score'] ?? ''));
    }

    /**
     * @return array{score: string, winner_side: ?string}
     */
    public static function fromRequest(string $scoreRaw, ?string $resultType, ?string $walkedOffSide): array
    {
        if ($resultType === self::RESULT_WALKOVER && in_array($walkedOffSide, ['home', 'away'], true)) {
            return [
                'score' => self::WALKOVER_SCORE_LABEL,
                'winner_side' => $walkedOffSide === 'home' ? 'away' : 'home',
            ];
        }

        $scoreTrimmed = trim($scoreRaw);

        return [
            'score' => $scoreTrimmed,
            'winner_side' => null,
        ];
    }

    public static function walkedOffSideFromStored(?string $score, ?string $winnerSide): ?string
    {
        if (! MatchScoreReader::isWalkover((string) $score)) {
            return null;
        }

        if ($winnerSide === 'home') {
            return 'away';
        }
        if ($winnerSide === 'away') {
            return 'home';
        }

        return null;
    }

    public static function isWalkoverRequest(?string $resultType, ?string $score): bool
    {
        return $resultType === self::RESULT_WALKOVER
            || MatchScoreReader::isWalkover(trim((string) $score));
    }
}
