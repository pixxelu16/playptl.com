<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MentorController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        
        $revenue = \App\Models\Booking::where('provider_id', $user->id)
            ->whereIn('status', [\App\Models\Booking::STATUS_ACCEPTED, \App\Models\Booking::STATUS_COMPLETED])
            ->sum('provider_amount');
            
        $studentsCount = \App\Models\Booking::where('provider_id', $user->id)
            ->distinct('student_id')
            ->count('student_id');

        $totalBookings = \App\Models\Booking::where('provider_id', $user->id)->count();
        
        $pendingBookings = \App\Models\Booking::where('provider_id', $user->id)
            ->where('status', \App\Models\Booking::STATUS_PENDING)
            ->count();

        $acceptedBookings = \App\Models\Booking::where('provider_id', $user->id)
            ->where('status', \App\Models\Booking::STATUS_ACCEPTED)
            ->count();

        $recentBookings = \App\Models\Booking::where('provider_id', $user->id)
            ->with('student')
            ->latest()
            ->take(5)
            ->get();

        $currencySymbol = \App\Models\SiteSetting::currencySymbol();

        return view('mentor.dashboard', [
            'user' => $user,
            'revenue' => $revenue,
            'studentsCount' => $studentsCount,
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'acceptedBookings' => $acceptedBookings,
            'recentBookings' => $recentBookings,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function profile(Request $request): View
    {
        return view('mentor.profile', [
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
        

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()->symbols()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password changed successfully.');
    }
}
