<?php

use App\Enums\UserRole;
use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminGroupCardController;
use App\Http\Controllers\AdminGroupController;
use App\Http\Controllers\AdminGroupMatchController;
use App\Http\Controllers\AdminLeagueController;
use App\Http\Controllers\AdminLeagueGroupCardAssignPlayerController;
use App\Http\Controllers\AdminLeagueGroupCardGroupController;
use App\Http\Controllers\AdminLeagueGroupCardPlayerController;
use App\Http\Controllers\AdminOfficialPartnerController;
use App\Http\Controllers\AdminLeagueGroupCardPointsController;
use App\Http\Controllers\AdminLeagueGroupCardQualifierController;
use App\Http\Controllers\AdminLeagueManagementController;
use App\Http\Controllers\AdminCharityCauseController;
use App\Http\Controllers\AdminCharityDonationController;
use App\Http\Controllers\AdminContactSettingController;
use App\Http\Controllers\AdminPaymentHistoryController;
use App\Http\Controllers\AdminSkillController;
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
use App\Http\Controllers\LegalPageController;
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

Route::get('/privacy-policy', [LegalPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-and-conditions', [LegalPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/rules', [\App\Http\Controllers\RulesController::class, 'index'])->name('rules');
Route::get('/rules-and-regulations', [\App\Http\Controllers\RulesController::class, 'index']);

Route::get('/league', function () {
    abort(404);
})->name('league');
Route::get('/league/{slug}', [LeagueController::class, 'overview'])->name('league.overview');
Route::get('/league/{leagueSlug}/{groupCardSlug}', [LeagueController::class, 'show'])->name('league.group');
Route::get('/player-services/mentors', [\App\Http\Controllers\PlayerServicesController::class, 'mentors'])->name('player-services.mentors');
Route::get('/player-services/coaches', [\App\Http\Controllers\PlayerServicesController::class, 'coaches'])->name('player-services.coaches');
Route::get('/player-services/mentor/{user:username}', [\App\Http\Controllers\PlayerServicesController::class, 'showProfile'])->name('player-services.mentor.show');
Route::get('/player-services/coach/{user:username}', [\App\Http\Controllers\PlayerServicesController::class, 'showProfile'])->name('player-services.coach.show');

// ── Student: Booking ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/booking/{user:username}',              [\App\Http\Controllers\BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/payment-intent',              [\App\Http\Controllers\BookingController::class, 'createPaymentIntent'])->name('booking.payment-intent');
    Route::post('/booking',                             [\App\Http\Controllers\BookingController::class, 'store'])->name('booking.store');
    Route::get('/student/bookings',                     [\App\Http\Controllers\BookingController::class, 'myBookings'])->name('student.bookings');
    Route::get('/student/bookings/{booking}',           [\App\Http\Controllers\BookingController::class, 'show'])->name('student.booking.show');
    Route::patch('/student/bookings/{booking}/cancel',  [\App\Http\Controllers\BookingController::class, 'cancel'])->name('student.booking.cancel');
});

// ── Provider (Mentor / Coach): Booking Management ────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/provider/bookings',                         [\App\Http\Controllers\ProviderBookingController::class, 'index'])->name('provider.bookings');
    Route::get('/provider/bookings/{booking}',               [\App\Http\Controllers\ProviderBookingController::class, 'show'])->name('provider.booking.show');
    Route::patch('/provider/bookings/{booking}/accept',      [\App\Http\Controllers\ProviderBookingController::class, 'accept'])->name('provider.booking.accept');
    Route::patch('/provider/bookings/{booking}/reject',      [\App\Http\Controllers\ProviderBookingController::class, 'reject'])->name('provider.booking.reject');
});


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
        Route::get('/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('leagues', AdminLeagueController::class);
        Route::patch('leagues/{league}/toggle-menu', [AdminLeagueController::class, 'toggleMenu'])->name('leagues.toggle-menu');
        Route::patch('leagues/{league}/toggle-realize', [AdminLeagueController::class, 'toggleRealize'])->name('leagues.toggle-realize');
        Route::get('league-management', [AdminLeagueManagementController::class, 'index'])->name('league-management.index');
        Route::get('league-management/{league}', [AdminLeagueManagementController::class, 'show'])->name('league-management.show');
        Route::post('league-management/{league}/finish', [AdminLeagueManagementController::class, 'finish'])->name('league-management.finish');

        Route::get('league-management/{league}/group-cards/{groupCard}/groups', [AdminLeagueGroupCardGroupController::class, 'index'])->name('league-management.groups.index');
        Route::get('league-management/{league}/group-cards/{groupCard}/groups/create', [AdminLeagueGroupCardGroupController::class, 'create'])->name('league-management.groups.create');
        Route::post('league-management/{league}/group-cards/{groupCard}/groups', [AdminLeagueGroupCardGroupController::class, 'store'])->name('league-management.groups.store');

        Route::get('league-management/{league}/group-cards/{groupCard}/assign-players', [AdminLeagueGroupCardAssignPlayerController::class, 'index'])->name('league-management.assign-players.index');
        Route::post('league-management/{league}/group-cards/{groupCard}/assign-players', [AdminLeagueGroupCardAssignPlayerController::class, 'store'])->name('league-management.assign-players.store');

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
        Route::resource('official-partners', AdminOfficialPartnerController::class)->except(['show']);
        Route::get('contact-settings', [AdminContactSettingController::class, 'edit'])->name('contact-settings.edit');
        Route::put('contact-settings', [AdminContactSettingController::class, 'update'])->name('contact-settings.update');
        Route::post('contact-settings/test-smtp', [AdminContactSettingController::class, 'testSmtp'])->name('contact-settings.test-smtp');
        Route::resource('groups', AdminGroupController::class);
        Route::resource('group-cards', AdminGroupCardController::class);
        Route::resource('players', AdminPlayerController::class)->only(['index', 'edit', 'update', 'destroy']);
        Route::resource('roles', \App\Http\Controllers\AdminRoleController::class);
        Route::get('payment-histories', [AdminPaymentHistoryController::class, 'index'])->name('payment-histories.index');
        Route::get('charity-donations', [AdminCharityDonationController::class, 'index'])->name('charity-donations.index');
        Route::get('charity-donations/email-recipient-count', [AdminCharityDonationController::class, 'recipientCount'])->name('charity-donations.email-recipient-count');
        Route::post('charity-donations/send-email', [AdminCharityDonationController::class, 'sendEmail'])->name('charity-donations.send-email');
        Route::resource('charity-causes', AdminCharityCauseController::class);
        Route::resource('skills', AdminSkillController::class);
        Route::get('rules', [\App\Http\Controllers\AdminRulesController::class, 'index'])->name('rules.index');
        Route::post('rules/sections', [\App\Http\Controllers\AdminRulesController::class, 'storeSection'])->name('rules.store-section');
        Route::delete('rules/sections/{section}', [\App\Http\Controllers\AdminRulesController::class, 'destroySection'])->name('rules.destroy-section');
        Route::post('rules/sections/{section}/items', [\App\Http\Controllers\AdminRulesController::class, 'storeItem'])->name('rules.store-item');
        Route::put('rules/items/{item}', [\App\Http\Controllers\AdminRulesController::class, 'updateItem'])->name('rules.update-item');
        Route::delete('rules/items/{item}', [\App\Http\Controllers\AdminRulesController::class, 'destroyItem'])->name('rules.destroy-item');
        Route::post('rules/version', [\App\Http\Controllers\AdminRulesController::class, 'updateVersion'])->name('rules.update-version');
        Route::post('rules/faqs', [\App\Http\Controllers\AdminRulesController::class, 'storeFaq'])->name('rules.store-faq');
        Route::delete('rules/faqs/{faq}', [\App\Http\Controllers\AdminRulesController::class, 'destroyFaq'])->name('rules.destroy-faq');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::get('provider-requests', [\App\Http\Controllers\AdminProviderRequestController::class, 'index'])->name('provider-requests.index');
        Route::patch('provider-requests/{user}/approve', [\App\Http\Controllers\AdminProviderRequestController::class, 'approve'])->name('provider-requests.approve');
        Route::patch('provider-requests/{user}/reject', [\App\Http\Controllers\AdminProviderRequestController::class, 'reject'])->name('provider-requests.reject');
        Route::resource('users', \App\Http\Controllers\AdminUserController::class);
        Route::post('users/{user}/unblock', [\App\Http\Controllers\AdminUserController::class, 'unblock'])->name('users.unblock');
        // Secure signed route to unlock account directly from email
        Route::get('users/{user}/unlock-signed', [\App\Http\Controllers\AdminUserController::class, 'unlockSigned'])
            ->name('users.unlock-signed')
            ->middleware('signed');

        // Gallery management
        Route::get('gallery', [\App\Http\Controllers\AdminGalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [\App\Http\Controllers\AdminGalleryController::class, 'store'])->name('gallery.store');
        Route::put('gallery/{upload}', [\App\Http\Controllers\AdminGalleryController::class, 'update'])->name('gallery.update');
        Route::delete('gallery/{upload}', [\App\Http\Controllers\AdminGalleryController::class, 'destroy'])->name('gallery.destroy');

        // Booking management
        Route::get('bookings', [\App\Http\Controllers\AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [\App\Http\Controllers\AdminBookingController::class, 'show'])->name('bookings.show');
        Route::patch('bookings/{booking}/mark-paid', [\App\Http\Controllers\AdminBookingController::class, 'markPaid'])->name('bookings.mark-paid');
        Route::patch('bookings/{booking}/status', [\App\Http\Controllers\AdminBookingController::class, 'updateStatus'])->name('bookings.update-status');

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
                'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->symbols()],
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
        Route::post('/my-profile/become-student', [PlayerProfileController::class, 'becomeStudent'])->name('become-student');
        Route::post('/my-profile/become-mentor', [PlayerProfileController::class, 'becomeMentor'])->name('become-mentor');
        Route::post('/my-profile/team-name', [PlayerProfileController::class, 'updateTeamName'])->name('profile.team-name.update');
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
                'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->symbols()],
            ]);

            $request->user()->update([
                'password' => $validated['password'],
            ]);

            return back()->with('status', 'Password changed successfully.');
        })->name('password.update');
    });

    Route::middleware('role:mentor')->prefix('mentor')->name('mentor.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\MentorController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\MentorController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\MentorController::class, 'updateProfile'])->name('profile.update');
        Route::put('/change-password', [\App\Http\Controllers\MentorController::class, 'updatePassword'])->name('password.update');
        Route::get('/transactions', [\App\Http\Controllers\ProviderTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{booking}', [\App\Http\Controllers\ProviderTransactionController::class, 'show'])->name('transactions.show');
    });

    Route::middleware('role:coach')->prefix('coach')->name('coach.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\CoachController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\CoachController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\CoachController::class, 'updateProfile'])->name('profile.update');
        Route::put('/change-password', [\App\Http\Controllers\CoachController::class, 'updatePassword'])->name('password.update');
        Route::get('/transactions', [\App\Http\Controllers\ProviderTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{booking}', [\App\Http\Controllers\ProviderTransactionController::class, 'show'])->name('transactions.show');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\StudentController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\StudentController::class, 'updateProfile'])->name('profile.update');
        Route::put('/change-password', [\App\Http\Controllers\StudentController::class, 'updatePassword'])->name('password.update');
    });

    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileAvatarController::class, 'update'])->name('profile.avatar.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
