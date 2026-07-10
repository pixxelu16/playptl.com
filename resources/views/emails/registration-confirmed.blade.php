@extends('emails.layout')

@section('title', 'Registration confirmed')
@section('header', 'Registration confirmed')
@section('preheader', 'Your tournament registration has been confirmed.')

@section('content')
    <p style="margin:0 0 14px;">Hi {{ $userName }},</p>
    <p style="margin:0 0 14px;">
        Your registration for <strong>{{ $leagueName }}</strong> has been confirmed. We are glad to have you in the league.
    </p>

    @include('emails.partials.info-box', ['content' => '
        <p style="margin:0 0 8px;"><strong>Type:</strong> '.e(ucfirst($registrationType)).'</p>
        <p style="margin:0 0 8px;"><strong>Skill level:</strong> '.e($skillLevel).'</p>
        <p style="margin:0 0 8px;"><strong>Paid:</strong> '.e($currency).' '.e($amount).'</p>
        <p style="margin:0;"><strong>Transaction:</strong> '.e($paymentIntentId).'</p>
    '])

    <p style="margin:0;">Thanks,<br><strong>{{ config('app.name', 'Premier Tennis League') }}</strong></p>
@endsection

@section('footer')
    If you did not make this registration, please contact your league administrator.
@endsection
