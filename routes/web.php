<?php

use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminGroupCardController;
use App\Http\Controllers\AdminGroupController;
use App\Http\Controllers\AdminLeagueController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\LeagueController;
use App\Models\Announcement;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use Illuminate\Http\Request;
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
            ]);
        })->name('dashboard');

        Route::resource('leagues', AdminLeagueController::class);
        Route::resource('announcements', AdminAnnouncementController::class);
        Route::resource('groups', AdminGroupController::class);
        Route::resource('group-cards', AdminGroupCardController::class);

        Route::get('/profile', function () {
            return view('admin.profile');
        })->name('profile');

        Route::put('/profile', function (Request $request) {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            ]);

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
        Route::get('/dashboard', function () {
            return view('player.dashboard');
        })->name('dashboard');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
