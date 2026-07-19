<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('coach.dashboard', [
            'user' => $request->user(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('coach.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($user->id)],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:64'],
            'profile_title_ad' => ['nullable', 'string'],
            'profile_lessons' => ['nullable', 'string'],
            'profile_bio' => ['nullable', 'string'],
            'profile_locations' => ['nullable', 'array'],
            'profile_rate' => ['nullable', 'numeric', 'min:0'],
            'profile_rate_details' => ['nullable', 'string'],
        ]);

        $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $validated['profile_locations'] = $request->input('profile_locations', []);

        $user->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if ($request->has('password')) {
            $request->merge(['password' => \App\Support\PasswordEncryptionHelper::decrypt((string) $request->input('password'))]);
        }
        if ($request->has('password_confirmation')) {
            $request->merge(['password_confirmation' => \App\Support\PasswordEncryptionHelper::decrypt((string) $request->input('password_confirmation'))]);
        }
        if ($request->has('current_password')) {
            $request->merge(['current_password' => \App\Support\PasswordEncryptionHelper::decrypt((string) $request->input('current_password'))]);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password changed successfully.');
    }
}
