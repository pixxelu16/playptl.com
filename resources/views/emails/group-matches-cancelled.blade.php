<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Group matches cancelled</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;">
            <h2 style="margin:0 0 10px;font-size:18px;">Group matches cancelled</h2>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Hi {{ $recipientDisplayName }},
            </p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                The league administrator has cancelled
                @if ($divisionWideCancel ?? false)
                    <strong>all scheduled group matches</strong>
                    for <strong>{{ $divisionName !== '' ? $divisionName : $leagueName }}</strong>
                    (all subgroups)
                @else
                    <strong>{{ $cancelledMatchCount }} {{ $cancelledMatchCount === 1 ? 'scheduled group match' : 'scheduled group matches' }}</strong>
                    for <strong>{{ $groupName }}</strong> in
                    <strong>{{ $leagueName }}</strong>
                    @if ($divisionName !== '')
                        ({{ $divisionName }})
                    @endif
                @endif
                .
            </p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                These matches are no longer on your schedule. You will receive a new notification when matches are rescheduled.
            </p>
            <p style="margin:0;font-size:13px;line-height:1.5;color:#374151;">
                If you have questions, please contact your league administrator.<br>
                {{ config('app.name', 'Premier Tennis League') }}
            </p>
        </div>
        <p style="margin:12px 0 0;font-size:12px;color:#6b7280;text-align:center;">
            If this doesn’t look right, contact your league administrator.
        </p>
    </div>
</body>
</html>
