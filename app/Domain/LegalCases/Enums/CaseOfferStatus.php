<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CaseOfferStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    /** Otro abogado tomó el caso primero. */
    case Superseded = 'superseded';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Sin responder',
            self::Accepted => 'Aceptado',
            self::Declined => 'Rechazado',
            self::Superseded => 'Tomado por otro',
            self::Expired => 'Vencido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::Superseded => 'gray',
            self::Expired => 'gray',
        };
    }
}
