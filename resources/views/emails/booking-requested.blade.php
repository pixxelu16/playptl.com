@extends('emails.layout')

@section('title', 'New Booking Request')
@section('preheader', 'You have received a new booking request from ' . $booking->student->name)
@section('header', 'New Booking Request')

@section('content')
@php $currencySymbol = \App\Models\SiteSetting::currencySymbol(); @endphp
<p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->provider->name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    You have received a new <strong>{{ ucfirst($booking->provider_type) }}</strong> booking request from
    <strong>{{ $booking->student->name }}</strong>.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:8px;padding:20px;margin-bottom:24px;">
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;width:50%;">Dates</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">
            {{ $booking->from_date->format('M d, Y') }} → {{ $booking->to_date->format('M d, Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Total Days</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->total_days }} days</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Hours / Day</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->hours_per_day }} hrs</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Total Hours</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->total_hours }} hrs</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Total Amount</td>
        <td style="padding:6px 0;font-size:15px;color:#5DA44E;font-weight:700;">
            {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}
        </td>
    </tr>
    @if($booking->message)
    <tr>
        <td colspan="2" style="padding:12px 0 0;">
            <p style="margin:0 0 4px;font-size:13px;color:#6b7280;font-weight:600;">Message from Student:</p>
            <p style="margin:0;font-size:14px;color:#374151;font-style:italic;">"{{ $booking->message }}"</p>
        </td>
    </tr>
    @endif
</table>

<p style="margin:0 0 24px;font-size:14px;color:#374151;">
    Please log in to review and respond to this booking request.
</p>

<div style="text-align:center;margin-bottom:24px;">
    <a href="{{ url('/provider/bookings/' . $booking->id) }}"
       style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
        Review Booking Request
    </a>
</div>
@endsection
