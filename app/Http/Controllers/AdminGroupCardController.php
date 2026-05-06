<?php

namespace App\Http\Controllers;

use App\Models\GroupCard;
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
            'groupCard' => new GroupCard(['status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['slug'] = $this->generateSlug($validated['name']);

        GroupCard::create($validated);

        return redirect()->route('admin.group-cards.index')->with('status', 'Group card created successfully.');
    }

    public function show(GroupCard $groupCard): View
    {
        return view('admin.group-cards.show', [
            'groupCard' => $groupCard,
        ]);
    }

    public function edit(GroupCard $groupCard): View
    {
        return view('admin.group-cards.edit', [
            'groupCard' => $groupCard,
        ]);
    }

    public function update(Request $request, GroupCard $groupCard): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['slug'] = $this->generateSlug($validated['name']);

        $groupCard->update($validated);

        return redirect()->route('admin.group-cards.index')->with('status', 'Group card updated successfully.');
    }

    public function destroy(GroupCard $groupCard): RedirectResponse
    {
        $groupCard->delete();

        return redirect()->route('admin.group-cards.index')->with('status', 'Group card deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tag' => ['required', Rule::in(['single', 'doubles', 'mixed', 'youth'])],
            'players_count' => ['required', 'integer', 'min:0'],
            'groups_count' => ['required', 'integer', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'deactive'])],
        ]);
    }

    protected function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        return $baseSlug !== '' ? $baseSlug : 'group-card';
    }
}
