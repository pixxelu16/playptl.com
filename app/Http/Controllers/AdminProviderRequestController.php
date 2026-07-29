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
        $statusFilter = $request->query('status', 'pending');
        $roleFilter = $request->query('role');

        $query = User::query();

        // Apply role filter and status filter dynamically
        $query->where(function ($q) use ($statusFilter, $roleFilter) {
            // First option: primary role
            $q->where(function ($sub) use ($statusFilter, $roleFilter) {
                if ($roleFilter === 'mentor') {
                    $sub->where('role', UserRole::Mentor);
                } elseif ($roleFilter === 'coach') {
                    $sub->where('role', UserRole::Coach);
                } else {
                    $sub->whereIn('role', [UserRole::Mentor, UserRole::Coach]);
                }
                
                if ($statusFilter !== 'all') {
                    $sub->where('status', $statusFilter);
                }
            });

            // Second option: secondary Mentor status
            if (!$roleFilter || $roleFilter === 'mentor') {
                $q->orWhere(function ($sub) use ($statusFilter) {
                    if ($statusFilter !== 'all') {
                        $sub->where('mentor_status', $statusFilter);
                    } else {
                        $sub->whereNotNull('mentor_status');
                    }
                });
            }

            // Third option: secondary Coach status
            if (!$roleFilter || $roleFilter === 'coach') {
                $q->orWhere(function ($sub) use ($statusFilter) {
                    if ($statusFilter !== 'all') {
                        $sub->where('coach_status', $statusFilter);
                    } else {
                        $sub->whereNotNull('coach_status');
                    }
                });
            }
        });

        // Search filter
        if ($search = trim($request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $pendingCount = User::where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereIn('role', [UserRole::Mentor, UserRole::Coach])
                    ->where('status', 'pending');
            })
            ->orWhere('mentor_status', 'pending')
            ->orWhere('coach_status', 'pending');
        })->count();

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
        if ($user->mentor_status === 'pending') {
            $user->update(['mentor_status' => 'active']);
            \Spatie\Permission\Models\Role::findOrCreate('Mentor', 'web');
            $user->assignRole('Mentor');
            $roleLabel = 'Mentor';
        } elseif ($user->coach_status === 'pending') {
            $user->update(['coach_status' => 'active']);
            \Spatie\Permission\Models\Role::findOrCreate('Coach', 'web');
            $user->assignRole('Coach');
            $roleLabel = 'Coach';
        } else {
            if (!in_array($user->role, [UserRole::Mentor, UserRole::Coach], true)) {
                return back()->with('error', 'Selected user is not a Mentor or Coach.');
            }
            $user->update(['status' => 'active']);
            $roleName = ucfirst($user->role->value);
            \Spatie\Permission\Models\Role::findOrCreate($roleName, 'web');
            $user->assignRole($roleName);
            $roleLabel = $user->role->value;
        }

        try {
            Mail::to($user->email)->send(new ProviderApplicationDecisionMail($user, 'approved'));
        } catch (\Throwable $e) {
            // Ignore email fail
        }

        return back()->with('success', "{$user->name}'s {$roleLabel} application has been approved successfully. Email notification sent.");
    }

    /**
     * Reject a Mentor or Coach application.
     */
    public function reject(User $user): RedirectResponse
    {
        if ($user->mentor_status === 'pending') {
            $user->update(['mentor_status' => 'rejected']);
            $roleLabel = 'Mentor';
        } elseif ($user->coach_status === 'pending') {
            $user->update(['coach_status' => 'rejected']);
            $roleLabel = 'Coach';
        } else {
            if (!in_array($user->role, [UserRole::Mentor, UserRole::Coach], true)) {
                return back()->with('error', 'Selected user is not a Mentor or Coach.');
            }
            $user->update(['status' => 'rejected']);
            $roleLabel = $user->role->value;
        }

        try {
            Mail::to($user->email)->send(new ProviderApplicationDecisionMail($user, 'rejected'));
        } catch (\Throwable $e) {
            // Ignore email fail
        }

        return back()->with('error', "{$user->name}'s {$roleLabel} application has been rejected. Email notification sent.");
    }
}
