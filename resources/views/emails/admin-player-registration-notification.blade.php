@extends('emails.layout')

@section('title', 'New Player Registration')
@section('header', 'New Tournament Registration')
@section('preheader', 'A new registration has been received for ' . $leagueName . '.')

@section('content')
    <p style="margin:0 0 14px;">Hi Admin,</p>
    <p style="margin:0 0 14px;">
        A new <strong>{{ ucfirst($registrationType) }}</strong> registration has been submitted for <strong>{{ $leagueName }}</strong>.
    </p>

    @if($registrationType === 'doubles')
        @include('emails.partials.info-box', ['content' => '
            <p style="margin:0 0 8px; font-size:14px; text-decoration:underline;"><strong>Registration Summary</strong></p>
            <p style="margin:0 0 6px;"><strong>Tournament:</strong> '.e($leagueName).'</p>
            <p style="margin:0 0 6px;"><strong>Registration Type:</strong> Doubles</p>
            <p style="margin:0 0 6px;"><strong>Amount Paid:</strong> '.e($currency).' '.e($amount).'</p>
            <p style="margin:0 0 12px;"><strong>Transaction ID:</strong> '.e($paymentIntentId).'</p>

            <p style="margin:0 0 6px; font-size:14px; text-decoration:underline;"><strong>Player 1 (Primary)</strong></p>
            <p style="margin:0 0 6px;"><strong>Name:</strong> '.e($playerName).'</p>
            <p style="margin:0 0 6px;"><strong>Email:</strong> '.e($playerEmail).'</p>
            <p style="margin:0 0 6px;"><strong>Phone:</strong> '.e($playerPhone).'</p>
            <p style="margin:0 0 12px;"><strong>Skill Level:</strong> '.e($skillLevel).'</p>

            <p style="margin:0 0 6px; font-size:14px; text-decoration:underline;"><strong>Player 2 (Partner)</strong></p>
            <p style="margin:0 0 6px;"><strong>Name:</strong> '.e($partnerName ?? 'N/A').'</p>
            <p style="margin:0 0 6px;"><strong>Email:</strong> '.e($partnerEmail ?? 'N/A').'</p>
            <p style="margin:0 0 6px;"><strong>Phone:</strong> '.e($partnerPhone ?? 'N/A').'</p>
            <p style="margin:0;"><strong>Skill Level:</strong> '.e($partnerSkill ?? 'N/A').'</p>
        '])
    @else
        @include('emails.partials.info-box', ['content' => '
            <p style="margin:0 0 8px; font-size:14px; text-decoration:underline;"><strong>Player Details</strong></p>
            <p style="margin:0 0 6px;"><strong>Name:</strong> '.e($playerName).'</p>
            <p style="margin:0 0 6px;"><strong>Email:</strong> '.e($playerEmail).'</p>
            <p style="margin:0 0 6px;"><strong>Phone:</strong> '.e($playerPhone).'</p>
            <p style="margin:0 0 6px;"><strong>Tournament:</strong> '.e($leagueName).'</p>
            <p style="margin:0 0 6px;"><strong>Registration Type:</strong> Singles</p>
            <p style="margin:0 0 6px;"><strong>Skill Level:</strong> '.e($skillLevel).'</p>
            <p style="margin:0 0 6px;"><strong>Amount Paid:</strong> '.e($currency).' '.e($amount).'</p>
            <p style="margin:0;"><strong>Transaction ID:</strong> '.e($paymentIntentId).'</p>
        '])
    @endif

    <p style="margin:0;">Thanks,<br><strong>{{ \App\Support\MailBranding::teamName() }}</strong></p>
@endsection

@section('footer')
    This notification was automatically sent from the PTL Admin System.
@endsection
