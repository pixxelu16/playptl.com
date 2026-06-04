<?php

namespace App\Support;

use App\Mail\GroupMatchScheduledMail;
use App\Models\PlayoffMatch;
use App\Models\User;

final class PlayoffMatchScheduleNotifier
{
    /**
     * Email playoff match participants when date, time, venue, or court changes.
     */
    public static function notifyParticipants(
        PlayoffMatch $playoffMatch,
        bool $updatedByPlayer = false,
    ): void {
        if (! $playoffMatch->home_user_id || ! $playoffMatch->away_user_id) {
            return;
        }

        self::notifyPlayoffAssignmentChange($playoffMatch, null, null, false, true, $updatedByPlayer);
    }

    /**
     * Email current and former participants when admin changes players and/or schedule.
     */
    public static function notifyPlayoffAssignmentChange(
        PlayoffMatch $playoffMatch,
        ?int $oldHomeId,
        ?int $oldAwayId,
        bool $rosterChanged,
        bool $scheduleChanged,
        bool $updatedByPlayer = false,
    ): void {
        if (! $rosterChanged && ! $scheduleChanged) {
            return;
        }

        $playoffMatch->loadMissing(['homeUser', 'awayUser', 'league', 'groupCard']);

        $notifyUserIds = array_values(array_unique(array_filter([
            $oldHomeId,
            $oldAwayId,
            $playoffMatch->home_user_id ? (int) $playoffMatch->home_user_id : null,
            $playoffMatch->away_user_id ? (int) $playoffMatch->away_user_id : null,
        ])));

        if ($notifyUserIds === []) {
            return;
        }

        $leagueName = (string) ($playoffMatch->league?->name ?? '');
        $divisionName = (string) ($playoffMatch->groupCard?->name ?? '');
        $matchDateDisplay = $playoffMatch->match_date
            ? $playoffMatch->match_date->timezone(config('app.timezone'))->format('l, F j, Y')
            : 'To be confirmed';
        $startTimeDisplay = MatchStartTime::formatDisplay((string) ($playoffMatch->start_time ?? ''));
        $venueParts = array_filter([
            trim((string) ($playoffMatch->venue ?? '')),
            trim((string) ($playoffMatch->court ?? '')),
        ]);
        $venueDisplay = $venueParts !== [] ? implode(' · ', $venueParts) : 'To be confirmed';

        $users = User::query()->whereIn('id', $notifyUserIds)->get()->keyBy('id');

        foreach ($notifyUserIds as $userId) {
            $user = $users->get($userId);
            if (! self::userHasDeliverableEmail($user)) {
                continue;
            }

            $stillOnMatch = $userId === (int) $playoffMatch->home_user_id
                || $userId === (int) $playoffMatch->away_user_id;

            MatchScheduleMailQueue::queue($user->email, new GroupMatchScheduledMail(
                recipientDisplayName: self::userDisplayName($user),
                leagueName: $leagueName !== '' ? $leagueName : 'Tournament',
                divisionName: $divisionName,
                groupName: $playoffMatch->roundLabel(),
                matchDateDisplay: $matchDateDisplay,
                startTime: $startTimeDisplay !== '' ? $startTimeDisplay : 'To be confirmed',
                venueDisplay: $venueDisplay,
                formatLabel: 'playoff',
                opponentSummary: $stillOnMatch
                    ? self::opponentSummaryForRecipient($playoffMatch, $userId)
                    : 'You are no longer listed on this playoff match.',
                updatedByPlayer: $updatedByPlayer,
                playoffRoundLabel: $playoffMatch->roundLabel(),
                rosterChanged: $rosterChanged,
                removedFromMatch: $rosterChanged && ! $stillOnMatch,
            ));
        }
    }

    /**
     * @return list<User>
     */
    private static function participantUsers(PlayoffMatch $playoffMatch): array
    {
        return collect([
            $playoffMatch->homeUser,
            $playoffMatch->awayUser,
        ])->filter()->unique('id')->values()->all();
    }

    private static function userHasDeliverableEmail(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }
        $email = trim((string) ($user->email ?? ''));

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private static function userDisplayName(?User $user): string
    {
        if (! $user instanceof User) {
            return 'Player';
        }
        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }
        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));

        return trim($first.' '.$last) !== '' ? trim($first.' '.$last) : 'Player';
    }

    private static function opponentSummaryForRecipient(PlayoffMatch $match, int $recipientUserId): string
    {
        if ($recipientUserId === (int) $match->home_user_id) {
            return 'You vs '.self::userDisplayName($match->awayUser);
        }

        return 'You vs '.self::userDisplayName($match->homeUser);
    }
}
