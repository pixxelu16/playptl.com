@extends('emails.layout')

@section('title', 'Group matches cancelled')
@section('header', 'Group matches cancelled')
@section('preheader', 'Scheduled group matches have been cancelled for your league.')

@section('content')
    <p style="margin:0 0 14px;">Hi {{ $recipientDisplayName }},</p>
    <p style="margin:0 0 14px;">
        The league administrator has cancelled
        @if ($divisionWideCancel ?? false)
            <strong>all scheduled group matches</strong>
            for <strong>{{ $divisionName !== '' ? $divisionName : $leagueName }}</strong>
            (all subgroups)
        @else
            <strong>{{ $cancelledMatchCount }} {{ $cancelledMatchCount === 1 ? 'scheduled group match' : 'scheduled group matches' }}</strong>
            for <strong>{{ $groupName }}</strong> in
            <strong>{{ $leagueName }}</strong>
            @if ($divisionName !== '')
                ({{ $divisionName }})
            @endif
        @endif
        .
    </p>
    <p style="margin:0 0 14px;">
        These matches are no longer on your schedule. You will receive a new notification when matches are rescheduled.
    </p>
    <p style="margin:0;">
        If you have questions, please contact your league administrator.<br>
        <strong>{{ config('app.name', 'Premier Tennis League') }}</strong>
    </p>
@endsection

@section('footer')
    If this does not look right, contact your league administrator.
@endsection
