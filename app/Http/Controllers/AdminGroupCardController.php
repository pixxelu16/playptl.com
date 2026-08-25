<?php

namespace App\Http\Controllers;

use App\Enums\GroupPlayoffFormat;
use App\Models\GroupCard;
use App\Models\Group;
use App\Models\Skill;
use App\Models\Category;
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
                ->with('category')
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
            'skills' => Skill::allSkills(),
            'categories' => Category::query()->orderBy('menu_order')->get(),
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
            'groupCard' => $groupCard->load(['groups', 'category']),
        ]);
    }

    public function edit(GroupCard $groupCard): View
    {
        return view('admin.group-cards.edit', [
            'groupCard' => $groupCard->load(['groups', 'category']),
            'groups' => Group::query()->orderBy('name')->get(),
            'playoffFormatOptions' => GroupPlayoffFormat::options(),
            'skills' => Skill::allSkills(),
            'categories' => Category::query()->orderBy('menu_order')->get(),
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
            'tag' => ['required', 'integer', 'exists:categories,id'],
            'players_count' => ['required', 'integer', 'min:0'],
            'groups_count' => ['nullable', 'integer', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'deactive'])],
            'skill_level_match' => ['nullable', 'array'],
            'skill_level_match.*' => ['string', 'max:32'],
            'playoff_format' => ['required', 'string', Rule::in(array_map(fn (GroupPlayoffFormat $f) => $f->value, GroupPlayoffFormat::cases()))],
            'playoff_quarter_spots' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:64'],
            'playoff_r16_spots' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:64'],
            'playoff_ppq_spots' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:64'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ]);

        $category = Category::findOrFail((int) $validated['tag']);
        $validated['category_id'] = $category->id;

        $catName = strtolower(trim((string) $category->name));
        $catType = strtolower(trim((string) $category->type));

        if (str_contains($catName, 'single')) {
            $tagStr = 'single';
        } elseif (str_contains($catName, 'double')) {
            $tagStr = 'doubles';
        } elseif (str_contains($catName, 'mixed')) {
            $tagStr = 'mixed';
        } elseif (str_contains($catName, 'youth')) {
            $tagStr = 'youth';
        } elseif ($catType === 'doubles' || (str_contains($catType, 'doubles') && ! str_contains($catType, 'single'))) {
            $tagStr = 'doubles';
        } elseif ($catType === 'single' || (str_contains($catType, 'single') && ! str_contains($catType, 'doubles'))) {
            $tagStr = 'single';
        } else {
            $tagStr = 'single';
        }
        $validated['tag'] = $tagStr;

        if (isset($validated['skill_level_match']) && is_array($validated['skill_level_match'])) {
            $validated['skill_level_match'] = implode(',', array_filter(array_map('trim', $validated['skill_level_match'])));
        }

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
