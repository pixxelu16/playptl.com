<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'You must be logged in.');
        }

        // Global bypass for Super Admin & Admin
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            return $next($request);
        }

        // Check if user has ANY of the specified permissions
        $hasPermission = false;
        foreach ($permissions as $permissionGroup) {
            $perms = preg_split('/[,|]/', $permissionGroup);
            foreach ($perms as $perm) {
                $perm = trim($perm);
                if ($perm !== '' && $user->can($perm)) {
                    $hasPermission = true;
                    break 2;
                }
            }
        }

        if (! $hasPermission) {
            abort(403, 'You do not have permission to access this page or perform this action.');
        }

        return $next($request);
    }
}
