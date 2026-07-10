<?php

namespace App\Support;

use App\Enums\GroupMatchFormat;
use App\Mail\GroupMatchScheduledMail;
use App\Models\GroupMatch;
use App\Models\User;

final class GroupMatchScheduleNotifier
{
    /**
     * Email match participants when date, time, or venue changes.
     *
     * @param  int|null  $excludeUserId  Skip this user (e.g. the player who saved the update).
     */
    public static function notifyParticipants(
        GroupMatch $groupMatch,
        ?int $excludeUserId = null,
        bool $updatedByOpponent = false,
        bool $updatedByPlayer = false,
    ): void {
        $groupMatch->loadMissing([
            'homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser',
            'group', 'league', 'groupCard',
        ]);

        $leagueName = (string) ($groupMatch->league?->name ?? '');
        $divisionName = (string) ($groupMatch->groupCard?->name ?? '');
        $groupName = (string) ($groupMatch->group?->name ?? '');
        $matchDateDisplay = $groupMatch->match_date
            ? $groupMatch->match_date->timezone(config('app.timezone'))->format('l, F j, Y')
            : '';
        $startTimeDisplay = MatchStartTime::formatDisplay((string) ($groupMatch->start_time ?? ''));
        $venueParts = array_filter([
            trim((string) ($groupMatch->venue ?? '')),
            trim((string) ($groupMatch->court ?? '')),
        ]);
        $venueDisplay = $venueParts !== [] ? implode(' · ', $venueParts) : 'To be confirmed';
        $formatLabel = $groupMatch->format === GroupMatchFormat::Singles ? 'singles' : 'doubles';

        foreach (self::participantUsers($groupMatch) as $user) {
            if ($excludeUserId !== null && (int) $user->id === $excludeUserId) {
                continue;
            }
            if (! self::userHasDeliverableEmail($user)) {
                continue;
            }
            MatchScheduleMailQueue::queue($user->email, new GroupMatchScheduledMail(
                recipientDisplayName: self::userDisplayName($user),
                leagueName: $leagueName !== '' ? $leagueName : 'League',
                divisionName: $divisionName,
                groupName: $groupName,
                matchDateDisplay: $matchDateDisplay,
                startTime: $startTimeDisplay !== '' ? $startTimeDisplay : 'To be confirmed',
                venueDisplay: $venueDisplay,
                formatLabel: $formatLabel,
                opponentSummary: self::opponentSummaryForRecipient($groupMatch, (int) $user->id),
                updatedByOpponent: $updatedByOpponent && ! $updatedByPlayer,
                updatedByPlayer: $updatedByPlayer,
            ));
        }
    }

    /**
     * @return list<User>
     */
    private static function participantUsers(GroupMatch $groupMatch): array
    {
        return collect([
            $groupMatch->homeUser,
            $groupMatch->awayUser,
            $groupMatch->homePartnerUser,
            $groupMatch->awayPartnerUser,
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

    private static function opponentSummaryForRecipient(GroupMatch $match, int $recipientUserId): string
    {
        if ($match->format === GroupMatchFormat::Singles) {
            if ($recipientUserId === (int) $match->home_user_id) {
                return 'You vs '.self::userDisplayName($match->awayUser);
            }

            return 'You vs '.self::userDisplayName($match->homeUser);
        }

        $homeIds = [(int) $match->home_user_id, (int) $match->home_partner_user_id];
        $onHome = in_array($recipientUserId, $homeIds, true);
        if ($onHome) {
            $partner = $recipientUserId === (int) $match->home_user_id
                ? $match->homePartnerUser
                : $match->homeUser;
            $pName = self::userDisplayName($partner);
            $o1 = self::userDisplayName($match->awayUser);
            $o2 = self::userDisplayName($match->awayPartnerUser);

            return 'You and '.$pName.' vs '.$o1.' and '.$o2;
        }

        $partner = $recipientUserId === (int) $match->away_user_id
            ? $match->awayPartnerUser
            : $match->awayUser;
        $pName = self::userDisplayName($partner);
        $o1 = self::userDisplayName($match->homeUser);
        $o2 = self::userDisplayName($match->homePartnerUser);

        return 'You and '.$pName.' vs '.$o1.' and '.$o2;
    }
}
