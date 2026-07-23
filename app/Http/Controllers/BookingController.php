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

        $activeRole = session('active_dashboard_role');
        if (!$activeRole) {
            if (Auth::user()->hasRole('Student')) {
                $activeRole = 'student';
            } else {
                $activeRole = strtolower(Auth::user()->role->value);
            }
        }

        if ($activeRole !== 'student') {
            return redirect()->back()->with('error', 'Please switch to your Student account to book a mentor or coach.');
        }

        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot book a session with yourself.');
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

        $activeRole = session('active_dashboard_role');
        if (!$activeRole) {
            if (Auth::user()->hasRole('Student')) {
                $activeRole = 'student';
            } else {
                $activeRole = strtolower(Auth::user()->role->value);
            }
        }

        if ($activeRole !== 'student') {
            return response()->json(['message' => 'Please switch to your Student account to book a mentor or coach.'], 403);
        }

        if (Auth::id() === $provider->id) {
            return response()->json(['message' => 'You cannot book a session with yourself.'], 403);
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
            'message'              => ['required', 'string', 'max:2000'],
            'student_location'     => ['required', 'string', 'max:255'],
            'student_phone'        => ['required', 'string', 'max:30'],
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

        $activeRole = session('active_dashboard_role');
        if (!$activeRole) {
            if (Auth::user()->hasRole('Student')) {
                $activeRole = 'student';
            } else {
                $activeRole = strtolower(Auth::user()->role->value);
            }
        }

        if ($activeRole !== 'student') {
            return redirect()->back()->with('error', 'Please switch to your Student account to book a mentor or coach.')->withInput();
        }

        if (Auth::id() === $provider->id) {
            return redirect()->back()->with('error', 'You cannot book a session with yourself.')->withInput();
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
            'stripe_charge_id' => $validated['stripe_charge_id'] ?? null,
        ]);

        if ($booking->total_amount > 0 && $booking->stripe_charge_id) {
            try {
                \App\Models\PaymentHistory::create([
                    'user_id' => $booking->student_id,
                    'league_id' => null,
                    'amount' => $booking->total_amount,
                    'currency' => strtoupper(SiteSetting::stripeCurrency() ?: 'USD'),
                    'status' => 'completed',
                    'transaction_id' => (string) $booking->stripe_charge_id,
                    'description' => "Booking Session with " . $provider->name,
                    'meta' => [
                        'booking_id' => $booking->id,
                        'provider_id' => $provider->id,
                        'provider_name' => $provider->name,
                        'provider_type' => $roleName,
                        'total_hours' => $booking->total_hours,
                        'hourly_rate' => $booking->hourly_rate,
                        'provider_share_rate' => $booking->commission_rate . '%',
                        'platform_commission_rate' => (100 - $booking->commission_rate) . '%',
                        'commission_amount' => $booking->commission_amount,
                        'provider_amount' => $booking->provider_amount,
                    ],
                ]);
            } catch (\Throwable $e) {
                // Ignore or log error so that booking creation itself is not blocked
            }
        }

        // Send email notifications
        try {
            Mail::to($provider->email)->send(new BookingRequestedMail($booking, 'provider'));
            Mail::to(Auth::user()->email)->send(new BookingRequestedMail($booking, 'student'));
            $adminEmail = SiteSetting::getValue('contact_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new BookingRequestedMail($booking, 'admin'));
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

        $freshStatus = Booking::whereKey($booking->id)->value('status');
        if ($freshStatus !== Booking::STATUS_PENDING) {
            $statusLabel = match($freshStatus) {
                Booking::STATUS_ACCEPTED  => 'accepted',
                Booking::STATUS_REJECTED  => 'rejected',
                Booking::STATUS_CANCELLED => 'cancelled',
                Booking::STATUS_COMPLETED => 'completed',
                default                   => $freshStatus,
            };
            return back()->with('error', "This booking request has already been {$statusLabel}. Please refresh the page.");
        }

        $refundId = null;

        // Issue Stripe refund if charge exists
        if ($booking->stripe_charge_id) {
            $secret = SiteSetting::stripeSecretKey();
            if ($secret !== '') {
                $stripe = new \Stripe\StripeClient($secret);
                try {
                    $params = [];
                    if (str_starts_with($booking->stripe_charge_id, 'pi_')) {
                        $params['payment_intent'] = $booking->stripe_charge_id;
                    } else {
                        $params['charge'] = $booking->stripe_charge_id;
                    }
                    $refund   = $stripe->refunds->create($params);
                    $refundId = $refund->id;
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return back()->with('error', 'Refund failed: ' . $e->getMessage());
                }
            }
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'stripe_refund_id' => $refundId,
        ]);

        if ($booking->stripe_charge_id) {
            try {
                $paymentHistory = \App\Models\PaymentHistory::where('transaction_id', $booking->stripe_charge_id)->first();
                if ($paymentHistory) {
                    $paymentHistory->update(['status' => 'refunded']);
                }
            } catch (\Throwable $e) {}
        }

        // Notify provider, student and admin
        try {
            Mail::to($booking->provider->email)->send(new BookingCancelledMail($booking, 'provider'));
            Mail::to($booking->student->email)->send(new BookingCancelledMail($booking, 'student'));
            $adminEmail = SiteSetting::getValue('contact_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new BookingCancelledMail($booking, 'admin'));
            }
        } catch (\Throwable) {}

        return redirect()->route('student.bookings')
            ->with('success', 'Booking cancelled successfully.');
    }
}
