<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Queues outbound emails so bulk actions do not block HTTP requests.
 * Stagger delays spread sends when many messages are queued at once.
 */
final class MatchScheduleMailQueue
{
    private static int $staggerIndex = 0;

    public static function beginBulkScheduling(): void
    {
        self::$staggerIndex = 0;
    }

    public static function queue(string $email, Mailable $mailable): void
    {
        $email = trim($email);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        $secondsBetween = max(0, (int) config('mail.schedule_queue_stagger_seconds', 2));
        $delaySeconds = self::$staggerIndex * $secondsBetween;
        self::$staggerIndex++;

        try {
            if ($delaySeconds > 0) {
                Mail::to($email)->later(now()->addSeconds($delaySeconds), $mailable);
            } else {
                Mail::to($email)->queue($mailable);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
