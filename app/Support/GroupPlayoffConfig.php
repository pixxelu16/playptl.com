<?php

namespace App\Support;

use App\Enums\GroupPlayoffFormat;
use App\Enums\PlayoffQualifierPath;
use App\Models\GroupCard;

/**
 * Playoff slot counts per group — who goes to Quarter, Round of 16, Pre-Pre-Q by rank.
 */
final class GroupPlayoffConfig
{
    public function __construct(
        public readonly GroupPlayoffFormat $format,
        public readonly int $quarterSpots,
        public readonly int $r16Spots,
        public readonly int $ppqSpots,
    ) {}

    public static function fromGroupCard(GroupCard $groupCard): self
    {
        $format = GroupPlayoffFormat::resolveOrDefault($groupCard->playoff_format ?? null);

        $defaults = match ($format) {
            GroupPlayoffFormat::Top4QuarterRestR16 => ['quarter' => 4, 'r16' => 8, 'ppq' => 0],
            GroupPlayoffFormat::RoundOf16 => ['quarter' => 0, 'r16' => 16, 'ppq' => 0],
            GroupPlayoffFormat::PrePreQR16 => ['quarter' => 0, 'r16' => 8, 'ppq' => 16],
            GroupPlayoffFormat::DirectQuarter => ['quarter' => 8, 'r16' => 0, 'ppq' => 0],
        };

        return new self(
            $format,
            max(0, (int) ($groupCard->playoff_quarter_spots ?? $defaults['quarter'])),
            max(0, (int) ($groupCard->playoff_r16_spots ?? $defaults['r16'])),
            max(0, (int) ($groupCard->playoff_ppq_spots ?? $defaults['ppq'])),
        );
    }

    public function pathForRank(int $rank): string
    {
        if ($rank < 1) {
            return PlayoffQualifierPath::Eliminated->value;
        }

        $qEnd = $this->quarterEndRank();
        if ($qEnd > 0 && $rank <= $qEnd) {
            return PlayoffQualifierPath::Quarter->value;
        }

        $r16Start = $qEnd + 1;
        $r16End = $qEnd + $this->r16Spots;
        if ($this->r16Spots > 0 && $rank >= $r16Start && $rank <= $r16End) {
            return PlayoffQualifierPath::RoundOf16->value;
        }

        $ppqStart = $r16End + 1;
        $ppqEnd = $r16End + $this->ppqSpots;
        if ($this->ppqSpots > 0 && $rank >= $ppqStart && $rank <= $ppqEnd) {
            return PlayoffQualifierPath::PrePreQ->value;
        }

        return PlayoffQualifierPath::Eliminated->value;
    }

    public function quarterEndRank(): int
    {
        return match ($this->format) {
            GroupPlayoffFormat::Top4QuarterRestR16, GroupPlayoffFormat::DirectQuarter => $this->quarterSpots,
            default => 0,
        };
    }

    public function r16StartRank(): int
    {
        return $this->quarterEndRank() + 1;
    }

    public function r16EndRank(): int
    {
        return $this->quarterEndRank() + $this->r16Spots;
    }

    public function ppqStartRank(): int
    {
        return $this->r16EndRank() + 1;
    }

    public function ppqEndRank(): int
    {
        return $this->r16EndRank() + $this->ppqSpots;
    }

    /**
     * @return list<string>
     */
    public function summaryLines(): array
    {
        $lines = [];

        if ($this->quarterSpots > 0) {
            $lines[] = "Rank 1–{$this->quarterEndRank()}: {$this->quarterSpots} players → Quarter (direct)";
        }

        if ($this->r16Spots > 0) {
            $start = $this->r16StartRank();
            $end = $this->r16EndRank();
            $label = $this->format === GroupPlayoffFormat::PrePreQR16
                ? 'Round of 16 (direct home seeds)'
                : 'Round of 16';
            $lines[] = "Rank {$start}–{$end}: {$this->r16Spots} players → {$label}";
        }

        if ($this->ppqSpots > 0) {
            $start = $this->ppqStartRank();
            $end = $this->ppqEndRank();
            $matches = (int) floor($this->ppqSpots / 2);
            $lines[] = "Rank {$start}–{$end}: {$this->ppqSpots} players → Pre-Pre-Q ({$matches} matches, {$matches} winners)";
        }

        $lastIn = max($this->ppqEndRank(), $this->r16EndRank(), $this->quarterEndRank());
        if ($lastIn > 0) {
            $lines[] = 'Rank '.($lastIn + 1).'+: not in playoffs';
        }

        return $lines;
    }

    public function descriptionHtml(): string
    {
        return implode('<br>', $this->summaryLines());
    }
}
