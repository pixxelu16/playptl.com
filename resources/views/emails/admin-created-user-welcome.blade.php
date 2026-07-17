<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="font-family: 'Inter', system-ui, -apple-system, sans-serif; background-color: #f4f7f6; margin: 0; padding: 40px 0;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <!-- Header -->
        <tr>
            <td style="background-color: #1A301D; padding: 32px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; tracking-wide: 0.05em; text-transform: uppercase;">
                    Welcome to {{ config('app.name', 'playptl') }}
                </h1>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding: 40px 32px; color: #334155; line-height: 1.6;">
                <h2 style="color: #1e293b; margin-top: 0; margin-bottom: 20px; font-size: 18px; font-weight: 700;">Hello {{ $user->name }},</h2>
                <p style="margin-bottom: 24px; font-size: 15px;">
                    An account has been created for you by the administrator on {{ config('app.name') }}.
                </p>
                
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                    <p style="margin: 0 0 8px 0; font-size: 14px; color: #64748b;"><strong>Your Account Details:</strong></p>
                    <p style="margin: 0 0 6px 0; font-size: 14px;"><strong>Email:</strong> {{ $user->email }}</p>
                    <p style="margin: 0; font-size: 14px;"><strong>Username:</strong> {{ $user->username }}</p>
                </div>

                <p style="margin-bottom: 30px; font-size: 15px;">
                    To secure your account and set your password, please click the button below. This link is secure and will expire in 60 minutes.
                </p>

                <div style="text-align: center; margin-bottom: 35px;">
                    <a href="{{ $resetUrl }}" style="background-color: #5DA44E; color: #ffffff; text-decoration: none; padding: 14px 30px; font-size: 15px; font-weight: 700; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(93, 164, 78, 0.2);">
                        Set Your Password
                    </a>
                </div>

                <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
                    If you did not expect this, you can safely ignore this email.
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
