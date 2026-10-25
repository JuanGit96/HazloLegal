<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Mail;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CaseTakenByOtherMail extends Mailable
{
    public function __construct(
        public readonly LegalCase $case,
        public readonly Lawyer $lawyer,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "El caso {$this->case->reference} ya fue tomado");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cases.taken-by-other', with: [
            'case' => $this->case,
            'lawyer' => $this->lawyer,
        ]);
    }
}
