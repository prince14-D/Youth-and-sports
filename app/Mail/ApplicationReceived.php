<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicationReceived extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Application received: '.$this->application->vacancy->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recruitment.application-received');
    }
}