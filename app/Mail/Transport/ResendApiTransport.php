<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class ResendApiTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (trim($this->apiKey) === '') {
            throw new TransportException('Resend API key is not configured (RESEND_API_KEY).');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromAddresses = $email->getFrom();
        $from = !empty($fromAddresses)
            ? $fromAddresses[0]->toString()
            : $message->getEnvelope()->getSender()->toString();

        $payload = [
            'from'    => $from,
            'to'      => array_map(fn (Address $addr) => $addr->toString(), $email->getTo()),
            'subject' => (string) $email->getSubject(),
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map(fn (Address $addr) => $addr->toString(), $cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map(fn (Address $addr) => $addr->toString(), $bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $payload['reply_to'] = array_map(fn (Address $addr) => $addr->toString(), $replyTo);
        }

        if (($html = $email->getHtmlBody()) !== null) {
            $payload['html'] = (string) $html;
        }

        if (($text = $email->getTextBody()) !== null) {
            $payload['text'] = (string) $text;
        }

        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'filename' => $attachment->getFilename() ?: 'attachment',
                'content'  => base64_encode($attachment->getBody()),
            ];
        }
        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
        }

        $request = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(10);

        if (
            PHP_OS_FAMILY === 'Windows'
            && defined('CURLSSLOPT_NATIVE_CA')
            && !ini_get('curl.cainfo')
            && !ini_get('openssl.cafile')
        ) {
            $request = $request->withOptions([
                'curl' => [
                    CURLOPT_SSL_OPTIONS => CURLSSLOPT_NATIVE_CA,
                ],
            ]);
        }

        $response = $request->post('https://api.resend.com/emails', $payload);

        if ($response->failed()) {
            $errorMessage = $response->json('message') ?: $response->body() ?: 'Unknown Resend API error';
            throw new TransportException(
                "Resend API failed (HTTP {$response->status()}): {$errorMessage}",
                $response->status()
            );
        }

        $resendId = $response->json('id');
        if ($resendId) {
            $message->getOriginalMessage()->getHeaders()->addTextHeader('X-Resend-Email-ID', (string) $resendId);
        }
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
