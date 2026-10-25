<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Mail;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LawyerAssignedMail extends Mailable
{
    public function __construct(
        public readonly LegalCase $case,
        public readonly Lawyer $lawyer,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ya tienes abogado asignado para tu caso {$this->case->reference}",
            replyTo: [config('hazlolegal.contact.cases_email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cases.lawyer-assigned', with: [
            'case' => $this->case,
            'lawyer' => $this->lawyer,
        ]);
    }
}
