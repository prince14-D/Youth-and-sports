<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
class DepartmentCommunicationEmail extends Mailable implements ShouldQueue { use Queueable; public function __construct(public string $reference,public string $fromDepartment,public string $subjectLine,public string $bodyText,public ?string $actionUrl=null){} public function envelope():Envelope{return new Envelope(subject:"Department request {$this->reference}: {$this->subjectLine}");} public function content():Content{return new Content(view:'emails.department-communication');} }
