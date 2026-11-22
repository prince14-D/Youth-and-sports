<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipDistributionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $payslipData,
        public string $pdfBinary,
        public string $pdfFilename,
    ) {
    }

    public function envelope(): Envelope
    {
        $periodName = $this->payslipData['record']->period?->name ?? 'Payroll Period';
        $docType = !empty($this->payslipData['isContractor']) ? 'Payment Advice' : 'Salary Payslip';

        return new Envelope(
            subject: "Ministry of Youth & Sports — Official {$docType} ({$periodName})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip-distribution',
            with: $this->payslipData,
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
