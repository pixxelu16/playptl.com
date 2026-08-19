<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (
            $user->hasRole('Super Admin')
            || $user->hasRole('Admin')
            || $user->hasRole('Organiser')
            || $user->role === UserRole::Admin
            || $user->role === UserRole::Organiser
            || $user->can('view admin panel')
            || $user->getAllPermissions()->isNotEmpty()
            || $user->roles->whereNotIn('name', ['Student', 'student', 'Player', 'player'])->isNotEmpty()
        ) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === UserRole::Player) {
            return redirect()->to($user->playerProfileUrl());
        }

        return redirect()->route($user->dashboardRouteName());
    }
}
