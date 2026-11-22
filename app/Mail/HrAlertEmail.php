<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HrAlertEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertSubject,
        public string $recipientName,
        public string $badgeLabel,
        public string $badgeColor,
        public string $headline,
        public string $bodyMessage,
        public array $details = [],
        public ?string $actionUrl = null,
        public string $actionText = 'Open HRMIS Portal',
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MYS HRMIS Alert: ' . $this->alertSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.hr-alert');
    }
}
