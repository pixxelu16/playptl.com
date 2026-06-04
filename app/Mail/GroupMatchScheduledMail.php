<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupMatchScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public string $recipientDisplayName,
        public string $leagueName,
        public string $divisionName,
        public string $groupName,
        public string $matchDateDisplay,
        public string $startTime,
        public string $venueDisplay,
        public string $formatLabel,
        public string $opponentSummary,
        public bool $updatedByOpponent = false,
        public bool $updatedByPlayer = false,
        public ?string $playoffRoundLabel = null,
        public bool $rosterChanged = false,
        public bool $removedFromMatch = false,
    ) {}

    public function envelope(): Envelope
    {
        $isPlayoff = $this->playoffRoundLabel !== null && $this->playoffRoundLabel !== '';
        $isUpdate = $this->updatedByOpponent || $this->updatedByPlayer;

        $subject = match (true) {
            $this->removedFromMatch => 'Playoff match assignment changed — '.$this->leagueName,
            $this->rosterChanged && $isPlayoff => 'Playoff match players updated — '.$this->leagueName,
            $isUpdate => 'Match schedule updated — '.$this->leagueName,
            $isPlayoff => 'Your playoff match is scheduled — '.$this->leagueName,
            default => 'Your match is scheduled — '.$this->leagueName,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.group-match-scheduled',
        );
    }
}
