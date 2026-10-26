<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Mail;

use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CaseReceivedMail extends Mailable
{
    public function __construct(public readonly LegalCase $case) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Recibimos tu caso {$this->case->reference} · HazloLegal",
            replyTo: [config('hazlolegal.contact.cases_email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cases.received', with: [
            'case' => $this->case,
            'ttlHours' => (int) config('hazlolegal.cases.offer_ttl_hours'),
        ]);
    }
}
