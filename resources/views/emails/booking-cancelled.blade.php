@extends('emails.layout')

@section('title', 'Booking Cancelled')
@section('preheader', $booking->student->name . ' has cancelled their booking request.')
@section('header', 'Booking Cancelled')

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->provider->name }}</strong>,</p>
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    <strong>{{ $booking->student->name }}</strong> has cancelled their booking request with you.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:8px;padding:20px;margin-bottom:24px;">
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;width:50%;">Student</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->student->name }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Dates</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">
            {{ $booking->from_date->format('M d, Y') }} → {{ $booking->to_date->format('M d, Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Status</td>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;font-weight:600;">Cancelled</td>
    </tr>
</table>

<p style="margin:0 0 24px;font-size:14px;color:#374151;">
    No action is required. You can view your full bookings list from your dashboard.
</p>

<div style="text-align:center;margin-bottom:24px;">
    <a href="{{ url('/provider/bookings') }}"
       style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
        View My Bookings
    </a>
</div>
@endsection
