<?php

namespace App\Support;

use App\Mail\GroupMatchesCancelledMail;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use App\Models\User;
use Illuminate\Support\Collection;

final class GroupMatchScheduleCancelledNotifier
{
    /**
     * @param  Collection<int, GroupMatch>  $matches
     */
    public static function notifyDivision(
        Collection $matches,
        GroupCard $groupCard,
        League $league,
    ): void {
        if ($matches->isEmpty()) {
            return;
        }

        $leagueName = (string) ($league->name ?? 'League');
        $divisionName = trim((string) ($groupCard->name ?? ''));
        $matchesByUserId = [];

        foreach ($matches as $match) {
            foreach ([
                $match->home_user_id,
                $match->away_user_id,
                $match->home_partner_user_id,
                $match->away_partner_user_id,
            ] as $userId) {
                if (! $userId) {
                    continue;
                }
                $uid = (int) $userId;
                $matchesByUserId[$uid] = ($matchesByUserId[$uid] ?? 0) + 1;
            }
        }

        if ($matchesByUserId === []) {
            return;
        }

        $users = User::query()
            ->whereIn('id', array_keys($matchesByUserId))
            ->get()
            ->keyBy('id');

        foreach ($matchesByUserId as $userId => $matchCount) {
            $user = $users->get($userId);
            if (! self::userHasDeliverableEmail($user)) {
                continue;
            }

            MatchScheduleMailQueue::queue($user->email, new GroupMatchesCancelledMail(
                recipientDisplayName: self::userDisplayName($user),
                leagueName: $leagueName,
                divisionName: $divisionName,
                groupName: '',
                cancelledMatchCount: $matchCount,
                divisionWideCancel: true,
            ));
        }
    }

    /**
     * @param  Collection<int, GroupMatch>  $matches
     */
    public static function notifyParticipants(
        Collection $matches,
        Group $group,
        GroupCard $groupCard,
        League $league,
    ): void {
        if ($matches->isEmpty()) {
            return;
        }

        $leagueName = (string) ($league->name ?? 'League');
        $divisionName = trim((string) ($groupCard->name ?? ''));
        $groupName = trim((string) ($group->name ?? ''));
        $matchCount = $matches->count();

        $userIds = $matches
            ->flatMap(fn (GroupMatch $match) => [
                $match->home_user_id,
                $match->away_user_id,
                $match->home_partner_user_id,
                $match->away_partner_user_id,
            ])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $users = User::query()
            ->whereIn('id', $userIds->all())
            ->get()
            ->keyBy('id');

        foreach ($userIds as $userId) {
            $user = $users->get($userId);
            if (! self::userHasDeliverableEmail($user)) {
                continue;
            }

            MatchScheduleMailQueue::queue($user->email, new GroupMatchesCancelledMail(
                recipientDisplayName: self::userDisplayName($user),
                leagueName: $leagueName,
                divisionName: $divisionName,
                groupName: $groupName !== '' ? $groupName : 'your subgroup',
                cancelledMatchCount: $matchCount,
            ));
        }
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
}
