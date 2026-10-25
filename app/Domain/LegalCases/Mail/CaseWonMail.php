<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Mail;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Confirmación al abogado que ganó, ya con los datos de contacto del cliente. */
class CaseWonMail extends Mailable
{
    public function __construct(
        public readonly LegalCase $case,
        public readonly Lawyer $lawyer,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmado: el caso {$this->case->reference} es tuyo",
            replyTo: [config('hazlolegal.contact.cases_email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cases.won', with: [
            'case' => $this->case,
            'lawyer' => $this->lawyer,
        ]);
    }
}
