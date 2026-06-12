@extends('emails.layout')

@section('title', 'Doubles partner invitation')
@section('header', 'You were added as a doubles partner')
@section('preheader', $inviterName.' added you to a doubles registration for '.$leagueName.'.')

@section('content')
    <p style="margin:0 0 14px;">Hello,</p>
    <p style="margin:0 0 14px;">
        <strong>{{ $inviterName }}</strong> added you as a doubles partner for <strong>{{ $leagueName }}</strong>.
    </p>
    <p style="margin:0 0 14px;">
        Set up your account with the same email address and create your password to join the league.
    </p>

    @include('emails.partials.button', [
        'url' => $setupUrl,
        'label' => 'Set up my account',
    ])

    <p style="margin:0;font-size:12px;color:#666666;">
        If you did not expect this invitation, you can safely ignore this email.
    </p>
@endsection

@section('footer')
    Need help? Contact your league administrator.
@endsection
