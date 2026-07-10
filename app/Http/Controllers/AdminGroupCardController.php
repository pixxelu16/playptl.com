<?php

namespace App\Http\Controllers;

use App\Enums\GroupPlayoffFormat;
use App\Models\GroupCard;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminGroupCardController extends Controller
{
    public function index(): View
    {
        return view('admin.group-cards.index', [
            'groupCards' => GroupCard::query()
                ->orderBy('display_order')
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.group-cards.create', [
            'groupCard' => new GroupCard([
                'status' => 'active',
                'playoff_format' => GroupPlayoffFormat::RoundOf16->value,
                'playoff_r16_spots' => 16,
            ]),
            'playoffFormatOptions' => GroupPlayoffFormat::options(),
            'groups' => Group::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupIds = array_values(array_unique(array_filter(array_map('intval', $validated['group_ids'] ?? []), fn ($v) => $v > 0)));
        unset($validated['group_ids']);
        $validated['slug'] = $this->generateSlug($validated['name']);

        $groupCard = GroupCard::create($validated);
        $groupCard->groups()->sync($groupIds);

        return redirect()->route('admin.group-cards.index')->with('status', 'Group created successfully.');
    }

    public function show(GroupCard $groupCard): View
    {
        return view('admin.group-cards.show', [
            'groupCard' => $groupCard->load('groups'),
        ]);
    }

    public function edit(GroupCard $groupCard): View
    {
        return view('admin.group-cards.edit', [
            'groupCard' => $groupCard->load('groups'),
            'groups' => Group::query()->orderBy('name')->get(),
            'playoffFormatOptions' => GroupPlayoffFormat::options(),
        ]);
    }

    public function update(Request $request, GroupCard $groupCard): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $groupIds = array_values(array_unique(array_filter(array_map('intval', $validated['group_ids'] ?? []), fn ($v) => $v > 0)));
        unset($validated['group_ids']);
        $validated['slug'] = $this->generateSlug($validated['name']);

        $groupCard->update($validated);
        $groupCard->groups()->sync($groupIds);

        return redirect()->route('admin.group-cards.index')->with('status', 'Group updated successfully.');
    }

    public function destroy(GroupCard $groupCard): RedirectResponse
    {
        $groupCard->delete();

        return redirect()->route('admin.group-cards.index')->with('status', 'Group deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tag' => ['required', Rule::in(['single', 'doubles', 'mixed', 'youth'])],
            'players_count' => ['required', 'integer', 'min:0'],
            'groups_count' => ['nullable', 'integer', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'deactive'])],
            'skill_level_match' => ['nullable', 'string', 'max:32', 'regex:/^$|^not-sure$|^[0-9]+(\.[0-9]+)?$/'],
            'playoff_format' => ['required', 'string', Rule::in(array_map(fn (GroupPlayoffFormat $f) => $f->value, GroupPlayoffFormat::cases()))],
            'playoff_quarter_spots' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:64'],
            'playoff_r16_spots' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:64'],
            'playoff_ppq_spots' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:64'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ]);

        if (($validated['skill_level_match'] ?? '') === '') {
            $validated['skill_level_match'] = null;
        }

        $format = GroupPlayoffFormat::resolveOrDefault($validated['playoff_format'] ?? null);
        $validated['playoff_quarter_spots'] = match ($format) {
            GroupPlayoffFormat::Top4QuarterRestR16 => max(1, (int) ($validated['playoff_quarter_spots'] ?? 4)),
            GroupPlayoffFormat::DirectQuarter => max(2, (int) ($validated['playoff_quarter_spots'] ?? 8)),
            default => null,
        };
        $validated['playoff_r16_spots'] = match ($format) {
            GroupPlayoffFormat::Top4QuarterRestR16, GroupPlayoffFormat::PrePreQR16 => max(2, (int) ($validated['playoff_r16_spots'] ?? ($format === GroupPlayoffFormat::PrePreQR16 ? 8 : 8))),
            GroupPlayoffFormat::RoundOf16 => max(2, (int) ($validated['playoff_r16_spots'] ?? 16)),
            default => null,
        };
        $validated['playoff_ppq_spots'] = match ($format) {
            GroupPlayoffFormat::PrePreQR16 => max(2, (int) ($validated['playoff_ppq_spots'] ?? 16)),
            default => null,
        };

        $existing = $request->route('group_card');
        if (! array_key_exists('groups_count', $validated) || $validated['groups_count'] === null) {
            $validated['groups_count'] = $existing?->groups_count ?? 0;
        }

        return $validated;
    }

    protected function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        return $baseSlug !== '' ? $baseSlug : 'group-card';
    }
}
