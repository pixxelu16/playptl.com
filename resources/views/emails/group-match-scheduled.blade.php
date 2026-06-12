@extends('emails.layout')

@php
    $isPlayoff = ($playoffRoundLabel ?? null) !== null && ($playoffRoundLabel ?? '') !== '';
    $isUpdate = ($updatedByOpponent ?? false) || ($updatedByPlayer ?? false);

    if ($removedFromMatch ?? false) {
        $header = 'Playoff match assignment changed';
    } elseif ($rosterChanged ?? false) {
        $header = 'Your playoff match players were updated';
    } elseif ($isUpdate) {
        $header = 'Your match schedule was updated';
    } elseif ($isPlayoff) {
        $header = 'Your playoff match is scheduled';
    } else {
        $header = 'Your match is scheduled';
    }
@endphp

@section('title', $header)
@section('header', $header)
@section('preheader', $header.' — '.$leagueName)

@section('content')
    <p style="margin:0 0 14px;">Hi {{ $recipientDisplayName }},</p>
    <p style="margin:0 0 14px;">
        @if ($removedFromMatch ?? false)
            An administrator has updated the players for a <strong>{{ $playoffRoundLabel }}</strong> playoff match in
        @elseif ($rosterChanged ?? false)
            An administrator has updated the players for your <strong>{{ $playoffRoundLabel }}</strong> playoff match in
        @elseif ($updatedByPlayer ?? false)
            A player has updated the schedule for your
            @if ($isPlayoff)
                <strong>{{ $playoffRoundLabel }}</strong> playoff match in
            @else
                <strong>{{ $formatLabel }}</strong> match in
            @endif
        @elseif ($updatedByOpponent ?? false)
            Your opponent has updated the schedule for your <strong>{{ $formatLabel }}</strong> match in
        @elseif ($isPlayoff)
            An administrator has scheduled your <strong>{{ $playoffRoundLabel }}</strong> playoff match in
        @else
            An administrator has scheduled a <strong>{{ $formatLabel }}</strong> match for you in
        @endif
        <strong>{{ $leagueName }}</strong>
        @if ($divisionName !== '')
            ({{ $divisionName }})
        @endif
        @if (! $isPlayoff && ($groupName ?? '') !== '')
            — {{ $groupName }}
        @endif
        .
    </p>

    @include('emails.partials.info-box', ['content' => '
        <p style="margin:0 0 8px;"><strong>Date:</strong> '.e($matchDateDisplay).'</p>
        <p style="margin:0 0 8px;"><strong>Time:</strong> '.e($startTime).'</p>
        <p style="margin:0 0 8px;"><strong>Venue:</strong> '.e($venueDisplay).'</p>
        <p style="margin:0;"><strong>Match-up:</strong> '.e($opponentSummary).'</p>
    '])

    @include('emails.partials.button', [
        'url' => route('player.profile.location'),
        'label' => 'View my matches',
    ])

    <p style="margin:0;">Good luck — see you on court.<br><strong>{{ config('app.name', 'Premier Tennis League') }}</strong></p>
@endsection

@section('footer')
    If this does not look right, contact your league administrator.
@endsection
