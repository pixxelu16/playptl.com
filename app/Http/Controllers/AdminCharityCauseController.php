<?php

namespace App\Http\Controllers;

use App\Models\CharityCause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCharityCauseController extends Controller
{
    public function index(): View
    {
        return view('admin.charity-causes.index', [
            'charityCauses' => CharityCause::query()
                ->orderBy('display_order')
                ->orderBy('title')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.charity-causes.create', [
            'charityCause' => new CharityCause([
                'is_active' => true,
                'display_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['image_path'] = $this->storeImage($request);
        $validated['slug'] = $this->generateUniqueSlug((string) $validated['title']);

        CharityCause::create($validated);

        return redirect()->route('admin.charity-causes.index')->with('status', 'Charity cause created successfully.');
    }

    public function show(CharityCause $charityCause): View
    {
        return view('admin.charity-causes.show', [
            'charityCause' => $charityCause,
        ]);
    }

    public function edit(CharityCause $charityCause): View
    {
        return view('admin.charity-causes.edit', [
            'charityCause' => $charityCause,
        ]);
    }

    public function update(Request $request, CharityCause $charityCause): RedirectResponse
    {
        $validated = $this->validatedData($request, updating: true);
        $imagePath = $this->storeImage($request);

        if ($imagePath !== null) {
            $this->deleteImage($charityCause->image_path);
            $validated['image_path'] = $imagePath;
        }

        if ((string) $validated['title'] !== (string) $charityCause->title) {
            $validated['slug'] = $this->generateUniqueSlug((string) $validated['title'], $charityCause->id);
        }

        $charityCause->update($validated);

        return redirect()->route('admin.charity-causes.index')->with('status', 'Charity cause updated successfully.');
    }

    public function destroy(CharityCause $charityCause): RedirectResponse
    {
        $this->deleteImage($charityCause->image_path);
        $charityCause->delete();

        return redirect()->route('admin.charity-causes.index')->with('status', 'Charity cause deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request, bool $updating = false): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => [$updating ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['image']);

        return $validated;
    }

    protected function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $directory = public_path('upload/charity-causes');
        File::ensureDirectoryExists($directory);

        $file = $request->file('image');
        $filename = 'charity-cause-'.bin2hex(random_bytes(8)).'.'.strtolower((string) $file->getClientOriginalExtension());
        $file->move($directory, $filename);

        return 'upload/charity-causes/'.$filename;
    }

    protected function deleteImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'charity-cause';
        $slug = $baseSlug;
        $counter = 2;

        while (
            CharityCause::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
