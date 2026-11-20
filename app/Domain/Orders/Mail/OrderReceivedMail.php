<?php

declare(strict_types=1);

namespace App\Domain\Orders\Mail;

use App\Domain\Orders\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;

/**
 * El primer correo tras el pedido.
 *
 * Cambia de tono según haya algo que cobrar: un trámite gratuito no tiene
 * «pago pendiente» que reclamar, tiene un documento que entregar.
 */
class OrderReceivedMail extends Mailable
{
    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        $subject = $this->order->isFree()
            ? "Tu {$this->order->document->name} está listo · HazloLegal"
            : "Tu pedido {$this->order->reference} quedó registrado · HazloLegal";

        return new Envelope(
            subject: $subject,
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.received', with: [
            'order' => $this->order->loadMissing(['document', 'addons']),
        ]);
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $files = [];
        $disk = Storage::disk('local');

        if ($this->order->document_path && $disk->exists($this->order->document_path)) {
            $files[] = Attachment::fromStorageDisk('local', $this->order->document_path)
                ->as(\App\Domain\Documents\Services\LegalDocumentPdfBuilder::fileName($this->order))
                ->withMime('application/pdf');
        }

        if ($this->order->instructions_path && $disk->exists($this->order->instructions_path)) {
            $files[] = Attachment::fromStorageDisk('local', $this->order->instructions_path)
                ->as("instructivo-{$this->order->reference}.pdf")
                ->withMime('application/pdf');
        }

        return $files;
    }
}
