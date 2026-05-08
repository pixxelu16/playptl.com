<?php

namespace App\Http\Controllers;

use App\Models\LeagueRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PlayerProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('player.my-profile', $this->profilePayload($request));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'ntrp' => ['nullable', 'string', 'max:32', 'regex:/^$|^not-sure$|^[0-9]+(\.[0-9]+)?$/'],
            'home_court' => ['nullable', 'string', 'max:255'],
            'dominant_hand' => ['nullable', 'in:Right,Left,Ambidextrous'],
            'league_id' => ['nullable', 'integer'],
            'group_card_id' => ['nullable', 'integer'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $updates = [];
        foreach (['first_name', 'last_name', 'phone', 'city'] as $column) {
            if (array_key_exists($column, $validated)) {
                $updates[$column] = $validated[$column] ?? null;
            }
        }

        $composedName = trim(((string) ($validated['first_name'] ?? '')).' '.((string) ($validated['last_name'] ?? '')));
        if ($composedName !== '') {
            $updates['name'] = $composedName;
        }

        foreach (['date_of_birth', 'home_court', 'dominant_hand'] as $column) {
            if (array_key_exists($column, $validated) && Schema::hasColumn('users', $column)) {
                $updates[$column] = $validated[$column] ?? null;
            }
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'avatar-'.$user->id.'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dir = public_path('upload/user-avatar');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file->move($dir, $filename);

            $newPath = 'upload/user-avatar/'.$filename;
            $oldPath = (string) ($user->avatar_path ?? '');
            if ($oldPath !== '' && $oldPath !== 'upload/user-avatar/default-user-pic.png') {
                $oldFull = public_path($oldPath);
                if (File::exists($oldFull)) {
                    File::delete($oldFull);
                }
            }
            $updates['avatar_path'] = $newPath;
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        $registration = $this->profileRegistration($request);
        if ($registration && array_key_exists('ntrp', $validated)) {
            $registration->update(['skill_level' => $validated['ntrp'] ?: null]);
        }

        return redirect()->route('player.my-profile')->with('status', 'Profile updated successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function profilePayload(Request $request): array
    {
        $user = $request->user();
        $registration = $this->profileRegistration($request);

        $firstName = trim((string) ($user->first_name ?? ''));
        $lastName = trim((string) ($user->last_name ?? ''));
        if ($firstName === '' && $lastName === '') {
            $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $division = (string) ($registration?->groupCard?->name ?? '—');
        $group = (string) ($registration?->group?->name ?? '—');

        return [
            'leagueId' => (int) ($registration?->league_id ?? 0),
            'groupCardId' => (int) ($registration?->group_card_id ?? 0),
            'myProfile' => [
                'name' => trim((string) $user->name) !== '' ? (string) $user->name : trim($firstName.' '.$lastName),
                'roleLine' => 'Player - '.$group,
                'avatarUrl' => asset($user->avatar_path ?: 'upload/user-avatar/default-user-pic.png'),
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dob' => $user->date_of_birth?->format('Y-m-d') ?? '',
                'ntrp' => (string) ($registration?->skill_level ?? ''),
                'email' => (string) $user->email,
                'phone' => (string) ($user->phone ?? ''),
                'city' => (string) ($user->city ?? ''),
                'division' => $division,
                'group' => $group,
                'homeCourt' => (string) ($user->home_court ?? ''),
                'dominantHand' => (string) ($user->dominant_hand ?? 'Right'),
            ],
        ];
    }

    protected function profileRegistration(Request $request): ?LeagueRegistration
    {
        $query = $request->user()
            ->leagueRegistrations()
            ->with(['league', 'groupCard', 'group'])
            ->whereHas('league', fn ($q) => $q->where('stats', 'active'))
            ->whereHas('groupCard', fn ($q) => $q->where('status', 'active'));

        if ($request->filled('league_id')) {
            $query->where('league_id', (int) $request->input('league_id'));
        }
        if ($request->filled('group_card_id')) {
            $query->where('group_card_id', (int) $request->input('group_card_id'));
        }

        return $query->latest('id')->first();
    }
}
