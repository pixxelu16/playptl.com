<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;">
            <h2 style="margin:0 0 10px;font-size:18px;">Registration confirmed</h2>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Hi {{ $userName }},
            </p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Your registration for <strong>{{ $leagueName }}</strong> has been confirmed.
            </p>

            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin:14px 0;">
                <p style="margin:0 0 6px;font-size:13px;"><strong>Type:</strong> {{ ucfirst($registrationType) }}</p>
                <p style="margin:0 0 6px;font-size:13px;"><strong>Skill level:</strong> {{ $skillLevel }}</p>
                <p style="margin:0 0 6px;font-size:13px;"><strong>Paid:</strong> {{ $currency }} {{ $amount }}</p>
                <p style="margin:0;font-size:13px;"><strong>Transaction:</strong> {{ $paymentIntentId }}</p>
            </div>

            <p style="margin:0;font-size:13px;line-height:1.5;color:#374151;">
                Thanks,<br>
                {{ config('app.name', 'Premier Tennis League') }}
            </p>
        </div>
        <p style="margin:12px 0 0;font-size:12px;color:#6b7280;text-align:center;">
            If you didn’t make this registration, please contact support.
        </p>
    </div>
</body>
</html>

