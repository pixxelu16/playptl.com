@extends('emails.layout')

@section('title', 'Application Received')
@section('header', 'Application Received')
@section('preheader', 'Your application is currently under review by our admin team.')

@section('content')
    <p style="margin:0 0 14px;">Hi {{ $user->name }},</p>
    @php
        $roleStr = ucfirst($user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role);
    @endphp
    <p style="margin:0 0 14px;">
        Thank you for registering as a <strong>{{ $roleStr }}</strong> with Premier Tennis League.
    </p>

    @include('emails.partials.info-box', ['content' => '
        <p style="margin:0 0 8px;"><strong>Account Status:</strong> <span style="color:#d97706; font-weight:bold;">Under Review (Pending Approval)</span></p>
        <p style="margin:0 0 8px;"><strong>Role:</strong> '.e($roleStr).'</p>
        <p style="margin:0 0 8px;"><strong>Email:</strong> '.e($user->email).'</p>
        <p style="margin:0;"><strong>City / State:</strong> '.e($user->city).', '.e($user->state).'</p>
    '])

    <p style="margin:14px 0;">
        Your account is currently pending administrator review and approval. <strong>You will be able to log in to your account once an administrator approves your registration.</strong>
    </p>

    <p style="margin:0 0 14px;">
        You will receive an email notification as soon as your account status is updated.
    </p>

    <p style="margin:0;">Thanks,<br><strong>{{ \App\Support\MailBranding::teamName() }}</strong></p>
@endsection

@section('footer')
    If you have any questions regarding your application, please reply to this email or contact support.
@endsection
