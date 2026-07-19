<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Locked — Security Alert</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f1f5f9;padding:32px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                   style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:600px;width:100%;">

                {{-- Header --}}
                <tr>
                    <td style="background:#dc2626;padding:28px 40px;text-align:center;">
                        <p style="margin:0;font-size:28px;">🔒</p>
                        <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:0.5px;">
                            Security Alert — Account Locked
                        </h1>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 40px 28px;">
                        <p style="margin:0 0 16px;font-size:15px;color:#374151;line-height:1.6;">
                            Hi Admin,
                        </p>
                        <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.6;">
                            A user account has been <strong style="color:#dc2626;">automatically locked</strong>
                            after <strong>3 consecutive failed login attempts</strong>. This could indicate
                            a brute-force or credential-stuffing attack.
                        </p>

                        {{-- User Details Box --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                               style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;margin-bottom:24px;">
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 10px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#991b1b;">
                                        Locked Account Details
                                    </p>
                                    <table cellpadding="0" cellspacing="0" role="presentation">
                                        <tr>
                                            <td style="font-size:14px;color:#6b7280;padding:4px 0;min-width:120px;">Name:</td>
                                            <td style="font-size:14px;color:#111827;font-weight:600;padding:4px 0;">{{ $lockedUser->name }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px;color:#6b7280;padding:4px 0;">Email:</td>
                                            <td style="font-size:14px;color:#111827;font-weight:600;padding:4px 0;">{{ $lockedUser->email }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px;color:#6b7280;padding:4px 0;">Role:</td>
                                            <td style="font-size:14px;color:#111827;font-weight:600;padding:4px 0;">{{ ucfirst($lockedUser->role?->value ?? 'N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:14px;color:#6b7280;padding:4px 0;">Locked At:</td>
                                            <td style="font-size:14px;color:#111827;font-weight:600;padding:4px 0;">{{ now()->format('D, d M Y H:i:s T') }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
                            If this user is legitimate and was locked out by mistake, you can unblock their
                            account from the admin panel using the button below:
                        </p>

                        {{-- CTA Button --}}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="center" style="padding-bottom:28px;">
                                    <a href="{{ $unblockUrl }}"
                                       style="display:inline-block;background:#5DA44E;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:8px;letter-spacing:0.3px;">
                                        🔓 Unblock This Account
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.5;border-top:1px solid #f3f4f6;padding-top:20px;">
                            If you believe this was a genuine attack, do <strong>not</strong> unblock the account and investigate further.
                            This notification was sent because you are the site administrator.
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f9fafb;border-top:1px solid #f3f4f6;padding:20px 40px;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#9ca3af;">
                            {{ config('app.name', 'PlayPTL') }} · Automated Security Notification
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
