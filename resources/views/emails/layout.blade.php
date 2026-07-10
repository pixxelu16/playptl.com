@php
    use App\Support\MailBranding;

    $appName = MailBranding::appName();
    $siteUrl = MailBranding::siteUrl();
    $logoUrl = MailBranding::logoUrl();
    $pageTitle = trim($__env->yieldContent('title')) ?: $appName;
    $emailHeader = trim($__env->yieldContent('header'));
    $preheader = trim($__env->yieldContent('preheader'));
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $pageTitle }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    @if ($preheader !== '')
        <span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;">{{ $preheader }}</span>
    @endif
</head>
<body style="margin:0;padding:0;background:#eef2f0;font-family:Arial,Helvetica,sans-serif;color:#333333;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#eef2f0;">
        <tr>
            <td align="center" style="padding:28px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:620px;">
                    {{-- Header / logo --}}
                    <tr>
                        <td align="center" style="padding:0 0 18px;">
                            <a href="{{ $siteUrl }}" style="text-decoration:none;display:inline-block;">
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="180" style="display:block;max-width:180px;height:auto;border:0;outline:none;text-decoration:none;">
                            </a>
                        </td>
                    </tr>

                    {{-- Main card --}}
                    <tr>
                        <td style="background:#ffffff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                @if ($emailHeader !== '')
                                    <tr>
                                        <td style="background:{{ MailBranding::colorPrimary() }};padding:16px 24px;">
                                            <h1 style="margin:0;font-size:18px;line-height:1.35;font-weight:700;color:#ffffff;">{{ $emailHeader }}</h1>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:24px 24px 20px;font-size:14px;line-height:1.6;color:#424242;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:18px 8px 0;">
                            @hasSection('footer')
                                <p style="margin:0 0 10px;font-size:12px;line-height:1.5;color:#666666;text-align:center;">
                                    @yield('footer')
                                </p>
                            @endif
                            <p style="margin:0 0 6px;font-size:12px;line-height:1.5;color:#888888;text-align:center;">
                                &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </p>
                            @if ($siteUrl !== '')
                                <p style="margin:0;font-size:12px;line-height:1.5;text-align:center;">
                                    <a href="{{ $siteUrl }}" style="color:{{ MailBranding::colorPrimary() }};text-decoration:none;font-weight:600;">{{ str_replace(['https://', 'http://'], '', $siteUrl) }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
