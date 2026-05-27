<?php

namespace App\Http\Controllers;

use App\Enums\GroupMatchFormat;
use App\Models\Announcement;
use App\Models\GroupMatch;
use App\Models\GroupMatchPlayerUpload;
use App\Models\User;
use App\Support\GalleryUploadPresenter;
use App\Support\MatchStartTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $todayPhotos = collect();
        $yesterdayPhotos = collect();

        if (Schema::hasTable('group_match_player_uploads')) {
            $todayPhotos = GroupMatchPlayerUpload::query()
                ->with(GalleryUploadPresenter::eagerLoadRelations())
                ->whereDate('upload_date', Carbon::today())
                ->orderByDesc('id')
                ->get()
                ->map(fn (GroupMatchPlayerUpload $u) => GalleryUploadPresenter::toItem($u));

            $yesterdayPhotos = GroupMatchPlayerUpload::query()
                ->with(GalleryUploadPresenter::eagerLoadRelations())
                ->whereDate('upload_date', Carbon::yesterday())
                ->orderByDesc('id')
                ->get()
                ->map(fn (GroupMatchPlayerUpload $u) => GalleryUploadPresenter::toItem($u));
        }

        $defaultGalleryTab = $todayPhotos->isNotEmpty()
            ? 'today'
            : ($yesterdayPhotos->isNotEmpty() ? 'yesterday' : 'today');

        $homeScheduleDays = $this->homeRecentScheduleDays(4);
        $announcements = $this->homeAnnouncementsPayload();

        return view('home', [
            'homeGalleryToday' => $todayPhotos,
            'homeGalleryYesterday' => $yesterdayPhotos,
            'homeGalleryDefaultTab' => $defaultGalleryTab,
            'homeScheduleDays' => $homeScheduleDays,
            'homeFeaturedAnnouncement' => $announcements['featured'],
            'homeAnnouncementRows' => $announcements['rows'],
        ]);
    }

    /**
     * @return array{featured: ?array{badgeClass: string, badgeLabel: string, datetime: string, dateHuman: string, title: string, description: string}, rows: list<array{day: string, month: string, title: string, text: string, tag: string, tagClass: string}>}
     */
    protected function homeAnnouncementsPayload(): array
    {
        $empty = ['featured' => null, 'rows' => []];

        if (! Schema::hasTable('announcements')) {
            return $empty;
        }

        $today = Carbon::today()->toDateString();

        $featured = Announcement::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderByDesc('announcement_date')
            ->orderByDesc('id')
            ->first();

        if (! $featured instanceof Announcement) {
            $featured = Announcement::query()
                ->where('is_active', true)
                ->whereDate('announcement_date', $today)
                ->orderByDesc('id')
                ->first();
        }

        if (! $featured instanceof Announcement) {
            $featured = Announcement::query()
                ->where('is_active', true)
                ->orderByDesc('announcement_date')
                ->orderByDesc('id')
                ->first();
        }

        if (! $featured instanceof Announcement) {
            return $empty;
        }

        $rows = Announcement::query()
            ->where('is_active', true)
            ->where('id', '!=', $featured->id)
            ->orderByDesc('announcement_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->map(fn (Announcement $a): array => $this->homeAnnouncementRowArray($a))
            ->all();

        return [
            'featured' => $this->homeAnnouncementFeaturedArray($featured),
            'rows' => $rows,
        ];
    }

    /**
     * @return array{badgeClass: string, badgeLabel: string, datetime: string, dateHuman: string, title: string, description: string}
     */
    protected function homeAnnouncementFeaturedArray(Announcement $a): array
    {
        $d = $a->announcement_date;
        $dStart = $d->copy()->startOfDay();
        $typeLabel = ucfirst((string) $a->type);

        if ($a->is_featured) {
            $badgeLabel = 'Featured - '.$typeLabel;
        } elseif ($dStart->equalTo(Carbon::today()->startOfDay())) {
            $badgeLabel = 'Today - '.$typeLabel;
        } else {
            $badgeLabel = $typeLabel;
        }

        return [
            'badgeClass' => $this->homeAnnouncementFeaturedBadgeClass((string) $a->type),
            'badgeLabel' => $badgeLabel,
            'datetime' => $d->format('Y-m-d'),
            'dateHuman' => $d->format('M j, Y'),
            'title' => (string) $a->title,
            'description' => (string) $a->description,
        ];
    }

    /**
     * @return array{day: string, month: string, title: string, text: string, tag: string, tagClass: string}
     */
    protected function homeAnnouncementRowArray(Announcement $a): array
    {
        $d = $a->announcement_date;

        return [
            'day' => $d->format('j'),
            'month' => $d->format('M'),
            'title' => (string) $a->title,
            'text' => (string) $a->description,
            'tag' => ucfirst((string) $a->type),
            'tagClass' => $this->homeAnnouncementListTagClass((string) $a->type),
        ];
    }

    protected function homeAnnouncementFeaturedBadgeClass(string $type): string
    {
        return match ($type) {
            'notice' => 'inline-flex items-center rounded border border-[rgba(198,40,40,0.35)] bg-[rgba(211,47,47,0.1)] px-3 py-1.5 text-sm font-semibold uppercase tracking-[0.08em] text-[#c62828]',
            'update' => 'inline-flex items-center rounded border border-[rgba(21,101,192,0.35)] bg-[rgba(25,118,210,0.1)] px-3 py-1.5 text-sm font-semibold uppercase tracking-[0.08em] text-[#1565c0]',
            'event' => 'inline-flex items-center rounded border border-[rgba(106,27,154,0.35)] bg-[rgba(106,27,154,0.1)] px-3 py-1.5 text-sm font-semibold uppercase tracking-[0.08em] text-[#6a1b9a]',
            default => 'inline-flex items-center rounded border border-[#55A64E] bg-[#E4F7E7] px-3 py-1.5 text-sm font-semibold uppercase tracking-[0.08em] text-[#55A64E]',
        };
    }

    protected function homeAnnouncementListTagClass(string $type): string
    {
        return match ($type) {
            'notice' => 'border-[rgba(198,40,40,0.2)] bg-[rgba(211,47,47,0.12)] text-[#c62828]',
            'update' => 'border-[rgba(21,101,192,0.2)] bg-[rgba(25,118,210,0.12)] text-[#1565c0]',
            'event' => 'border-[rgba(106,27,154,0.18)] bg-[rgba(106,27,154,0.1)] text-[#6a1b9a]',
            default => 'border-[rgba(85,166,78,0.28)] bg-[rgba(85,166,78,0.12)] text-[#2E7D32]',
        };
    }

    /**
     * @return list<array{dayBadge: string, dateLine: string, matches: list<array{time: string, location: string, players: string}>}>
     */
    protected function homeRecentScheduleDays(int $limit): array
    {
        if (! Schema::hasTable('group_matches')) {
            return [];
        }

        $tz = config('app.timezone');
        $today = Carbon::now($tz)->startOfDay();

        $upcoming = $this->homeScheduleBaseQuery()
            ->whereDate('match_date', '>=', $today->toDateString())
            ->orderBy('match_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $matches = $upcoming;
        if ($matches->count() < $limit) {
            $need = $limit - $matches->count();
            $past = $this->homeScheduleBaseQuery()
                ->whereDate('match_date', '<', $today->toDateString())
                ->orderByDesc('match_date')
                ->orderByDesc('sort_order')
                ->orderByDesc('id')
                ->limit($need)
                ->get();
            $matches = $matches->concat($past)->take($limit)->values();
        }

        if ($matches->isEmpty()) {
            return [];
        }

        $grouped = $matches->groupBy(fn (GroupMatch $m) => $m->match_date->format('Y-m-d'));

        $sortedDateKeys = $grouped->keys()->sort(function (string $a, string $b) use ($today, $tz): int {
            $da = Carbon::parse($a, $tz)->startOfDay();
            $db = Carbon::parse($b, $tz)->startOfDay();

            $bucket = static function (Carbon $d, Carbon $t): int {
                if ($d->equalTo($t)) {
                    return 0;
                }

                return $d->greaterThan($t) ? 1 : 2;
            };

            $ba = $bucket($da, $today);
            $bb = $bucket($db, $today);
            if ($ba !== $bb) {
                return $ba <=> $bb;
            }
            if ($ba === 1) {
                return $da <=> $db;
            }
            if ($ba === 2) {
                return $db <=> $da;
            }

            return 0;
        })->values();

        $days = [];
        foreach ($sortedDateKeys as $dateKey) {
            $dayMatches = $grouped->get($dateKey) ?? collect();
            /** @var Collection<int, GroupMatch> $dayMatches */
            if ($dayMatches->isEmpty()) {
                continue;
            }
            $d = Carbon::parse($dateKey, $tz)->startOfDay();
            $dayBadge = $d->equalTo($today)
                ? 'Today'
                : ($d->equalTo($today->copy()->addDay()) ? 'Tomorrow' : $d->format('D'));

            $days[] = [
                'dayBadge' => $dayBadge,
                'dateLine' => $d->format('D').' - '.$d->format('M j'),
                'matches' => $dayMatches->map(function (GroupMatch $match): array {
                    $timeRaw = trim((string) ($match->start_time ?? ''));
                    $time = $timeRaw !== ''
                        ? (MatchStartTime::formatDisplay($timeRaw) ?: $timeRaw)
                        : 'TBA';

                    $venue = trim((string) ($match->venue ?? ''));
                    $court = trim((string) ($match->court ?? ''));
                    $placeParts = array_values(array_filter([$venue, $court]));
                    $location = $placeParts !== [] ? implode(' — ', $placeParts) : 'TBA';

                    return [
                        'time' => $time,
                        'venue' => $venue,
                        'court' => $court,
                        'location' => $location,
                        'players' => $this->homeMatchPlayersLine($match),
                    ];
                })->values()->all(),
            ];
        }

        return $days;
    }

    /**
     * @return Builder<GroupMatch>
     */
    protected function homeScheduleBaseQuery()
    {
        return GroupMatch::query()
            ->whereHas('league', fn ($q) => $q->where('stats', 'active'))
            ->when(
                Schema::hasColumn('group_cards', 'status'),
                fn ($q) => $q->whereHas('groupCard', fn ($qq) => $qq->where('status', 'active'))
            )
            ->with(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser']);
    }

    protected function homeMatchPlayersLine(GroupMatch $match): string
    {
        return $this->homeMatchSideLabel($match, 'home').' Vs '.$this->homeMatchSideLabel($match, 'away');
    }

    protected function homeMatchSideLabel(GroupMatch $match, string $side): string
    {
        $isHome = $side === 'home';
        if ($match->format === GroupMatchFormat::Doubles) {
            if ($isHome && $match->homePartnerUser) {
                return $this->homeMatchPlayerName($match->homeUser).' & '.$this->homeMatchPlayerName($match->homePartnerUser);
            }
            if (! $isHome && $match->awayPartnerUser) {
                return $this->homeMatchPlayerName($match->awayUser).' & '.$this->homeMatchPlayerName($match->awayPartnerUser);
            }
        }

        $u = $isHome ? $match->homeUser : $match->awayUser;

        return $this->homeMatchPlayerName($u);
    }

    protected function homeMatchPlayerName(?User $user): string
    {
        $rawName = trim((string) ($user?->name ?? ''));
        $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);

        return $displayName !== '' ? $displayName : '—';
    }
}
