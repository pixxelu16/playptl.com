<?php

namespace App\Support;

use App\Models\GroupMatch;
use App\Models\GroupMatchPlayerUpload;
use Carbon\Carbon;

final class GalleryUploadPresenter
{
    /**
     * @return array{
     *     id: int,
     *     url: string,
     *     uploadDate: string,
     *     isEarlier: bool,
     *     notes: ?string,
     *     leagueName: string,
     *     divisionLabel: string,
     *     matchLabel: string,
     *     matchScore: string,
     *     alt: string
     * }
     */
    public static function toItem(GroupMatchPlayerUpload $upload, ?Carbon $oldestTabDay = null): array
    {
        $d = $upload->upload_date instanceof Carbon
            ? $upload->upload_date->copy()->startOfDay()
            : Carbon::parse($upload->upload_date)->startOfDay();

        $match = $upload->relationLoaded('groupMatch')
            ? $upload->groupMatch
            : $upload->groupMatch()->with([
                'league',
                'groupCard',
                'group',
                'homeUser',
                'awayUser',
                'homePartnerUser',
                'awayPartnerUser',
            ])->first();
        $leagueName = trim((string) ($match?->league?->name ?? ''));
        $groupCardName = trim((string) ($match?->groupCard?->name ?? ''));
        $groupName = trim((string) ($match?->group?->name ?? ''));
        $divisionLabel = $groupCardName;
        if ($groupName !== '') {
            $divisionLabel = $divisionLabel !== ''
                ? $divisionLabel.' · '.$groupName
                : $groupName;
        }

        $matchLabel = self::matchLabel($match);
        $matchScore = self::matchScoreLabel($match);

        $altParts = array_filter([$leagueName, $divisionLabel, $matchLabel, $matchScore]);
        $alt = $altParts !== []
            ? implode(' — ', $altParts)
            : 'Match photo';

        return [
            'id' => (int) $upload->id,
            'url' => asset($upload->image_path),
            'uploadDate' => $d->toDateString(),
            'isEarlier' => $oldestTabDay !== null && $d->lt($oldestTabDay),
            'notes' => $upload->notes,
            'leagueName' => $leagueName,
            'divisionLabel' => $divisionLabel,
            'matchLabel' => $matchLabel,
            'matchScore' => $matchScore,
            'alt' => $alt,
        ];
    }

    public static function matchScoreLabel(?GroupMatch $match): string
    {
        if (! $match instanceof GroupMatch) {
            return '';
        }

        $score = trim((string) ($match->score ?? ''));
        if ($score !== '') {
            return $score;
        }

        return $match->homeSideWon() !== null ? 'Recorded' : '';
    }

    public static function matchLabel(?GroupMatch $match): string
    {
        if (! $match instanceof GroupMatch) {
            return '';
        }

        $home = MatchSchedulePresenter::formatSideNames($match, 'home');
        $away = MatchSchedulePresenter::formatSideNames($match, 'away');
        $label = $home.' vs '.$away;

        if ($match->round_number !== null && (int) $match->round_number > 0) {
            $label .= ' · '.LeagueWeekCalendar::weekHeading((int) $match->round_number);
        } elseif ($match->match_date) {
            $label .= ' · '.$match->match_date->format('M j, Y');
        }

        return $label;
    }

    /** @return list<string> */
    public static function eagerLoadRelations(): array
    {
        return [
            'groupMatch.league',
            'groupMatch.groupCard',
            'groupMatch.group',
            'groupMatch.homeUser',
            'groupMatch.awayUser',
            'groupMatch.homePartnerUser',
            'groupMatch.awayPartnerUser',
        ];
    }
}
