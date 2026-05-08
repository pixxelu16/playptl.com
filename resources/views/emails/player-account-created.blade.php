<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account created</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;">
            <h2 style="margin:0 0 10px;font-size:18px;">Your account is ready</h2>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Hi {{ $userName }},
            </p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                An admin has created your player account on {{ config('app.name', 'playptl') }}. You can login using the details below.
            </p>

            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin:14px 0;">
                <p style="margin:0 0 6px;font-size:13px;"><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
                <p style="margin:0 0 6px;font-size:13px;"><strong>Email:</strong> {{ $email }}</p>
                <p style="margin:0;font-size:13px;"><strong>Temporary password:</strong> {{ $password }}</p>
            </div>

            <p style="margin:0;font-size:13px;line-height:1.5;color:#374151;">
                For security, please change your password after logging in.
            </p>

            <p style="margin:14px 0 0;font-size:13px;line-height:1.5;color:#374151;">
                Thanks,<br>
                {{ config('app.name', 'Premier Tennis League') }}
            </p>
        </div>
        <p style="margin:12px 0 0;font-size:12px;color:#6b7280;text-align:center;">
            If you did not request this account, please contact support.
        </p>
    </div>
</body>
</html>

