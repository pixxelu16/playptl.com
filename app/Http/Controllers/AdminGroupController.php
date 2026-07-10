<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminGroupController extends Controller
{
    public function index(): View
    {
        return view('admin.groups.index', [
            'groups' => Group::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.groups.create', [
            'group' => new Group(['status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Group::create($this->validatedData($request));

        return redirect()->route('admin.groups.index')->with('status', 'Subgroup created successfully.');
    }

    public function show(Group $group): View
    {
        return view('admin.groups.show', [
            'group' => $group,
        ]);
    }

    public function edit(Group $group): View
    {
        return view('admin.groups.edit', [
            'group' => $group,
        ]);
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $group->update($this->validatedData($request));

        return redirect()->route('admin.groups.index')->with('status', 'Subgroup updated successfully.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.groups.index')->with('status', 'Subgroup deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'deactive'])],
        ]);
    }
}
