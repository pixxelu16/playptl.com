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

            // Super Admin bypasses only the 'admin' role gate — NOT all role gates.
            // This prevents privilege escalation for users that only hold certain permissions.
            $isSuperAdminBypass = ($role === 'admin' || $normalised === 'Admin')
                && $user->hasRole('Super Admin');

            if (
                $user->hasRole($role)
                || $user->hasRole($normalised)
                || $user->hasRole(ucfirst($role))
                || $isSuperAdminBypass
                || strtolower($user->role->value) === strtolower($role)
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
            $isSuperAdminBypass = ($role === 'admin' || $normalised === 'Admin') && $user->hasRole('Super Admin');
            if (
                $user->hasRole($role)
                || $user->hasRole($normalised)
                || $user->hasRole(ucfirst($role))
                || $isSuperAdminBypass
                || strtolower($user->role->value) === strtolower($role)
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
