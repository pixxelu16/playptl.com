<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::latest('announcement_date')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'announcement' => new Announcement(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::create($this->validatedData($request));

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement): View
    {
        return view('admin.announcements.show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validatedData($request));

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'announcement_date' => ['required', 'date', 'after_or_equal:today'],
            'type' => ['required', Rule::in(['news', 'notice', 'update', 'event'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
