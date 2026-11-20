<?php

declare(strict_types=1);

namespace App\Domain\Orders\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerAccessMail extends Mailable
{
    public function __construct(
        public readonly string $email,
        public readonly string $link,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu enlace para ver tus trámites · HazloLegal',
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.access', with: [
            'link' => $this->link,
        ]);
    }
}
