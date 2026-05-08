<?php

use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminGroupCardController;
use App\Http\Controllers\AdminGroupController;
use App\Http\Controllers\AdminLeagueGroupCardGroupController;
use App\Http\Controllers\AdminLeagueGroupCardPlayerController;
use App\Http\Controllers\AdminLeagueController;
use App\Http\Controllers\AdminLeagueManagementController;
use App\Http\Controllers\AdminPaymentHistoryController;
use App\Http\Controllers\AdminPlayerController;
use App\Http\Controllers\AdminPlayerLeagueRegistrationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterStripePaymentIntentController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\PlayerProfileController;
use App\Models\Announcement;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', function () {
    return view('home');
});

Route::get('/gallery', function () {
    return view('gallery');
})->name('gallery');

Route::get('/charity', function () {
    return view('charity');
})->name('charity');

Route::get('/league', function () {
    abort(404);
})->name('league');
Route::get('/league/{slug}', [LeagueController::class, 'overview'])->name('league.overview');
Route::get('/league/{leagueSlug}/{groupCardSlug}', [LeagueController::class, 'show'])->name('league.group');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::post('/register/payment-intent', RegisterStripePaymentIntentController::class)->name('register.payment-intent');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard', [
                'leaguesCount' => League::query()->count(),
                'announcementsCount' => Announcement::query()->count(),
                'groupsCount' => Group::query()->count(),
                'groupCardsCount' => GroupCard::query()->count(),
                'playersCount' => User::query()->where('role', \App\Enums\UserRole::Player)->count(),
                'paymentsCount' => PaymentHistory::query()->count(),
            ]);
        })->name('dashboard');

        Route::resource('leagues', AdminLeagueController::class);
        Route::get('league-management', [AdminLeagueManagementController::class, 'index'])->name('league-management.index');
        Route::get('league-management/{league}', [AdminLeagueManagementController::class, 'show'])->name('league-management.show');

        Route::get('league-management/{league}/group-cards/{groupCard}/groups', [AdminLeagueGroupCardGroupController::class, 'index'])->name('league-management.groups.index');
        Route::get('league-management/{league}/group-cards/{groupCard}/groups/create', [AdminLeagueGroupCardGroupController::class, 'create'])->name('league-management.groups.create');
        Route::post('league-management/{league}/group-cards/{groupCard}/groups', [AdminLeagueGroupCardGroupController::class, 'store'])->name('league-management.groups.store');

        Route::get('league-management/{league}/group-cards/{groupCard}/players', [AdminLeagueGroupCardPlayerController::class, 'index'])->name('league-management.players.index');
        Route::put('league-management/{league}/group-cards/{groupCard}/players/{registration}', [AdminLeagueGroupCardPlayerController::class, 'updateGroup'])->name('league-management.players.update-group');
        Route::resource('announcements', AdminAnnouncementController::class);
        Route::resource('groups', AdminGroupController::class);
        Route::resource('group-cards', AdminGroupCardController::class);
        Route::resource('players', AdminPlayerController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('players/{player}/league-registrations/create', [AdminPlayerLeagueRegistrationController::class, 'create'])->name('players.league-registrations.create');
        Route::post('players/{player}/league-registrations', [AdminPlayerLeagueRegistrationController::class, 'store'])->name('players.league-registrations.store');
        Route::get('payment-histories', [AdminPaymentHistoryController::class, 'index'])->name('payment-histories.index');

        Route::get('/profile', function () {
            return view('admin.profile');
        })->name('profile');

        Route::put('/profile', function (Request $request) {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'first_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:32'],
                'city' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:64'],
                'sex' => ['nullable', Rule::in(['male', 'female'])],
                'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            // Keep display name in sync with first/last name when provided.
            $composedName = trim(((string) ($validated['first_name'] ?? '')).' '.((string) ($validated['last_name'] ?? '')));
            if ($composedName !== '') {
                $validated['name'] = $composedName;
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
                $validated['avatar_path'] = $newPath;
            }

            $user->update($validated);

            return back()->with('status', 'Profile updated successfully.');
        })->name('profile.update');

        Route::get('/change-password', function () {
            return view('admin.change-password');
        })->name('password.edit');

        Route::put('/change-password', function (Request $request) {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $request->user()->update([
                'password' => $validated['password'],
            ]);

            return back()->with('status', 'Password changed successfully.');
        })->name('password.update');
    });

    Route::middleware('role:organiser')->prefix('organiser')->name('organiser.')->group(function () {
        Route::get('/dashboard', function () {
            return view('organiser.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:player')->prefix('player')->name('player.')->group(function () {
        Route::get('/dashboard', function (Request $request) {
            return redirect()->to($request->user()->playerProfileUrl());
        })->name('dashboard');
        Route::get('/my-profile', [PlayerProfileController::class, 'show'])->name('my-profile');
        Route::put('/profile', [PlayerProfileController::class, 'update'])->name('profile.update');
        Route::get('/change-password', function () {
            return view('player.change-password');
        })->name('password.edit');
        Route::put('/change-password', function (Request $request) {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $request->user()->update([
                'password' => $validated['password'],
            ]);

            return back()->with('status', 'Password changed successfully.');
        })->name('password.update');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
