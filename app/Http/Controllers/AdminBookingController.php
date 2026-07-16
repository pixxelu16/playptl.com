<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    /**
     * List all bookings with filters.
     */
    public function index(Request $request)
    {
        $status       = $request->query('status', 'all');
        $providerType = $request->query('provider_type', 'all');
        $search       = $request->query('search', '');

        $query = Booking::with(['student', 'provider'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($providerType !== 'all') {
            $query->where('provider_type', $providerType);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('provider', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => Booking::count(),
            'pending'   => Booking::where('status', 'pending')->count(),
            'accepted'  => Booking::where('status', 'accepted')->count(),
            'rejected'  => Booking::where('status', 'rejected')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        $currencySymbol = \App\Models\SiteSetting::currencySymbol();

        return view('admin.bookings.index', compact('bookings', 'status', 'providerType', 'search', 'stats', 'currencySymbol'));
    }

    /**
     * Show a single booking in full detail.
     */
    public function show(Booking $booking)
    {
        $booking->load(['student', 'provider']);
        $currencySymbol = \App\Models\SiteSetting::currencySymbol();

        return view('admin.bookings.show', compact('booking', 'currencySymbol'));
    }

    /**
     * Mark payout as paid to the provider.
     */
    public function markPaid(Booking $booking)
    {
        $booking->update([
            'payout_status'  => Booking::PAYOUT_PAID,
            'payout_paid_at' => now(),
        ]);

        return back()->with('success', 'Payout marked as paid.');
    }

    /**
     * Override booking status (admin control).
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,accepted,rejected,cancelled'],
        ]);

        $booking->update(['status' => $validated['status']]);

        return back()->with('success', 'Booking status updated to ' . ucfirst($validated['status']) . '.');
    }
}
