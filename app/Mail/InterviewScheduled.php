<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InterviewScheduled extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public Interview $interview) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Interview scheduled for '.$this->interview->application->vacancy->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recruitment.interview-scheduled');
    }
}