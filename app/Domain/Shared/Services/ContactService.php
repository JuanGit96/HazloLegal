<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Domain\Shared\Enums\ContactKind;
use App\Domain\Shared\Enums\ContactStatus;
use App\Domain\Shared\Mail\ContactAcknowledgementMail;
use App\Domain\Shared\Mail\ContactReceivedMail;
use App\Domain\Shared\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

/**
 * Recibe los mensajes del público.
 *
 * Manda dos correos a propósito: uno al equipo, para que alguien lo atienda,
 * y otro al remitente con su número de radicado. En una solicitud de
 * eliminación de datos ese acuse no es cortesía: es la prueba de que la
 * petición entró y cuándo, que es lo que cuenta el plazo de la Ley 1581.
 */
final class ContactService
{
    public function __construct(private readonly ReferenceGenerator $references) {}

    public function receive(array $data, ContactKind $kind): ContactMessage
    {
        $message = ContactMessage::query()->create([
            'reference' => $this->references->for(
                ContactMessage::class,
                $kind === ContactKind::DataDeletion ? 'DAT' : 'MSG',
            ),
            'kind' => $kind,
            'status' => ContactStatus::New,

            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],

            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'confirms_ownership' => (bool) ($data['confirms_ownership'] ?? false),

            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 255),
        ]);

        Mail::to(config('hazlolegal.contact.email'))->send(new ContactReceivedMail($message));
        Mail::to($message->email)->send(new ContactAcknowledgementMail($message));

        return $message;
    }

    public function resolve(ContactMessage $message, ?int $userId = null, ?string $notes = null): ContactMessage
    {
        $message->update([
            'status' => ContactStatus::Resolved,
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'internal_notes' => $notes ?? $message->internal_notes,
        ]);

        return $message->refresh();
    }
}
