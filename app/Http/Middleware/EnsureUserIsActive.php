<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $status = strtolower((string) ($user->status ?? 'active'));

            if ($status !== 'active') {
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = match ($status) {
                    'suspend' => 'Your account has been suspended. Please contact the administrator for assistance.',
                    'pending' => 'Your account registration is pending administrator approval.',
                    'rejected' => 'Your account registration request has been rejected. Access is restricted.',
                    default => 'Your account status is invalid or inactive. Please contact support.',
                };

                return redirect()->route('login')->withErrors([
                    'email' => $message,
                ]);
            }
        }

        return $next($request);
    }
}
