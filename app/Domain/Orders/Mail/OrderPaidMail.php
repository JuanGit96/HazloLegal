<?php

declare(strict_types=1);

namespace App\Domain\Orders\Mail;

use App\Domain\Orders\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPaidMail extends Mailable
{
    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pago confirmado · {$this->order->reference} · ya estamos redactando",
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.paid', with: [
            'order' => $this->order->loadMissing(['document', 'plan']),
        ]);
    }
}
