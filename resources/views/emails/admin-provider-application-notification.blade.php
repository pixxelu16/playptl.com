@extends('emails.layout')

@section('title', 'New Provider Application')
@section('header', 'New Application Submitted')
@section('preheader', 'A new Mentor/Coach registration requires your approval.')

@section('content')
    <p style="margin:0 0 14px;">Hi Admin,</p>
    @php
        $roleStr = ucfirst($user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role);
    @endphp
    <p style="margin:0 0 14px;">
        A new <strong>{{ $roleStr }}</strong> registration has been submitted and is awaiting your review and approval.
    </p>

    @include('emails.partials.info-box', ['content' => '
        <p style="margin:0 0 8px;"><strong>Applicant Name:</strong> '.e($user->name).'</p>
        <p style="margin:0 0 8px;"><strong>Requested Role:</strong> '.e($roleStr).'</p>
        <p style="margin:0 0 8px;"><strong>Email:</strong> '.e($user->email).'</p>
        <p style="margin:0 0 8px;"><strong>Phone:</strong> '.e($user->phone ?? 'N/A').'</p>
        <p style="margin:0 0 8px;"><strong>Location:</strong> '.e($user->city).', '.e($user->state).'</p>
        <p style="margin:0;"><strong>Current Status:</strong> <span style="color:#d97706; font-weight:bold;">Pending Approval</span></p>
    '])

    <p style="margin:16px 0;">
        Please log in to the admin panel to review and approve or reject this applicant's registration request.
    </p>

    <div style="margin:20px 0; text-align:center;">
        <a href="{{ route('admin.provider-requests.index') }}" style="display:inline-block; background-color:#5FA252; color:#ffffff; font-weight:bold; padding:12px 24px; border-radius:6px; text-decoration:none;">
            Review Application Requests
        </a>
    </div>

    <p style="margin:0;">Thanks,<br><strong>{{ \App\Support\MailBranding::teamName() }}</strong></p>
@endsection

@section('footer')
    This notification was automatically sent from the PTL Admin System.
@endsection
