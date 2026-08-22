<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $hasRole = false;
        foreach ($roles as $role) {
            // Normalise the expected role name for matching (e.g. "admin" → "Admin")
            $normalised = ucwords($role);

            $userRoleVal = strtolower($user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role);

            // Admin / Super Admin, Organiser, custom roles, or users with management permissions can access admin area
            $isSuperAdminBypass = ($role === 'admin' || $normalised === 'Admin')
                && ($user->hasRole('Super Admin')
                    || $user->hasRole('Admin')
                    || $user->hasRole('Organiser')
                    || $userRoleVal === 'admin'
                    || $userRoleVal === 'organiser'
                    || $user->can('view admin panel')
                    || $user->getAllPermissions()->isNotEmpty()
                    || $user->roles->whereNotIn('name', ['Student', 'student', 'Player', 'player'])->isNotEmpty());

            if (
                $user->hasRole($role)
                || $user->hasRole($normalised)
                || $user->hasRole(ucfirst($role))
                || $isSuperAdminBypass
                || $userRoleVal === strtolower($role)
            ) {
                $hasRole = true;
                break;
            }
        }

        if (! $hasRole) {
            abort(403, 'You do not have authorization to access this page.');
        }

        // Set active dashboard role in the session
        foreach ($roles as $role) {
            $normalised = ucwords($role);
            $userRoleVal = strtolower($user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role);
            $isSuperAdminBypass = ($role === 'admin' || $normalised === 'Admin') && $user->hasRole('Super Admin');
            if (
                $user->hasRole($role)
                || $user->hasRole($normalised)
                || $user->hasRole(ucfirst($role))
                || $isSuperAdminBypass
                || $userRoleVal === strtolower($role)
            ) {
                $activeRole = strtolower($role);
                if (in_array($activeRole, ['player', 'student', 'mentor', 'coach'])) {
                    session(['active_dashboard_role' => $activeRole]);
                }
                break;
            }
        }

        return $next($request);
    }
}
