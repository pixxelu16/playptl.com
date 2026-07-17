<?php

namespace App\Http\Controllers;

use App\Models\GroupMatch;
use App\Models\GroupMatchPlayerUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AdminGalleryController extends Controller
{
    public function index()
    {
        abort_unless(Schema::hasTable('group_match_player_uploads'), 404);

        $uploads = GroupMatchPlayerUpload::query()
            ->with(['groupMatch.league', 'groupMatch.groupCard', 'groupMatch.group', 'uploadedBy'])
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->paginate(15);

        // Fetch matches for the upload dropdown selection
        $matches = GroupMatch::query()
            ->with(['league', 'groupCard', 'group', 'homeUser', 'awayUser'])
            ->latest()
            ->get();

        return view('admin.gallery.index', compact('uploads', 'matches'));
    }

    public function store(Request $request)
    {
        abort_unless(Schema::hasTable('group_match_player_uploads'), 404);

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'group_match_id' => ['nullable', 'integer', 'exists:group_matches,id'],
        ]);

        $dir = public_path('upload/group-match-uploads');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $file = $request->file('image');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $filename = 'admin-upload-' . time() . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
        $file->move($dir, $filename);
        $relative = 'upload/group-match-uploads/' . $filename;

        GroupMatchPlayerUpload::query()->create([
            'group_match_id' => $validated['group_match_id'] ?? null,
            'uploaded_by_user_id' => auth()->id(),
            'upload_date' => Carbon::today()->toDateString(),
            'image_path' => $relative,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('status', 'Photo uploaded successfully.');
    }

    public function update(Request $request, GroupMatchPlayerUpload $upload)
    {
        $validated = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'group_match_id' => ['nullable', 'integer', 'exists:group_matches,id'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old file
            $path = str_replace('\\', '/', trim((string) $upload->image_path));
            if ($path !== '') {
                $full = public_path($path);
                if (File::exists($full)) {
                    File::delete($full);
                }
            }

            $file = $request->file('image');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'admin-upload-' . time() . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
            $file->move(public_path('upload/group-match-uploads'), $filename);
            $upload->image_path = 'upload/group-match-uploads/' . $filename;
        }

        $upload->notes = $validated['notes'] ?? null;
        $upload->group_match_id = $validated['group_match_id'] ?? null;
        $upload->save();

        return back()->with('status', 'Photo updated successfully.');
    }

    public function destroy(GroupMatchPlayerUpload $upload)
    {
        $path = str_replace('\\', '/', trim((string) $upload->image_path));
        if ($path !== '') {
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $upload->delete();

        return back()->with('status', 'Photo deleted successfully.');
    }
}
