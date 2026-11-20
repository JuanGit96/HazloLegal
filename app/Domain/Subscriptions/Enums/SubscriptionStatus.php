<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: string implements HasColor, HasLabel
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Trialing => 'En prueba gratis',
            self::Active => 'Activa',
            self::PastDue => 'Pago pendiente',
            self::Cancelled => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Trialing => 'info',
            self::Active => 'success',
            self::PastDue => 'warning',
            self::Cancelled => 'gray',
        };
    }

    /** ¿El abogado disfruta de los beneficios del plan? */
    public function grantsAccess(): bool
    {
        return in_array($this, [self::Trialing, self::Active], true);
    }
}
