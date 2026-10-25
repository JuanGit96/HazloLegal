<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasLabel;

/** Motivos cubiertos por el Certificado de Garantía Hazlo Legal. */
enum ClaimReason: string implements HasLabel
{
    case Typo = 'typo';
    case WrongData = 'wrong_data';
    case RejectedByEntity = 'rejected_by_entity';
    case LateDelivery = 'late_delivery';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Typo => 'Errores de ortografía o redacción',
            self::WrongData => 'Datos míos o de la contraparte mal transcritos',
            self::RejectedByEntity => 'La entidad rechazó el documento por un defecto de forma',
            self::LateDelivery => 'No cumplieron el tiempo de entrega prometido',
            self::Other => 'Otro motivo',
        };
    }

    /** Motivos con derecho a devolución del 100 %. */
    public function qualifiesForRefund(): bool
    {
        return in_array($this, [self::RejectedByEntity, self::LateDelivery], true);
    }
}
