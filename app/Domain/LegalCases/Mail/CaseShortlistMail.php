<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Mail;

use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Al cliente: ya hay abogados interesados y le toca elegir. */
class CaseShortlistMail extends Mailable
{
    public function __construct(public readonly LegalCase $case) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ya hay abogados interesados en tu caso · {$this->case->reference}",
            replyTo: [config('hazlolegal.contact.cases_email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cases.shortlist', with: [
            'case' => $this->case->loadMissing('acceptedOffers.lawyer'),
        ]);
    }
}
