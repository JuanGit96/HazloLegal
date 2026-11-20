<?php

declare(strict_types=1);

namespace App\Domain\Shared\Mail;

use App\Domain\Shared\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Para el equipo: alguien escribió y hay que atenderlo. */
class ContactReceivedMail extends Mailable
{
    public function __construct(public readonly ContactMessage $message) {}

    public function envelope(): Envelope
    {
        $prefix = $this->message->kind->getLabel();

        return new Envelope(
            subject: "[{$prefix}] {$this->message->reference} · {$this->message->name}",
            replyTo: [$this->message->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact.received', with: ['message' => $this->message]);
    }
}
