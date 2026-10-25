<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Mail;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** El correo donde se corre la carrera: el primero que acepta se lleva el caso. */
class CaseOfferMail extends Mailable
{
    public function __construct(
        public readonly LegalCase $case,
        public readonly CaseOffer $offer,
        public readonly Lawyer $lawyer,
    ) {}

    public function envelope(): Envelope
    {
        $urgency = $this->case->urgency->shortLabel();

        return new Envelope(
            subject: "[{$urgency}] Caso nuevo {$this->case->reference}: {$this->case->title}",
            replyTo: [config('hazlolegal.contact.cases_email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cases.offer', with: [
            'case' => $this->case,
            'offer' => $this->offer,
            'lawyer' => $this->lawyer,
        ]);
    }
}
