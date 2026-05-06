<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\League;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            'league' => new League(),
            'groups' => Group::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupIds  = $this->normalizeGroupIds($validated['group_ids'] ?? []);
        unset($validated['group_ids']);
        $validated['logo_path'] = $this->storeLogo($request);

        $league = League::create($validated);
        $league->groups()->sync($groupIds);

        return redirect()->route('admin.leagues.index')->with('status', 'League created successfully.');
    }

    public function show(League $league): View
    {
        return view('admin.leagues.show', [
            'league' => $league->load(['groups' => fn($q) => $q->orderBy('name')]),
        ]);
    }

    public function edit(League $league): View
    {
        return view('admin.leagues.edit', [
            'league' => $league->load('groups'),
            'groups' => Group::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, League $league): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupIds  = $this->normalizeGroupIds($validated['group_ids'] ?? []);
        unset($validated['group_ids']);
        $logoPath = $this->storeLogo($request);

        if ($logoPath !== null) {
            $this->deleteLogo($league->logo_path);
            $validated['logo_path'] = $logoPath;
        }

        $league->update($validated);
        $league->groups()->sync($groupIds);

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
            'stats'       => ['nullable', Rule::in(['active', 'deactive', 'upcoming', 'completed'])],
            'start_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:start_date'],
            'type'        => ['required', Rule::in(['single', 'doubles'])],
            'group_ids'   => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
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

    protected function storeLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $directory = public_path('public/admin/uploads/leagues');
        File::ensureDirectoryExists($directory);

        $file     = $request->file('logo');
        $filename = uniqid('league-', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'public/admin/uploads/leagues/' . $filename;
    }

    protected function deleteLogo(?string $path): void
    {
        if ($path === null) {
            return;
        }

        File::delete(public_path($path));
    }
}
