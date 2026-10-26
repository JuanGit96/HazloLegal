<?php

declare(strict_types=1);

namespace App\Domain\Orders\Mail;

use App\Domain\Orders\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderReceivedMail extends Mailable
{
    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu pedido {$this->order->reference} quedó registrado · HazloLegal",
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.received', with: [
            'order' => $this->order->loadMissing(['document', 'plan']),
        ]);
    }
}
