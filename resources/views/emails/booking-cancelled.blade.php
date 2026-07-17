@extends('emails.layout')

@section('title', 'Booking Cancelled')
@section('preheader', 'Booking request has been cancelled.')
@section('header', 'Booking Cancelled')

@section('content')
@php 
    $recipientRole = $recipientRole ?? 'provider';
@endphp

@if($recipientRole === 'student')
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->student->name }}</strong>,</p>
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">
        You have successfully cancelled your booking request with <strong>{{ $booking->provider->name }}</strong> ({{ ucfirst($booking->provider_type) }}).
    </p>
@elseif($recipientRole === 'admin')
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello Admin,</p>
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">
        The booking request by <strong>{{ $booking->student->name }}</strong> with <strong>{{ $booking->provider->name }}</strong> ({{ ucfirst($booking->provider_type) }}) has been <strong style="color:#d97706;">cancelled</strong>.
    </p>
@else
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">Hello <strong>{{ $booking->provider->name }}</strong>,</p>
    <p style="margin:0 0 16px;font-size:15px;color:#374151;">
        <strong>{{ $booking->student->name }}</strong> has <strong style="color:#d97706;">cancelled</strong> their booking request with you.
    </p>
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
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Dates</td>
        <td style="padding:6px 0;font-size:14px;color:#111827;font-weight:600;">
            {{ $booking->from_date->format('M d, Y') }} → {{ $booking->to_date->format('M d, Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px 0;font-size:14px;color:#6b7280;">Status</td>
        <td style="padding:6px 0;font-size:14px;color:#d97706;font-weight:600;">Cancelled</td>
    </tr>
</table>

<div style="text-align:center;margin-bottom:24px;">
    @if($recipientRole === 'student')
        <a href="{{ url('/student/bookings') }}"
           style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
            View My Bookings
        </a>
    @elseif($recipientRole === 'admin')
        <a href="{{ url('/admin/bookings/' . $booking->id) }}"
           style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
            View Booking Details
        </a>
    @else
        <a href="{{ url('/provider/bookings') }}"
           style="display:inline-block;background:#5DA44E;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:8px;">
            View My Bookings
        </a>
    @endif
</div>
@endsection
