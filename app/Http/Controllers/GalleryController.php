<?php

namespace App\Http\Controllers;

use App\Models\GroupMatchPlayerUpload;
use App\Support\GalleryUploadPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public const PER_PAGE = 16;

    /**
     * Public gallery: date filter tabs + Laravel pagination (16 per page) per tab.
     */
    public function __invoke(Request $request): View
    {
        $now = Carbon::now();
        $oldestTabDay = $now->copy()->subDays(5)->startOfDay();

        $tabs = [['key' => 'all', 'label' => 'All']];
        $allowedDateKeys = [];

        for ($i = 0; $i < 6; $i++) {
            $d = $now->copy()->subDays($i)->startOfDay();
            $key = 'date:'.$d->toDateString();
            $allowedDateKeys[] = $key;

            if ($i === 0) {
                $label = 'Today '.$d->format('M j');
            } elseif ($i === 1) {
                $label = 'Yesterday '.$d->format('M j');
            } else {
                $label = $d->format('M j');
            }
            $tabs[] = ['key' => $key, 'label' => $label];
        }

        $hasEarlier = false;
        if (Schema::hasTable('group_match_player_uploads')) {
            $hasEarlier = GroupMatchPlayerUpload::query()
                ->where('upload_date', '<', $oldestTabDay->toDateString())
                ->exists();
        }

        if ($hasEarlier) {
            $tabs[] = ['key' => 'earlier', 'label' => 'Earlier'];
        }

        $allowedTabKeys = collect($tabs)->pluck('key')->all();
        $tab = (string) $request->query('tab', 'all');
        if (! in_array($tab, $allowedTabKeys, true)) {
            $tab = 'all';
        }

        $galleryPhotoCount = 0;
        if (Schema::hasTable('group_match_player_uploads')) {
            $galleryPhotoCount = (int) GroupMatchPlayerUpload::query()->count();
        }

        if (! Schema::hasTable('group_match_player_uploads')) {
            $emptyPaginator = new LengthAwarePaginator(
                [],
                0,
                self::PER_PAGE,
                1,
                ['path' => $request->url(), 'pageName' => 'page'],
            );

            return view('gallery', [
                'galleryTabs' => $tabs,
                'galleryItems' => $emptyPaginator,
                'galleryActiveTab' => $tab,
                'galleryPhotoCount' => 0,
            ]);
        }

        $query = GroupMatchPlayerUpload::query()
            ->with(GalleryUploadPresenter::eagerLoadRelations())
            ->orderByDesc('upload_date')
            ->orderByDesc('id');

        if ($tab === 'earlier') {
            $query->where('upload_date', '<', $oldestTabDay->toDateString());
        } elseif (str_starts_with($tab, 'date:')) {
            $dateStr = substr($tab, 5);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr) === 1 && in_array('date:'.$dateStr, $allowedDateKeys, true)) {
                $query->whereDate('upload_date', $dateStr);
            } else {
                $tab = 'all';
            }
        }

        $appends = $tab === 'all' ? [] : ['tab' => $tab];

        $paginator = $query->paginate(self::PER_PAGE)->appends($appends);

        $paginator->getCollection()->transform(
            fn (GroupMatchPlayerUpload $u) => GalleryUploadPresenter::toItem($u, $oldestTabDay),
        );

        return view('gallery', [
            'galleryTabs' => $tabs,
            'galleryItems' => $paginator,
            'galleryActiveTab' => $tab,
            'galleryPhotoCount' => $galleryPhotoCount,
        ]);
    }
}
