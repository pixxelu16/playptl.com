@extends('emails.layout')

@section('title', $decision === 'approved' ? 'Application Approved!' : 'Application Status Update')
@section('header', $decision === 'approved' ? 'Welcome Aboard!' : 'Application Status Update')
@section('preheader', $decision === 'approved' ? 'Your provider application has been approved.' : 'Update on your account application.')

@section('content')
    @php
        $roleTitle = ucfirst($user->role->value ?? (string) $user->role);
    @endphp

    <p style="margin:0 0 14px;">Hi {{ $user->name }},</p>

    @if ($decision === 'approved')
        <p style="margin:0 0 14px;">
            Great news! Your application to join {{ config('app.name', 'Premier Tennis League') }} as a <strong>{{ $roleTitle }}</strong> has been reviewed and <strong>approved</strong> by our admin team.
        </p>

        @include('emails.partials.info-box', ['content' => '
            <p style="margin:0 0 8px;"><strong>Status:</strong> Approved & Active</p>
            <p style="margin:0 0 8px;"><strong>Account Email:</strong> '.e($user->email).'</p>
            <p style="margin:0;"><strong>Role:</strong> '.e($roleTitle).'</p>
        '])

        <p style="margin:0 0 24px;">
            You can now log in to your account to complete your profile, set your rates, and start offering services to players!
        </p>

        @include('emails.partials.button', [
            'url' => route('login'),
            'label' => 'Log in to My Account',
        ])
    @else
        <p style="margin:0 0 14px;">
            Thank you for applying to join {{ config('app.name', 'Premier Tennis League') }} as a <strong>{{ $roleTitle }}</strong>.
        </p>

        <p style="margin:0 0 14px;">
            After careful review of your application details, we regret to inform you that your application has been <strong>rejected</strong> at this time.
        </p>

        @include('emails.partials.info-box', ['content' => '
            <p style="margin:0 0 8px;"><strong>Status:</strong> Application Declined</p>
            <p style="margin:0;"><strong>Account Email:</strong> '.e($user->email).'</p>
        '])

        <p style="margin:0 0 24px;">
            If you believe this decision was made in error or if you have updated qualifications, please contact our support team at <a href="mailto:{{ \App\Models\SiteSetting::getValue('contact_email') }}">{{ \App\Models\SiteSetting::getValue('contact_email') }}</a>.
        </p>
    @endif

    <p style="margin:14px 0 0;">Thanks,<br><strong>{{ \App\Support\MailBranding::teamName() }}</strong></p>
@endsection
