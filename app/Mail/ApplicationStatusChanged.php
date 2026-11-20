<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicationStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobApplication $application, public string $status) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Application update: '.$this->application->vacancy->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recruitment.status-changed');
    }
}