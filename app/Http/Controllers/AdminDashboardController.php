<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\League;
use App\Models\Announcement;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\PaymentHistory;
use App\Models\Booking;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // 1. Role counts
        $coachesCount = User::where('role', UserRole::Coach)->count();
        $mentorsCount = User::where('role', UserRole::Mentor)->count();
        $studentsCount = User::where('role', UserRole::Student)->count();
        $playersCount = User::where('role', UserRole::Player)->count();
        $adminsCount = User::where('role', UserRole::Admin)->count();
        $organisersCount = User::where('role', UserRole::Organiser)->count();
        $totalUsers = User::count();

        // 2. Revenue statistics
        $totalRevenue = PaymentHistory::where('status', 'completed')->sum('amount');
        $tournamentRevenue = PaymentHistory::where('status', 'completed')->whereNotNull('league_id')->sum('amount');
        $bookingRevenue = PaymentHistory::where('status', 'completed')->whereNull('league_id')->sum('amount');
        $totalCommission = Booking::whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_COMPLETED])->sum('commission_amount');

        // 3. Recent registrations
        $recentStudents = User::where('role', UserRole::Student)->latest()->take(5)->get();
        $recentMentors = User::where('role', UserRole::Mentor)->latest()->take(5)->get();
        $recentCoaches = User::where('role', UserRole::Coach)->latest()->take(5)->get();

        // 4. Graph data generation (Database-agnostic grouping)
        $payments = PaymentHistory::where('status', 'completed')->get();

        // Daily (Last 30 Days)
        $dailyData = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyData->put($date, 0);
        }
        foreach ($payments as $p) {
            $dateStr = $p->created_at?->format('Y-m-d');
            if ($dailyData->has($dateStr)) {
                $dailyData->put($dateStr, $dailyData->get($dateStr) + (float)$p->amount);
            }
        }

        // Weekly (Last 10 Weeks)
        $weeklyData = collect();
        for ($i = 9; $i >= 0; $i--) {
            $date = now()->subWeeks($i)->startOfWeek()->format('\W\e\e\k W');
            $weeklyData->put($date, 0);
        }
        foreach ($payments as $p) {
            $dateStr = $p->created_at?->startOfWeek()->format('\W\e\e\k W');
            if ($weeklyData->has($dateStr)) {
                $weeklyData->put($dateStr, $weeklyData->get($dateStr) + (float)$p->amount);
            }
        }

        // Monthly (Last 12 Months)
        $monthlyData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('M Y');
            $monthlyData->put($month, 0);
        }
        foreach ($payments as $p) {
            $monthStr = $p->created_at?->format('M Y');
            if ($monthlyData->has($monthStr)) {
                $monthlyData->put($monthStr, $monthlyData->get($monthStr) + (float)$p->amount);
            }
        }

        // Yearly (Last 5 Years)
        $yearlyData = collect();
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->format('Y');
            $yearlyData->put($year, 0);
        }
        foreach ($payments as $p) {
            $yearStr = $p->created_at?->format('Y');
            if ($yearlyData->has($yearStr)) {
                $yearlyData->put($yearStr, $yearlyData->get($yearStr) + (float)$p->amount);
            }
        }

        return view('admin.dashboard', [
            'leaguesCount' => League::query()->count(),
            'announcementsCount' => Announcement::query()->count(),
            'groupsCount' => Group::query()->count(),
            'groupCardsCount' => GroupCard::query()->count(),
            'paymentsCount' => PaymentHistory::query()->count(),
            
            'coachesCount' => $coachesCount,
            'mentorsCount' => $mentorsCount,
            'studentsCount' => $studentsCount,
            'playersCount' => $playersCount,
            'adminsCount' => $adminsCount,
            'organisersCount' => $organisersCount,
            'totalUsers' => $totalUsers,

            'totalRevenue' => $totalRevenue,
            'tournamentRevenue' => $tournamentRevenue,
            'bookingRevenue' => $bookingRevenue,
            'totalCommission' => $totalCommission,

            'recentStudents' => $recentStudents,
            'recentMentors' => $recentMentors,
            'recentCoaches' => $recentCoaches,

            'dailyLabels' => $dailyData->keys()->toArray(),
            'dailyValues' => $dailyData->values()->toArray(),
            'weeklyLabels' => $weeklyData->keys()->toArray(),
            'weeklyValues' => $weeklyData->values()->toArray(),
            'monthlyLabels' => $monthlyData->keys()->toArray(),
            'monthlyValues' => $monthlyData->values()->toArray(),
            'yearlyLabels' => $yearlyData->keys()->toArray(),
            'yearlyValues' => $yearlyData->values()->toArray(),
        ]);
    }
}
