<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use App\Mail\ProviderApplicationDecisionMail;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class AdminProviderRequestController extends Controller
{
    /**
     * Display a listing of Coach and Mentor registration requests.
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', [UserRole::Mentor, UserRole::Coach]);

        // Filter by role if provided
        if ($roleFilter = $request->query('role')) {
            if ($roleFilter === 'mentor') {
                $query->where('role', UserRole::Mentor);
            } elseif ($roleFilter === 'coach') {
                $query->where('role', UserRole::Coach);
            }
        }

        // Filter by status if provided (default to pending)
        $statusFilter = $request->query('status', 'pending');
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Search filter
        if ($search = trim($request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $pendingCount = User::whereIn('role', [UserRole::Mentor, UserRole::Coach])->where('status', 'pending')->count();

        return view('admin.provider-requests.index', [
            'requests' => $requests,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve a Mentor or Coach application.
     */
    public function approve(User $user): RedirectResponse
    {
        if (!in_array($user->role, [UserRole::Mentor, UserRole::Coach], true)) {
            return back()->with('error', 'Selected user is not a Mentor or Coach.');
        }

        $user->update(['status' => 'active']);

        try {
            Mail::to($user->email)->send(new ProviderApplicationDecisionMail($user, 'approved'));
        } catch (\Throwable $e) {
            // Ignore email fail
        }

        return back()->with('success', "{$user->name}'s {$user->role->value} application has been approved successfully. Email notification sent.");
    }

    /**
     * Reject a Mentor or Coach application.
     */
    public function reject(User $user): RedirectResponse
    {
        if (!in_array($user->role, [UserRole::Mentor, UserRole::Coach], true)) {
            return back()->with('error', 'Selected user is not a Mentor or Coach.');
        }

        $user->update(['status' => 'rejected']);

        try {
            Mail::to($user->email)->send(new ProviderApplicationDecisionMail($user, 'rejected'));
        } catch (\Throwable $e) {
            // Ignore email fail
        }

        return back()->with('error', "{$user->name}'s {$user->role->value} application has been rejected. Email notification sent.");
    }
}
