<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmployeeWelcomeCredentials extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $employeeName,
        public string $employeeNumber,
        public string $employmentType,
        public string $email,
        public string $temporaryPassword,
        public string $loginUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to the Ministry HRMS - Your employee account');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.employee-welcome-credentials');
    }
}
