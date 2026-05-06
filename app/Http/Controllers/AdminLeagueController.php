<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLeagueController extends Controller
{
    public function index(): View
    {
        return view('admin.leagues.index', [
            'leagues' => League::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.leagues.create', [
            'league' => new League,
            'groups' => Group::orderBy('name')->get(),
            'groupCards' => GroupCard::query()->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupIds = $this->normalizeGroupIds($validated['group_ids'] ?? []);
        $groupCardIds = $this->normalizeGroupIds($validated['group_card_ids'] ?? []);
        unset($validated['group_ids'], $validated['group_card_ids']);
        $validated['type'] = $validated['type'] ?? 'single';
        $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        $validated['logo_path'] = $this->storeLogo($request);

        $league = League::create($validated);
        $league->groups()->sync($groupIds);
        $league->groupCards()->sync($groupCardIds);

        return redirect()->route('admin.leagues.index')->with('status', 'League created successfully.');
    }

    public function show(League $league): View
    {
        return view('admin.leagues.show', [
            'league' => $league->load([
                'groups' => fn ($q) => $q->orderBy('name'),
                'groupCards' => fn ($q) => $q->orderBy('display_order')->orderBy('name'),
            ]),
        ]);
    }

    public function edit(League $league): View
    {
        return view('admin.leagues.edit', [
            'league' => $league->load(['groups', 'groupCards']),
            'groups' => Group::orderBy('name')->get(),
            'groupCards' => GroupCard::query()->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, League $league): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupIds = $this->normalizeGroupIds($validated['group_ids'] ?? []);
        $groupCardIds = $this->normalizeGroupIds($validated['group_card_ids'] ?? []);
        unset($validated['group_ids'], $validated['group_card_ids']);
        $validated['type'] = $validated['type'] ?? $league->type;
        $validated['slug'] = $this->generateUniqueSlug($validated['name'], $league->id);
        $logoPath = $this->storeLogo($request);

        if ($logoPath !== null) {
            $this->deleteLogo($league->logo_path);
            $validated['logo_path'] = $logoPath;
        }

        $league->update($validated);
        $league->groups()->sync($groupIds);
        $league->groupCards()->sync($groupCardIds);

        return redirect()->route('admin.leagues.index')->with('status', 'League updated successfully.');
    }

    public function destroy(League $league): RedirectResponse
    {
        $this->deleteLogo($league->logo_path);
        $league->delete();

        return redirect()->route('admin.leagues.index')->with('status', 'League deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'logo'        => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'stats' => ['nullable', Rule::in(['active', 'deactive', 'upcoming', 'completed'])],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
            'type' => ['nullable', Rule::in(['single', 'doubles'])],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'group_card_ids' => ['nullable', 'array'],
            'group_card_ids.*' => ['integer', 'exists:group_cards,id'],
        ]);
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function normalizeGroupIds(array $ids): array
    {
        $normalized = array_map('intval', $ids);

        return array_values(array_unique(array_filter($normalized, fn(int $id) => $id > 0)));
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreLeagueId = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'league';
        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreLeagueId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $ignoreLeagueId = null): bool
    {
        return League::query()
            ->where('slug', $slug)
            ->when($ignoreLeagueId !== null, fn ($query) => $query->whereKeyNot($ignoreLeagueId))
            ->exists();
    }

    protected function storeLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $directory = public_path('admin/uploads/leagues');
        File::ensureDirectoryExists($directory);

        $file     = $request->file('logo');
        $filename = uniqid('league-', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'admin/uploads/leagues/'.$filename;
    }

    protected function deleteLogo(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $relative = str_starts_with($path, 'public/') ? substr($path, strlen('public/')) : $path;
        foreach ([public_path($relative), public_path('public/'.$relative)] as $fullPath) {
            if (File::exists($fullPath)) {
                File::delete($fullPath);

                return;
            }
        }
    }
}
