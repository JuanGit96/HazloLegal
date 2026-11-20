<?php

declare(strict_types=1);

namespace App\Domain\Shared\Mail;

use App\Domain\Shared\Enums\ContactKind;
use App\Domain\Shared\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Para quien escribió: su número de radicado.
 *
 * En una solicitud de eliminación de datos esto no es cortesía: es la
 * constancia de que la petición entró y en qué fecha, que es lo que cuenta
 * el plazo legal de respuesta.
 */
class ContactAcknowledgementMail extends Mailable
{
    public function __construct(public readonly ContactMessage $message) {}

    public function envelope(): Envelope
    {
        $subject = $this->message->kind === ContactKind::DataDeletion
            ? "Recibimos tu solicitud de eliminación de datos · {$this->message->reference}"
            : "Recibimos tu mensaje · {$this->message->reference}";

        return new Envelope(
            subject: $subject,
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact.acknowledgement', with: ['message' => $this->message]);
    }
}
