<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderTransactionController extends Controller
{
    /**
     * Display a listing of bookings and earnings for the provider.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Mentor', 'Coach'])) {
            abort(403);
        }

        $roleName = $user->hasRole('Mentor') ? 'mentor' : 'coach';

        // Get bookings with pagination
        $bookings = Booking::where('provider_id', $user->id)
            ->with('student')
            ->latest()
            ->paginate(10);

        // Calculate summary metrics
        $totalRevenue = Booking::where('provider_id', $user->id)
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_COMPLETED])
            ->sum('provider_amount');

        $totalCommissionPaid = Booking::where('provider_id', $user->id)
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_COMPLETED])
            ->sum('commission_amount');

        $currencySymbol = SiteSetting::currencySymbol();

        return view('provider.transactions.index', [
            'user' => $user,
            'roleName' => $user->hasRole('Mentor') ? 'Mentor' : 'Coach',
            'bookings' => $bookings,
            'totalRevenue' => $totalRevenue,
            'totalCommissionPaid' => $totalCommissionPaid,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    /**
     * Show details of a specific transaction/booking.
     */
    public function show(Booking $booking)
    {
        $user = Auth::user();
        abort_unless($booking->provider_id === $user->id, 403);

        $booking->load('student');
        $currencySymbol = SiteSetting::currencySymbol();

        return view('provider.transactions.show', [
            'user' => $user,
            'roleName' => $user->hasRole('Mentor') ? 'Mentor' : 'Coach',
            'booking' => $booking,
            'currencySymbol' => $currencySymbol,
        ]);
    }
}
