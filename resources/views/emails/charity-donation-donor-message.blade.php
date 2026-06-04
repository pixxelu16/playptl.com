<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $emailSubject }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;">
            <h2 style="margin:0 0 10px;font-size:18px;">{{ $emailSubject }}</h2>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Hi {{ $donorName }},
            </p>
            <div style="margin:0 0 14px;font-size:14px;line-height:1.6;color:#374151;">
                {!! nl2br(e($adminMessage)) !!}
            </div>
            <p style="margin:0;font-size:13px;line-height:1.5;color:#374151;">
                Thanks,<br>
                {{ $adminName }}<br>
                {{ config('app.name', 'Premier Tennis League') }}
            </p>
        </div>
    </div>
</body>
</html>
