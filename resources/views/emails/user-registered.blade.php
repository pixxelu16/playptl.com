@extends('emails.layout')

@section('title', $isAdminNotification ? 'New User Registered' : 'Welcome!')
@section('header', $isAdminNotification ? 'New User Registered' : 'Registration Confirmed')
@section('preheader', $isAdminNotification ? 'A new user has registered.' : 'Welcome to Premier Tennis League.')

@section('content')
    @if($isAdminNotification)
        <p style="margin:0 0 14px;">Hello Admin,</p>
        <p style="margin:0 0 14px;">
            A new user has registered on the platform with the following details:
        </p>

        @include('emails.partials.info-box', ['content' => '
            <p style="margin:0 0 8px;"><strong>Name:</strong> '.e($user->name).'</p>
            <p style="margin:0 0 8px;"><strong>Email:</strong> '.e($user->email).'</p>
            <p style="margin:0 0 8px;"><strong>Phone:</strong> '.e($user->phone).'</p>
            <p style="margin:0 0 8px;"><strong>Role:</strong> '.e(ucfirst($user->role->value ?? $user->role)).'</p>
            <p style="margin:0 0 8px;"><strong>City:</strong> '.e($user->city).'</p>
            <p style="margin:0;"><strong>State:</strong> '.e($user->state).'</p>
        '])
    @else
        <p style="margin:0 0 14px;">Hi {{ $user->name }},</p>
        <p style="margin:0 0 14px;">
            Thank you for registering on <strong>{{ config('app.name', 'Premier Tennis League') }}</strong>. Your account has been created successfully.
        </p>

        @include('emails.partials.info-box', ['content' => '
            <p style="margin:0 0 8px;"><strong>Name:</strong> '.e($user->name).'</p>
            <p style="margin:0 0 8px;"><strong>Email:</strong> '.e($user->email).'</p>
            <p style="margin:0 0 8px;"><strong>Role:</strong> '.e(ucfirst($user->role->value ?? $user->role)).'</p>
            <p style="margin:0 0 8px;"><strong>City:</strong> '.e($user->city).'</p>
            <p style="margin:0;"><strong>State:</strong> '.e($user->state).'</p>
        '])

        <p style="margin:0 0 24px;">
            You can log in to your profile at any time to update your information and start using the platform.
        </p>

        @include('emails.partials.button', [
            'url' => route('login'),
            'label' => 'Log in to my account',
        ])
    @endif

    <p style="margin:14px 0 0;">Thanks,<br><strong>{{ config('app.name', 'Premier Tennis League') }}</strong></p>
@endsection
