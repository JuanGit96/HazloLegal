<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** En qué quedó el intento de comprobar una tarjeta. */
enum CardCheck: string implements HasColor, HasLabel
{
    case Approved = 'approved';
    case Declined = 'declined';
    /** Wompi todavía no responde; lo cierra el webhook. */
    case Pending = 'pending';
    /** No se pudo ni lanzar el cobro: la pasarela no respondió. */
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Approved => 'Tarjeta aprobada',
            self::Declined => 'Tarjeta rechazada',
            self::Pending => 'En confirmación',
            self::Failed => 'No se pudo comprobar',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Declined => 'danger',
            self::Pending => 'warning',
            self::Failed => 'gray',
        };
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }
}
