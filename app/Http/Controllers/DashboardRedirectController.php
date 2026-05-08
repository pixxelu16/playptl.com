<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->role === UserRole::Player) {
            return redirect()->to($request->user()->playerProfileUrl());
        }

        return redirect()->route($request->user()->dashboardRouteName());
    }
}
