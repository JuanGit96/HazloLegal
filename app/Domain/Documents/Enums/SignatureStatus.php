<?php

declare(strict_types=1);

namespace App\Domain\Documents\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SignatureStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Signed = 'signed';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente de firma',
            self::Signed => 'Firmado',
            self::Expired => 'Vencido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Signed => 'success',
            self::Expired => 'gray',
        };
    }
}
