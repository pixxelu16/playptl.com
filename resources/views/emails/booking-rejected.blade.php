@extends('emails.layout')

@section('title', 'Booking Declined')
@section('preheader', 'Booking request has been declined.')
@section('header', 'Booking Declined')

@section('content')
@php 
    $currencySymbol = \App\Models\SiteSetting::currencySymbol(); 
    $recipientRole = $recipientRole ?? 'student';
@endphp

@if($recipientRole === 'student')
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->student->name }}</strong>,</p>
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">
        Unfortunately, your booking request with <strong>{{ $booking->provider->name }}</strong> ({{ ucfirst($booking->provider_type) }}) has been <strong style="color:#dc2626;">declined</strong>.
    </p>
@elseif($recipientRole === 'admin')
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello Admin,</p>
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">
        The booking request by <strong>{{ $booking->student->name }}</strong> with <strong>{{ $booking->provider->name }}</strong> ({{ ucfirst($booking->provider_type) }}) has been <strong style="color:#dc2626;">declined</strong>.
    </p>
@else
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->provider->name }}</strong>,</p>
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">
        You have successfully <strong style="color:#dc2626;">declined</strong> the booking request from <strong>{{ $booking->student->name }}</strong>.
    </p>
@endif

@if($booking->total_amount > 0 && $booking->stripe_refund_id)
<div style="background:#fef2f2;border-left:4px solid #dc2626;border-radius:4px;padding:16px;margin-bottom:24px;">
    <p style="margin:0;font-size:14px;color:#991b1b;">
        <strong>Refund Issued:</strong> A full refund of <strong>{{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}</strong>
        has been initiated back to the original payment method. It may take 5–10 business days to appear.
    </p>
</div>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:8px;padding:20px;margin-bottom:24px;">
    @if($recipientRole !== 'student')
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;width:50%;">Student</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->student->name }}</td>
    </tr>
    @endif
    @if($recipientRole !== 'provider')
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;width:50%;">{{ ucfirst($booking->provider_type) }}</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">{{ $booking->provider->name }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Dates Requested</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">
            {{ $booking->from_date->format('M d, Y') }} → {{ $booking->to_date->format('M d, Y') }}
        </td>
    </tr>
</table>

@if($recipientRole === 'student')
<p style="margin:0 0 24px;font-size:14px;color:#374151;">
    You can browse other available Mentors and Coaches to find your ideal match.
</p>
<div style="text-align:center;margin-bottom:24px;">
    <a href="{{ url('/player-services/mentors') }}"
       style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
        Browse Mentors & Coaches
    </a>
</div>
@else
<div style="text-align:center;margin-bottom:24px;">
    @if($recipientRole === 'admin')
        <a href="{{ url('/admin/bookings/' . $booking->id) }}"
           style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
            View Booking Details
        </a>
    @else
        <a href="{{ url('/provider/bookings/' . $booking->id) }}"
           style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
            View Booking Details
        </a>
    @endif
</div>
@endif
@endsection
