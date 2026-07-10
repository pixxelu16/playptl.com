@extends('emails.layout')

@section('title', 'Your player account')
@section('header', 'Your account is ready')
@section('preheader', 'Your player account has been created. Log in with the details below.')

@section('content')
    <p style="margin:0 0 14px;">Hi {{ $userName }},</p>
    <p style="margin:0 0 14px;">
        An administrator has created your player account on {{ config('app.name', 'Premier Tennis League') }}.
        Use the details below to sign in.
    </p>

    @include('emails.partials.info-box', ['content' => '
        <p style="margin:0 0 8px;"><strong>Login URL:</strong> <a href="'.e($loginUrl).'" style="color:#66A157;">'.e($loginUrl).'</a></p>
        <p style="margin:0 0 8px;"><strong>Email:</strong> '.e($email).'</p>
        <p style="margin:0;"><strong>Temporary password:</strong> '.e($password).'</p>
    '])

    @include('emails.partials.button', [
        'url' => $loginUrl,
        'label' => 'Log in to my account',
    ])

    <p style="margin:0;font-size:13px;color:#424242;">
        For security, please change your password after your first login.
    </p>
@endsection

@section('footer')
    If you did not request this account, please contact your league administrator.
@endsection
