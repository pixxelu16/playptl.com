<?php

namespace App\Http\Controllers;

use App\Mail\BookingAcceptedMail;
use App\Mail\BookingRejectedMail;
use App\Models\Booking;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class ProviderBookingController extends Controller
{
    /**
     * List all bookings for the currently logged-in Mentor/Coach.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Booking::forProvider(Auth::id())
            ->with('student')
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(10)->withQueryString();
        $currencySymbol = \App\Models\SiteSetting::currencySymbol();

        return view('provider.bookings.index', [
            'bookings'       => $bookings,
            'status'         => $status,
            'currencySymbol' => $currencySymbol,
            'user'           => \Illuminate\Support\Facades\Auth::user(),
        ]);
    }

    /**
     * Show a single booking request.
     */
    public function show(Booking $booking)
    {
        abort_unless($booking->provider_id === Auth::id(), 403);

        $booking->load('student');
        $currencySymbol = \App\Models\SiteSetting::currencySymbol();

        return view('provider.bookings.show', [
            'booking'        => $booking,
            'currencySymbol' => $currencySymbol,
            'user'           => \Illuminate\Support\Facades\Auth::user(),
        ]);
    }

    /**
     * Accept a booking request.
     */
    public function accept(Booking $booking)
    {
        abort_unless($booking->provider_id === Auth::id(), 403);

        if (! $booking->isPending()) {
            return back()->with('error', 'This booking is no longer pending.');
        }

        $booking->update(['status' => Booking::STATUS_ACCEPTED]);

        try {
            Mail::to($booking->student->email)->send(new BookingAcceptedMail($booking));
        } catch (\Throwable) {}

        return back()->with('success', 'Booking accepted! The student has been notified.');
    }

    /**
     * Reject a booking request. Issues a Stripe refund if payment was made.
     */
    public function reject(Booking $booking)
    {
        abort_unless($booking->provider_id === Auth::id(), 403);

        if (! $booking->isPending()) {
            return back()->with('error', 'This booking is no longer pending.');
        }

        $refundId = null;

        // Issue Stripe refund if charge exists
        if ($booking->stripe_charge_id) {
            $secret = SiteSetting::stripeSecretKey();
            if ($secret !== '') {
                $stripe = new StripeClient($secret);
                try {
                    $refund   = $stripe->refunds->create(['charge' => $booking->stripe_charge_id]);
                    $refundId = $refund->id;
                } catch (ApiErrorException $e) {
                    return back()->with('error', 'Refund failed: ' . $e->getMessage());
                }
            }
        }

        $booking->update([
            'status'          => Booking::STATUS_REJECTED,
            'stripe_refund_id'=> $refundId,
        ]);

        try {
            Mail::to($booking->student->email)->send(new BookingRejectedMail($booking));
        } catch (\Throwable) {}

        return back()->with('success', 'Booking rejected and student has been notified.' . ($refundId ? ' A full refund has been issued.' : ''));
    }
}
