<?php

use App\Enums\UserRole;
use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminGroupCardController;
use App\Http\Controllers\AdminGroupController;
use App\Http\Controllers\AdminGroupMatchController;
use App\Http\Controllers\AdminLeagueController;
use App\Http\Controllers\AdminLeagueGroupCardGroupController;
use App\Http\Controllers\AdminLeagueGroupCardPlayerController;
use App\Http\Controllers\AdminLeagueGroupCardPointsController;
use App\Http\Controllers\AdminLeagueGroupCardQualifierController;
use App\Http\Controllers\AdminLeagueManagementController;
use App\Http\Controllers\AdminCharityCauseController;
use App\Http\Controllers\AdminCharityDonationController;
use App\Http\Controllers\AdminPaymentHistoryController;
use App\Http\Controllers\CharityCauseContributionController;
use App\Http\Controllers\CharityController;
use App\Http\Controllers\CharityDonationController;
use App\Http\Controllers\AdminPlayerController;
use App\Http\Controllers\AdminPlayoffMatchController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegisterStripePaymentIntentController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\PlayerProfileController;
use App\Models\Announcement;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', HomeController::class);

Route::get('/gallery', GalleryController::class)->name('gallery');

Route::get('/charity', [CharityController::class, 'show'])->name('charity');
Route::get('/charity/cause/{charityCause:slug}', [CharityController::class, 'showCause'])->name('charity.cause');
Route::post('/charity/cause/{charityCause:slug}/contribute', [CharityCauseContributionController::class, 'store'])->name('charity.cause.contribute');
Route::post('/charity/donation/payment-intent', [CharityDonationController::class, 'createPaymentIntent'])->name('charity.donation.payment-intent');
Route::post('/charity/donation', [CharityDonationController::class, 'store'])->name('charity.donation.store');

Route::get('/league', function () {
    abort(404);
})->name('league');
Route::get('/league/{slug}', [LeagueController::class, 'overview'])->name('league.overview');
Route::get('/league/{leagueSlug}/{groupCardSlug}', [LeagueController::class, 'show'])->name('league.group');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/register/tournament-groups', \App\Http\Controllers\Auth\TournamentRegistrationGroupsController::class)->name('register.tournament-groups');
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
                'playersCount' => User::query()->where('role', UserRole::Player)->count(),
                'paymentsCount' => PaymentHistory::query()->count(),
            ]);
        })->name('dashboard');

        Route::resource('leagues', AdminLeagueController::class);
        Route::get('league-management', [AdminLeagueManagementController::class, 'index'])->name('league-management.index');
        Route::get('league-management/{league}', [AdminLeagueManagementController::class, 'show'])->name('league-management.show');
        Route::post('league-management/{league}/finish', [AdminLeagueManagementController::class, 'finish'])->name('league-management.finish');

        Route::get('league-management/{league}/group-cards/{groupCard}/groups', [AdminLeagueGroupCardGroupController::class, 'index'])->name('league-management.groups.index');
        Route::get('league-management/{league}/group-cards/{groupCard}/groups/create', [AdminLeagueGroupCardGroupController::class, 'create'])->name('league-management.groups.create');
        Route::post('league-management/{league}/group-cards/{groupCard}/groups', [AdminLeagueGroupCardGroupController::class, 'store'])->name('league-management.groups.store');

        Route::get('league-management/{league}/group-cards/{groupCard}/players', [AdminLeagueGroupCardPlayerController::class, 'index'])->name('league-management.players.index');
        Route::put('league-management/{league}/group-cards/{groupCard}/players/{registration}', [AdminLeagueGroupCardPlayerController::class, 'updateGroup'])->name('league-management.players.update-group');
        Route::put('league-management/{league}/group-cards/{groupCard}/players/{registration}/partner', [AdminLeagueGroupCardPlayerController::class, 'updatePartner'])->name('league-management.players.update-partner');
        Route::put('league-management/{league}/group-cards/{groupCard}/players/{registration}/sub-group', [AdminLeagueGroupCardPlayerController::class, 'updateSubGroup'])->name('league-management.players.update-subgroup');

        Route::get('league-management/{league}/group-cards/{groupCard}/points', [AdminLeagueGroupCardPointsController::class, 'index'])->name('league-management.points.index');
        Route::get('league-management/{league}/group-cards/{groupCard}/qualifier', [AdminLeagueGroupCardQualifierController::class, 'index'])->name('league-management.qualifier.index');
        Route::put('league-management/{league}/group-cards/{groupCard}/qualifier', [AdminLeagueGroupCardQualifierController::class, 'update'])->name('league-management.qualifier.update');
        Route::post('league-management/{league}/group-cards/{groupCard}/qualifier/clear', [AdminLeagueGroupCardQualifierController::class, 'clearAll'])->name('league-management.qualifier.clear');
        Route::get('league-management/{league}/group-cards/{groupCard}/matches', [AdminGroupMatchController::class, 'index'])->name('league-management.matches.index');
        Route::post('league-management/{league}/group-cards/{groupCard}/matches', [AdminGroupMatchController::class, 'store'])->name('league-management.matches.store');
        Route::post('league-management/{league}/group-cards/{groupCard}/matches/schedule-dates', [AdminGroupMatchController::class, 'saveScheduleDates'])->name('league-management.matches.save-schedule-dates');
        Route::post('league-management/{league}/group-cards/{groupCard}/matches/cancel-schedule', [AdminGroupMatchController::class, 'cancelSchedule'])->name('league-management.matches.cancel-schedule');
        Route::post('league-management/{league}/group-cards/{groupCard}/matches/generate-round-robin', [AdminGroupMatchController::class, 'generateRoundRobin'])->name('league-management.matches.generate-round-robin');
        Route::put('league-management/{league}/group-cards/{groupCard}/matches/{groupMatch}', [AdminGroupMatchController::class, 'update'])->name('league-management.matches.update');
        Route::delete('league-management/{league}/group-cards/{groupCard}/matches/{groupMatch}', [AdminGroupMatchController::class, 'destroy'])->name('league-management.matches.destroy');

        Route::get('league-management/{league}/group-cards/{groupCard}/playoffs', [AdminPlayoffMatchController::class, 'index'])->name('league-management.playoffs.index');
        Route::post('league-management/{league}/group-cards/{groupCard}/playoffs/dates', [AdminPlayoffMatchController::class, 'savePlayoffDates'])->name('league-management.playoffs.dates');
        Route::post('league-management/{league}/group-cards/{groupCard}/playoffs/start', [AdminPlayoffMatchController::class, 'startPlayoffs'])->name('league-management.playoffs.start');
        Route::post('league-management/{league}/group-cards/{groupCard}/playoffs/close', [AdminPlayoffMatchController::class, 'closePlayoffs'])->name('league-management.playoffs.close');
        Route::post('league-management/{league}/group-cards/{groupCard}/playoffs/bracket', [AdminPlayoffMatchController::class, 'storeBracket'])->name('league-management.playoffs.store-bracket');
        Route::post('league-management/{league}/group-cards/{groupCard}/playoffs/rebuild', [AdminPlayoffMatchController::class, 'rebuildFromQualifier'])->name('league-management.playoffs.rebuild');
        Route::post('league-management/{league}/group-cards/{groupCard}/playoffs/pull-winners', [AdminPlayoffMatchController::class, 'pullWinners'])->name('league-management.playoffs.pull-winners');
        Route::put('league-management/{league}/group-cards/{groupCard}/playoffs/{playoffMatch}', [AdminPlayoffMatchController::class, 'update'])->name('league-management.playoffs.update');
        Route::resource('announcements', AdminAnnouncementController::class);
        Route::resource('groups', AdminGroupController::class);
        Route::resource('group-cards', AdminGroupCardController::class);
        Route::resource('players', AdminPlayerController::class)->only(['index', 'edit', 'update', 'destroy']);
        Route::get('payment-histories', [AdminPaymentHistoryController::class, 'index'])->name('payment-histories.index');
        Route::get('charity-donations', [AdminCharityDonationController::class, 'index'])->name('charity-donations.index');
        Route::get('charity-donations/email-recipient-count', [AdminCharityDonationController::class, 'recipientCount'])->name('charity-donations.email-recipient-count');
        Route::post('charity-donations/send-email', [AdminCharityDonationController::class, 'sendEmail'])->name('charity-donations.send-email');
        Route::resource('charity-causes', AdminCharityCauseController::class);

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
        Route::get('/my-profile/password', [PlayerProfileController::class, 'showPassword'])->name('profile.password');
        Route::get('/my-profile/location', [PlayerProfileController::class, 'showLocation'])->name('profile.location');
        Route::post('/my-profile/location', [PlayerProfileController::class, 'updateMatchLocation'])->name('profile.location.update');
        Route::post('/my-profile/match-result', [PlayerProfileController::class, 'updateMatchResult'])->name('profile.match.result');
        Route::get('/my-profile/upload', [PlayerProfileController::class, 'showUpload'])->name('profile.upload');
        Route::post('/my-profile/upload', [PlayerProfileController::class, 'storeMatchUpload'])->name('profile.upload.store');
        Route::delete('/my-profile/upload/{upload}', [PlayerProfileController::class, 'destroyMatchUpload'])->name('profile.upload.destroy');
        Route::delete('/my-profile/playoff-upload/{upload}', [PlayerProfileController::class, 'destroyPlayoffMatchUpload'])->name('profile.playoff-upload.destroy');
        Route::get('/my-profile', [PlayerProfileController::class, 'show'])->name('my-profile');
        Route::get('/my-profile/choose-league', [PlayerProfileController::class, 'showChooseLeague'])->name('profile.league');
        Route::get('/my-profile/choose-league/partner-lookup', [PlayerProfileController::class, 'lookupLeaguePartner'])->name('profile.league.partner-lookup');
        Route::get('/my-profile/choose-league/tournament-groups', \App\Http\Controllers\Auth\TournamentRegistrationGroupsController::class)->name('profile.league.tournament-groups');
        Route::post('/my-profile/choose-league/payment-intent', [PlayerProfileController::class, 'createLeaguePaymentIntent'])->name('profile.league.payment-intent');
        Route::post('/my-profile/choose-league', [PlayerProfileController::class, 'storeLeagueRegistration'])->name('profile.league.store');
        Route::put('/profile', [PlayerProfileController::class, 'update'])->name('profile.update');
        Route::get('/change-password', function () {
            return redirect()->route('player.profile.password');
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
