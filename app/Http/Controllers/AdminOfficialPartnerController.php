<?php

namespace App\Http\Controllers;

use App\Models\OfficialPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminOfficialPartnerController extends Controller
{
    public function index(): View
    {
        return view('admin.official-partners.index', [
            'officialPartners' => OfficialPartner::query()
                ->orderBy('display_order')
                ->orderBy('name')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.official-partners.create', [
            'officialPartner' => new OfficialPartner([
                'is_active' => true,
                'display_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['logo_path'] = $this->storeLogo($request);

        OfficialPartner::create($validated);

        return redirect()->route('admin.official-partners.index')->with('status', 'Official partner created successfully.');
    }

    public function edit(OfficialPartner $officialPartner): View
    {
        return view('admin.official-partners.edit', [
            'officialPartner' => $officialPartner,
        ]);
    }

    public function update(Request $request, OfficialPartner $officialPartner): RedirectResponse
    {
        $validated = $this->validatedData($request, updating: true);
        $logoPath = $this->storeLogo($request);

        if ($logoPath !== null) {
            $this->deleteLogo($officialPartner->logo_path);
            $validated['logo_path'] = $logoPath;
        }

        $officialPartner->update($validated);

        return redirect()->route('admin.official-partners.index')->with('status', 'Official partner updated successfully.');
    }

    public function destroy(OfficialPartner $officialPartner): RedirectResponse
    {
        $this->deleteLogo($officialPartner->logo_path);
        $officialPartner->delete();

        return redirect()->route('admin.official-partners.index')->with('status', 'Official partner deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request, bool $updating = false): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => [$updating ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['logo']);

        return $validated;
    }

    protected function storeLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $directory = public_path('upload/official-partners');
        File::ensureDirectoryExists($directory);

        $file = $request->file('logo');
        $filename = 'official-partner-'.bin2hex(random_bytes(8)).'.'.strtolower((string) $file->getClientOriginalExtension());
        $file->move($directory, $filename);

        return 'upload/official-partners/'.$filename;
    }

    protected function deleteLogo(?string $path): void
    {
        if ($path === null || $path === '' || ! str_starts_with($path, 'upload/official-partners/')) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
