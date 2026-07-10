@extends('emails.layout')

@section('title', 'Reset your password')
@section('header', 'Reset your password')
@section('preheader', 'Use the link below to reset your password.')

@section('content')
    <p style="margin:0 0 14px;">Hello,</p>
    <p style="margin:0 0 14px;">
        We received a request to reset the password for your {{ config('app.name', 'Premier Tennis League') }} account.
    </p>
    <p style="margin:0 0 14px;">
        Click the button below to choose a new password. This link will expire in {{ $expireMinutes }} minutes.
    </p>

    @include('emails.partials.button', [
        'url' => $resetUrl,
        'label' => 'Reset password',
    ])

    <p style="margin:0 0 10px;font-size:12px;color:#666666;">
        If you did not request a password reset, no action is required.
    </p>
    <p style="margin:0;font-size:12px;color:#888888;word-break:break-all;">
        Button not working? Copy this link:<br>
        <a href="{{ $resetUrl }}" style="color:#66A157;">{{ $resetUrl }}</a>
    </p>
@endsection

@section('footer')
    For security, never share your password or this reset link with anyone.
@endsection
