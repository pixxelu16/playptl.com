<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\PlayerAccountCreatedMail;

class AdminPlayerController extends Controller
{
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        $players = User::query()
            ->where('role', UserRole::Player)
            ->where('registration_type', $tab)
            ->with(['leagueRegistrations' => fn ($query) => $query->orderByDesc('id')])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.players.index', [
            'tab' => $tab,
            'players' => $players,
        ]);
    }

    public function create(Request $request): View
    {
        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        return view('admin.players.create', [
            'tab' => $tab,
            'player' => new User([
                'role' => UserRole::Player,
                'registration_type' => $tab,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'status' => ['required', Rule::in(['active', 'pending', 'suspend'])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $plainPassword = substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(12))), 0, 12);

        $player = User::create([
            ...$validated,
            'role' => UserRole::Player,
            'registration_type' => 'singles',
            'password' => $plainPassword,
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'avatar-'.$player->id.'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dir = public_path('upload/user-avatar');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $player->update(['avatar_path' => 'upload/user-avatar/'.$filename]);
        }

        try {
            Mail::to($player->email)->send(new PlayerAccountCreatedMail(
                userName: $player->name,
                email: $player->email,
                password: $plainPassword,
                loginUrl: route('login'),
            ));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.players.index', ['tab' => $tab])
                ->with('status', 'Player created, but email could not be sent. Please check mail settings.');
        }

        return redirect()
            ->route('admin.players.index', ['tab' => $tab])
            ->with('status', 'Player created and login details emailed successfully.');
    }

    public function edit(Request $request, User $player): View
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        return view('admin.players.edit', [
            'tab' => $tab,
            'player' => $player,
        ]);
    }

    public function update(Request $request, User $player)
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $tab = (string) $request->query('tab', (string) $player->registration_type);
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'status' => ['required', Rule::in(['active', 'pending', 'suspend'])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Enforce role stays as player
        $validated['role'] = UserRole::Player;

        // Email should not be editable from admin players screen.
        unset($validated['email']);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'avatar-'.$player->id.'-'.bin2hex(random_bytes(6)).'.'.$ext;
            $dir = public_path('upload/user-avatar');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $file->move($dir, $filename);

            $newPath = 'upload/user-avatar/'.$filename;
            $oldPath = (string) ($player->avatar_path ?? '');
            if ($oldPath !== '' && $oldPath !== 'upload/user-avatar/default-user-pic.png') {
                $oldFull = public_path($oldPath);
                if (File::exists($oldFull)) {
                    File::delete($oldFull);
                }
            }
            $validated['avatar_path'] = $newPath;
        }

        $player->update($validated);

        return redirect()
            ->route('admin.players.index', ['tab' => $tab])
            ->with('status', 'Player updated successfully.');
    }

    public function destroy(Request $request, User $player)
    {
        abort_unless($player->role === UserRole::Player, Response::HTTP_NOT_FOUND);

        $tab = (string) $request->query('tab', 'singles');
        if (! in_array($tab, ['singles', 'doubles'], true)) {
            $tab = 'singles';
        }

        $player->delete();

        return redirect()
            ->route('admin.players.index', ['tab' => $tab])
            ->with('status', 'Player deleted successfully.');
    }
}

