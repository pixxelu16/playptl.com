@extends('emails.layout')

@section('title', 'Booking Accepted')
@section('preheader', 'Great news! Your booking with ' . $booking->provider->name . ' has been accepted.')
@section('header', 'Booking Accepted! 🎉')

@section('content')
@php $currencySymbol = \App\Models\SiteSetting::currencySymbol(); @endphp
<p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->student->name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    Great news! Your booking with <strong>{{ $booking->provider->name }}</strong>
    ({{ ucfirst($booking->provider_type) }}) has been <strong style="color:#5DA44E;">accepted</strong>.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:8px;padding:20px;margin-bottom:24px;">
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;width:50%;">{{ ucfirst($booking->provider_type) }}</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->provider->name }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Dates</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">
            {{ $booking->from_date->format('M d, Y') }} → {{ $booking->to_date->format('M d, Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Total Days</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->total_days }} days</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Total Hours</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->total_hours }} hrs</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Amount Paid</td>
        <td style="padding:6px 0;font-size:15px;color:#5DA44E;font-weight:700;">
            @if($booking->total_amount > 0)
                {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}
            @else
                Free Session
            @endif
        </td>
    </tr>
</table>

<p style="margin:0 0 24px;font-size:14px;color:#374151;">
    Get ready for your session! You can view your booking details anytime from your dashboard.
</p>

<div style="text-align:center;margin-bottom:24px;">
    <a href="{{ url('/student/bookings/' . $booking->id) }}"
       style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
        View My Booking
    </a>
</div>
@endsection
