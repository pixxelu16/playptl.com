<?php
namespace App\Http\Controllers;

use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Support\LeagueEntryFee;
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
            'groupCards' => GroupCard::query()->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupCardIds = $this->normalizeGroupIds($validated['group_card_ids'] ?? []);
        unset($validated['group_card_ids'], $validated['singles_entry_fee'], $validated['doubles_entry_fee']);
        $validated['type'] = $validated['type'] ?? 'single';
        $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        $validated['logo_path'] = $this->storeLogo($request);

        /** @var League $league */
        $league = League::create($validated);
        $league->groupCards()->sync($groupCardIds);

        return redirect()->route('admin.leagues.index')->with('status', 'Tournament created successfully.');
    }

    public function show(League $league): View
    {
        $singlesCount = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('registration_type', 'singles')
            ->count();

        $doublesCount = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('registration_type', 'doubles')
            ->count();

        return view('admin.leagues.show', [
            'league' => $league->load([
                'groupCards' => fn ($q) => $q->orderBy('display_order')->orderBy('name'),
            ]),
            'singlesCount' => $singlesCount,
            'doublesCount' => $doublesCount,
        ]);
    }

    public function edit(League $league): View
    {
        return view('admin.leagues.edit', [
            'league' => $league->load(['groupCards']),
            'groupCards' => GroupCard::query()->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, League $league): RedirectResponse
    {
        $validated = $this->validatedData($request, $league);
        $groupCardIds = $this->normalizeGroupIds($validated['group_card_ids'] ?? []);
        unset($validated['group_card_ids'], $validated['singles_entry_fee'], $validated['doubles_entry_fee']);
        $validated['type'] = $validated['type'] ?? $league->type;
        $validated['slug'] = $this->generateUniqueSlug($validated['name'], $league->id);
        $logoPath = $this->storeLogo($request);

        if ($logoPath !== null) {
            $this->deleteLogo($league->logo_path);
            $validated['logo_path'] = $logoPath;
        }

        $league->update($validated);
        $league->groupCards()->sync($groupCardIds);

        return redirect()->route('admin.leagues.index')->with('status', 'Tournament updated successfully.');
    }

    public function destroy(League $league): RedirectResponse
    {
        $this->deleteLogo($league->logo_path);
        $league->delete();

        return redirect()->route('admin.leagues.index')->with('status', 'Tournament deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request, ?League $existing = null): array
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'logo'        => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'stats' => ['nullable', Rule::in(['active', 'deactive', 'upcoming', 'completed'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'type' => ['nullable', Rule::in(['single', 'doubles'])],
            'singles_entry_fee' => ['required', 'numeric', 'min:0', 'max:99999'],
            'doubles_entry_fee' => ['required', 'numeric', 'min:0', 'max:99999'],
            'group_card_ids' => ['nullable', 'array'],
            'group_card_ids.*' => ['integer', 'exists:group_cards,id'],
        ]);

        $validated['singles_entry_fee_cents'] = LeagueEntryFee::centsFromDollarsInput($validated['singles_entry_fee']);
        $validated['doubles_entry_fee_cents'] = LeagueEntryFee::centsFromDollarsInput($validated['doubles_entry_fee']);

        return $validated;
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
