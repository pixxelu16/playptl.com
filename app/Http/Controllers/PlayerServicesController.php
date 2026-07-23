<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PlayerServicesController extends Controller
{
    /**
     * Display a listing of Mentors.
     */
    public function mentors(Request $request)
    {
        $query = User::where(function ($q) {
            $q->where(function ($sq) {
                $sq->where('role', \App\Enums\UserRole::Mentor)
                   ->where('status', 'active');
            })->orWhere('mentor_status', 'active');
        });

        // Apply filters
        if ($search = trim($request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($city = trim($request->query('city', ''))) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($state = trim($request->query('state', ''))) {
            $query->where('state', 'like', "%{$state}%");
        }

        // Apply sorting
        $sort = $request->query('sort', 'newest');
        if ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderByDesc('id');
        }

        $mentors = $query->paginate(12)->withQueryString();

        return view('player-services.mentors', [
            'mentors' => $mentors,
            'search' => $search,
            'city' => $city,
            'state' => $state,
            'sort' => $sort,
        ]);
    }

    /**
     * Display a listing of Coaches.
     */
    public function coaches(Request $request)
    {
        $query = User::where(function ($q) {
            $q->where(function ($sq) {
                $sq->where('role', \App\Enums\UserRole::Coach)
                   ->where('status', 'active');
            })->orWhere('coach_status', 'active');
        });

        // Apply filters
        if ($search = trim($request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($city = trim($request->query('city', ''))) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($state = trim($request->query('state', ''))) {
            $query->where('state', 'like', "%{$state}%");
        }

        // Apply sorting
        $sort = $request->query('sort', 'newest');
        if ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderByDesc('id');
        }

        $coaches = $query->paginate(12)->withQueryString();

        return view('player-services.coaches', [
            'coaches' => $coaches,
            'search' => $search,
            'city' => $city,
            'state' => $state,
            'sort' => $sort,
        ]);
    }

    /**
     * Display a specific profile page for Coach or Mentor.
     */
    public function showProfile(User $user)
    {
        // Restrict to active Mentor or Coach roles
        if (!$user->hasAnyRole(['Mentor', 'Coach']) || $user->status !== 'active') {
            abort(404);
        }

        return view('player-services.show', [
            'user' => $user,
            'roleName' => $user->hasRole('Mentor') ? 'Mentor' : 'Coach'
        ]);
    }
}
