<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account setup</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;">
            <h2 style="margin:0 0 10px;font-size:18px;">You were added as a doubles partner</h2>

            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                {{ $inviterName }} added you to a doubles registration for <strong>{{ $leagueName }}</strong>.
            </p>

            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                To set up your account (same email), click the button below and create your password:
            </p>

            <p style="margin:18px 0;">
                <a href="{{ $setupUrl }}"
                   style="display:inline-block;background:#5FA252;color:#ffffff;text-decoration:none;padding:10px 14px;border-radius:10px;font-size:14px;font-weight:bold;">
                    Set up my account
                </a>
            </p>

            <p style="margin:0;font-size:12px;line-height:1.5;color:#6b7280;">
                If you did not expect this, you can ignore this email.
            </p>
        </div>
    </div>
</body>
</html>

