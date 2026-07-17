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
            if ($user->hasRole($role) || $user->hasRole(ucwords($role)) || $user->hasRole(ucfirst($role)) || $user->hasRole('Super Admin') || $user->can('view admin panel')) {
                $hasRole = true;
                break;
            }
        }

        if (! $hasRole) {
            return redirect()->route($user->dashboardRouteName());
        }

        return $next($request);
    }
}
