<?php

declare(strict_types=1);

namespace App\Domain\Payments\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Voided = 'voided';
    case Error = 'error';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobado',
            self::Declined => 'Rechazado',
            self::Voided => 'Anulado',
            self::Error => 'Error',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Declined => 'danger',
            self::Voided => 'gray',
            self::Error => 'danger',
        };
    }

    /** Traduce el estado que manda Wompi. */
    public static function fromWompi(string $status): self
    {
        return match (strtoupper($status)) {
            'APPROVED' => self::Approved,
            'DECLINED' => self::Declined,
            'VOIDED' => self::Voided,
            'ERROR' => self::Error,
            default => self::Pending,
        };
    }
}
