<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderApplicationDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $decision // 'approved' or 'rejected'
    ) {}

    public function envelope(): Envelope
    {
        $roleTitle = ucfirst($this->user->role->value ?? (string) $this->user->role);
        $subject = $this->decision === 'approved'
            ? "Your {$roleTitle} Application Has Been Approved! 🎉"
            : "Update Regarding Your {$roleTitle} Application";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-decision',
        );
    }
}
