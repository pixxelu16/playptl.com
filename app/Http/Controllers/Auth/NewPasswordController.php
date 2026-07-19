<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $token,
            'publicKey' => \App\Support\PasswordEncryptionHelper::getPublicKey(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('password')) {
            $decrypted = \App\Support\PasswordEncryptionHelper::decrypt((string) $request->input('password'));
            $request->merge(['password' => $decrypted]);
        }
        if ($request->has('password_confirmation')) {
            $decryptedConf = \App\Support\PasswordEncryptionHelper::decrypt((string) $request->input('password_confirmation'));
            $request->merge(['password_confirmation' => $decryptedConf]);
        }

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')->toString()),
                    'remember_token' => Str::random(60),
                    // Unlock the account on a successful password reset
                    'failed_login_attempts' => 0,
                    'is_locked' => false,
                    'locked_at' => null,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
