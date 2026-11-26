<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Mail;

use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Confirma por escrito un cambio que todavía no ha ocurrido.
 *
 * Un cambio de lo que se cobra tiene que quedar en el correo del abogado el
 * mismo día que lo pide, con la fecha en que empieza a regir. Si la primera
 * noticia del cambio es el cargo, lo siguiente es una llamada al banco.
 */
class SubscriptionScheduledMail extends Mailable
{
    public function __construct(public readonly LawyerSubscription $subscription) {}

    public function envelope(): Envelope
    {
        $cuando = $this->subscription->changesOn()?->translatedFormat('j \d\e F');

        return new Envelope(
            subject: $this->subscription->isEnding()
                ? "Tu plan termina el {$cuando} · HazloLegal"
                : "Desde el {$cuando} pasas al plan {$this->subscription->scheduledPlan?->name} · HazloLegal",
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscriptions.scheduled', with: [
            'subscription' => $this->subscription->loadMissing(['plan', 'scheduledPlan', 'lawyer']),
        ]);
    }
}
