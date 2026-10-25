<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ClaimStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case InReview = 'in_review';
    case Corrected = 'corrected';
    case Refunded = 'refunded';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Abierta',
            self::InReview => 'En revisión',
            self::Corrected => 'Corregida',
            self::Refunded => 'Devuelta',
            self::Rejected => 'No procede',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::InReview => 'info',
            self::Corrected => 'success',
            self::Refunded => 'success',
            self::Rejected => 'danger',
        };
    }
}
