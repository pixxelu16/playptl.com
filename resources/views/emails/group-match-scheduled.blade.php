<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Match scheduled</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;">
            <h2 style="margin:0 0 10px;font-size:18px;">
                {{ ($updatedByOpponent ?? false) ? 'Your match schedule was updated' : 'Your match is scheduled' }}
            </h2>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                Hi {{ $recipientDisplayName }},
            </p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;">
                @if ($updatedByOpponent ?? false)
                    Your opponent has updated the schedule for your <strong>{{ $formatLabel }}</strong> match in
                @else
                    An administrator has scheduled a <strong>{{ $formatLabel }}</strong> match for you in
                @endif
                <strong>{{ $leagueName }}</strong>
                @if ($divisionName !== '')
                    ({{ $divisionName }})
                @endif
                @if ($groupName !== '')
                    — {{ $groupName }}
                @endif
                .
            </p>

            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin:14px 0;">
                <p style="margin:0 0 8px;font-size:13px;"><strong>Date:</strong> {{ $matchDateDisplay }}</p>
                <p style="margin:0 0 8px;font-size:13px;"><strong>Time:</strong> {{ $startTime }}</p>
                <p style="margin:0 0 8px;font-size:13px;"><strong>Venue:</strong> {{ $venueDisplay }}</p>
                <p style="margin:0;font-size:13px;line-height:1.45;"><strong>Match-up:</strong> {{ $opponentSummary }}</p>
            </div>

            <p style="margin:0;font-size:13px;line-height:1.5;color:#374151;">
                Good luck — see you on court.<br>
                {{ config('app.name', 'Premier Tennis League') }}
            </p>
        </div>
        <p style="margin:12px 0 0;font-size:12px;color:#6b7280;text-align:center;">
            If this doesn’t look right, contact your league administrator.
        </p>
    </div>
</body>
</html>
