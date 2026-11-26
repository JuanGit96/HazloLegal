<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Data;

use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Enums\CardCheck;

/** Cómo quedó la comprobación de una tarjeta, y con qué transacción. */
final readonly class CardVerification
{
    public function __construct(
        public CardCheck $outcome,
        public ?PaymentTransaction $transaction = null,
        /** Lo que dijo el banco, cuando dijo algo. */
        public ?string $reason = null,
        /** El cobro se hizo pero no se pudo anular: hay dinero que devolver. */
        public bool $pendingRefund = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            outcome: CardCheck::from((string) ($data['outcome'] ?? CardCheck::Failed->value)),
            transaction: $data['transaction'] ?? null,
            reason: $data['reason'] ?? null,
            pendingRefund: (bool) ($data['pendingRefund'] ?? false),
        );
    }

    public function isApproved(): bool
    {
        return $this->outcome->isApproved();
    }

    /** Lo que se le dice al abogado, en su idioma y sin jerga de pasarela. */
    public function message(): string
    {
        return match ($this->outcome) {
            CardCheck::Approved => 'Tu tarjeta quedó comprobada.',
            CardCheck::Declined => $this->reason
                ? 'Tu banco rechazó la tarjeta: '.$this->reason.' Prueba con otra.'
                : 'Tu banco rechazó la tarjeta. Puede ser el cupo, que no esté habilitada para compras en línea, o un dato mal escrito. Prueba con otra.',
            CardCheck::Pending => 'Estamos confirmando tu tarjeta con el banco. Te escribimos en cuanto responda.',
            CardCheck::Failed => 'No pudimos comunicarnos con la pasarela para comprobar la tarjeta. Inténtalo en unos minutos.',
        };
    }
}
