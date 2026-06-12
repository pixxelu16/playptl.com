@extends('emails.layout')

@section('title', $emailSubject)
@section('header', $emailSubject)
@section('preheader', $emailSubject)

@section('content')
    <p style="margin:0 0 14px;">Hi {{ $donorName }},</p>
    <div style="margin:0 0 14px;font-size:14px;line-height:1.65;color:#424242;">
        {!! nl2br(e($adminMessage)) !!}
    </div>
    <p style="margin:0;">
        Thanks,<br>
        <strong>{{ $adminName }}</strong><br>
        {{ config('app.name', 'Premier Tennis League') }}
    </p>
@endsection

@section('footer')
    Thank you for supporting our charity initiatives.
@endsection
