<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SiteSetting;
use App\Models\User;
use App\Mail\BookingRequestedMail;
use App\Mail\BookingCancelledMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class BookingController extends Controller
{
    /**
     * Show the booking form for a given Mentor or Coach.
     */
    public function create(User $user)
    {
        if (! $user->hasAnyRole(['Mentor', 'Coach'])) {
            abort(404);
        }

        $roleName        = $user->hasRole('Mentor') ? 'mentor' : 'coach';
        $commissionRate  = $roleName === 'mentor'
            ? SiteSetting::mentorCommissionPercent()
            : SiteSetting::coachCommissionPercent();

        $stripePublishableKey = SiteSetting::stripePublishableKey();
        $currencySymbol       = SiteSetting::currencySymbol();

        return view('booking.create', compact('user', 'roleName', 'commissionRate', 'stripePublishableKey', 'currencySymbol'));
    }

    /**
     * Create a Stripe PaymentIntent for a paid booking (rate > 0).
     * Returns client_secret for front-end confirmation.
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'provider_id'   => ['required', 'integer', 'exists:users,id'],
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date', 'gte:from_date'],
            'booking_time'  => ['required', 'date_format:H:i'],
            'hours_per_day' => ['required', 'numeric', 'min:0.5', 'max:24'],
        ]);

        $provider = User::findOrFail($validated['provider_id']);
        if (! $provider->hasAnyRole(['Mentor', 'Coach'])) {
            return response()->json(['message' => 'Invalid provider.'], 422);
        }

        $roleName       = $provider->hasRole('Mentor') ? 'mentor' : 'coach';

        // Check availability overlap
        $overlapExists = Booking::where('provider_id', $provider->id)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_ACCEPTED])
            ->where('booking_time', $validated['booking_time'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('from_date', [$validated['from_date'], $validated['to_date']])
                      ->orWhereBetween('to_date', [$validated['from_date'], $validated['to_date']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('from_date', '<=', $validated['from_date'])
                            ->where('to_date', '>=', $validated['to_date']);
                      });
            })
            ->exists();

        if ($overlapExists) {
            return response()->json(['message' => 'The selected Mentor/Coach is not available for these dates and time.'], 422);
        }

        $commissionRate = $roleName === 'mentor'
            ? SiteSetting::mentorCommissionPercent()
            : SiteSetting::coachCommissionPercent();

        $totals = Booking::calculateTotals(
            $validated['from_date'],
            $validated['to_date'],
            (float) $validated['hours_per_day'],
            (float) ($provider->profile_rate ?? 0),
            $commissionRate
        );

        if ($totals['total_amount'] <= 0) {
            return response()->json(['message' => 'This session is free. Use the free booking flow.'], 422);
        }

        $secret = SiteSetting::stripeSecretKey();
        if ($secret === '') {
            return response()->json(['message' => 'Stripe is not configured.'], 500);
        }

        $stripe = new StripeClient($secret);
        $amountCents = (int) round($totals['total_amount'] * 100);

        try {
            $intent = $stripe->paymentIntents->create([
                'amount'                    => $amountCents,
                'currency'                  => strtolower(SiteSetting::stripeCurrency()),
                'automatic_payment_methods' => ['enabled' => true],
                'description'               => "Booking with {$provider->name}",
                'metadata'                  => [
                    'provider_id'   => $provider->id,
                    'provider_name' => $provider->name,
                    'student_id'    => Auth::id(),
                ],
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json([
            'client_secret'      => $intent->client_secret,
            'payment_intent_id'  => $intent->id,
            'amount_cents'       => $amountCents,
            'currency'           => strtoupper(SiteSetting::stripeCurrency()),
            'totals'             => $totals,
        ]);
    }

    /**
     * Store a new booking (paid via Stripe OR free).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id'          => ['required', 'integer', 'exists:users,id'],
            'message'              => ['nullable', 'string', 'max:2000'],
            'student_location'     => ['nullable', 'string', 'max:255'],
            'student_phone'        => ['nullable', 'string', 'max:30'],
            'from_date'            => ['required', 'date', 'after_or_equal:today'],
            'to_date'              => ['required', 'date', 'gte:from_date'],
            'booking_time'         => ['required', 'date_format:H:i'],
            'hours_per_day'        => ['required', 'numeric', 'min:0.5', 'max:24'],
            'stripe_charge_id'     => ['nullable', 'string'],
        ]);

        $provider = User::findOrFail($validated['provider_id']);
        if (! $provider->hasAnyRole(['Mentor', 'Coach'])) {
            abort(422, 'Invalid provider.');
        }

        $roleName       = $provider->hasRole('Mentor') ? 'mentor' : 'coach';

        // Check availability overlap
        $overlapExists = Booking::where('provider_id', $provider->id)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_ACCEPTED])
            ->where('booking_time', $validated['booking_time'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('from_date', [$validated['from_date'], $validated['to_date']])
                      ->orWhereBetween('to_date', [$validated['from_date'], $validated['to_date']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('from_date', '<=', $validated['from_date'])
                            ->where('to_date', '>=', $validated['to_date']);
                      });
            })
            ->exists();

        if ($overlapExists) {
            return back()->with('error', 'The selected Mentor/Coach is not available for these dates and time.')->withInput();
        }

        $commissionRate = $roleName === 'mentor'
            ? SiteSetting::mentorCommissionPercent()
            : SiteSetting::coachCommissionPercent();

        $hourlyRate = (float) ($provider->profile_rate ?? 0);

        $totals = Booking::calculateTotals(
            $validated['from_date'],
            $validated['to_date'],
            (float) $validated['hours_per_day'],
            $hourlyRate,
            $commissionRate
        );

        $booking = Booking::create([
            'student_id'       => Auth::id(),
            'provider_id'      => $provider->id,
            'provider_type'    => $roleName,
            'message'          => $validated['message'] ?? null,
            'student_location' => $validated['student_location'] ?? null,
            'student_phone'    => $validated['student_phone'] ?? null,
            'from_date'        => $validated['from_date'],
            'to_date'          => $validated['to_date'],
            'booking_time'     => $validated['booking_time'],
            'hours_per_day'    => $validated['hours_per_day'],
            'total_days'       => $totals['total_days'],
            'total_hours'      => $totals['total_hours'],
            'hourly_rate'      => $hourlyRate,
            'total_amount'     => $totals['total_amount'],
            'commission_rate'  => $commissionRate,
            'commission_amount'=> $totals['commission_amount'],
            'provider_amount'  => $totals['provider_amount'],
            'status'           => Booking::STATUS_PENDING,
            'stripe_charge_id' => $validated['stripe_charge_id'] ?? null,
        ]);

        // Send email notifications
        try {
            Mail::to($provider->email)->send(new BookingRequestedMail($booking));
            // Notify admin
            $adminEmail = SiteSetting::getValue('contact_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new BookingRequestedMail($booking));
            }
        } catch (\Throwable) {
            // Don't block booking if mail fails
        }

        return redirect()->route('student.bookings')
            ->with('success', 'Your booking request has been submitted successfully!');
    }

    /**
     * Student: list own bookings.
     */
    public function myBookings()
    {
        $bookings = Booking::forStudent(Auth::id())
            ->with('provider')
            ->latest()
            ->paginate(10);

        $currencySymbol = SiteSetting::currencySymbol();

        return view('student.bookings.index', [
            'bookings'       => $bookings,
            'currencySymbol' => $currencySymbol,
            'user'           => Auth::user(),
        ]);
    }

    /**
     * Student: view single booking.
     */
    public function show(Booking $booking)
    {
        abort_unless($booking->student_id === Auth::id(), 403);

        $booking->load('provider');
        $currencySymbol = SiteSetting::currencySymbol();

        return view('student.bookings.show', [
            'booking'        => $booking,
            'currencySymbol' => $currencySymbol,
            'user'           => Auth::user(),
        ]);
    }

    /**
     * Student: cancel a pending booking.
     */
    public function cancel(Booking $booking)
    {
        abort_unless($booking->student_id === Auth::id(), 403);

        if (! $booking->canBeCancelledByStudent()) {
            return back()->with('error', 'This booking can no longer be cancelled.');
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        // Notify provider
        try {
            Mail::to($booking->provider->email)->send(new BookingCancelledMail($booking));
        } catch (\Throwable) {}

        return redirect()->route('student.bookings')
            ->with('success', 'Booking cancelled successfully.');
    }
}
