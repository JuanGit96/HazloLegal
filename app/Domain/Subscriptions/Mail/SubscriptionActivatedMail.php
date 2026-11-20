<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Mail;

use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SubscriptionActivatedMail extends Mailable
{
    public function __construct(
        public readonly LawyerSubscription $subscription,
        public readonly ?PaymentTransaction $transaction = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu plan {$this->subscription->plan?->name} está activo · HazloLegal",
            replyTo: [config('hazlolegal.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscriptions.activated', with: [
            'subscription' => $this->subscription->loadMissing(['plan', 'lawyer']),
            'transaction' => $this->transaction,
        ]);
    }
}
