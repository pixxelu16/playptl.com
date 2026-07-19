<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Mail\AccountLockedNotificationMail;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\PasswordEncryptionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const MAX_ATTEMPTS = 3;

    public function create(): View
    {
        // Generate RSA public key for this page load/session
        $publicKey = PasswordEncryptionHelper::getPublicKey();

        return view('auth.login', [
            'publicKey' => $publicKey,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Decrypt the password on transit
        $decryptedPassword = PasswordEncryptionHelper::decrypt($credentials['password']);

        $remember = $request->boolean('remember');

        $user = User::where('email', strtolower($credentials['email']))->first();

        // Check if locked
        if ($user && $user->is_locked) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been locked due to too many failed login attempts. Please contact an administrator to unlock it.',
            ]);
        }

        // Standard Laravel Authenticate (with plain-text decrypted password)
        if (! $user || ! Hash::check($decryptedPassword, $user->getAuthPassword())) {
            if ($user) {
                $justLocked = $user->recordFailedLogin();

                if ($justLocked) {
                    $this->notifyAdminOfLockout($user);
                }

                if ($user->is_locked) {
                    throw ValidationException::withMessages([
                        'email' => 'Your account has been locked after ' . self::MAX_ATTEMPTS . ' failed attempts. An administrator has been notified.',
                    ]);
                }

                $remaining = self::MAX_ATTEMPTS - (int) $user->failed_login_attempts;
                $attemptsLeft = max(0, $remaining);

                throw ValidationException::withMessages([
                    'email' => 'These credentials do not match our records. You have ' . $attemptsLeft . ' attempt(s) remaining.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Success
        $user->resetFailedLogins();

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($request->user()->role === UserRole::Player) {
            return redirect()->to($request->user()->playerProfileUrl());
        }

        return redirect()->intended(route($request->user()->dashboardRouteName()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function notifyAdminOfLockout(User $lockedUser): void
    {
        try {
            $adminEmail = SiteSetting::getValue('contact_email');
            if ($adminEmail) {
                // Generate a cryptographically signed URL valid for 24 hours
                $unblockUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'admin.users.unlock-signed',
                    now()->addHours(24),
                    ['user' => $lockedUser->id]
                );

                Mail::to($adminEmail)->send(new AccountLockedNotificationMail($lockedUser, $unblockUrl));
            }
        } catch (\Throwable $e) {
            // Ignore mail failure
        }
    }
}
