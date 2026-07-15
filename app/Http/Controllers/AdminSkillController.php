<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSkillController extends Controller
{
    public function index(): View
    {
        return view('admin.skills.index', [
            'skills' => Skill::query()
                ->orderBy('display_order')
                ->orderBy('value')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.skills.create', [
            'skill' => new Skill([
                'display_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:32', 'unique:skills,value'],
            'display_order' => ['required', 'integer', 'min:0'],
        ]);

        Skill::create($validated);

        return redirect()->route('admin.skills.index')->with('status', 'Skill created successfully.');
    }

    public function edit(Skill $skill): View
    {
        return view('admin.skills.edit', [
            'skill' => $skill,
        ]);
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:32', 'unique:skills,value,' . $skill->id],
            'display_order' => ['required', 'integer', 'min:0'],
        ]);

        $skill->update($validated);

        return redirect()->route('admin.skills.index')->with('status', 'Skill updated successfully.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        return redirect()->route('admin.skills.index')->with('status', 'Skill deleted successfully.');
    }
}
