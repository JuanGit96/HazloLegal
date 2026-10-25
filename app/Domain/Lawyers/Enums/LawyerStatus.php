<?php

declare(strict_types=1);

namespace App\Domain\Lawyers\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LawyerStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Paused = 'paused';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Paused => 'En pausa',
            self::Inactive => 'Inactivo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Paused => 'warning',
            self::Inactive => 'gray',
        };
    }

    public function canReceiveCases(): bool
    {
        return $this === self::Active;
    }
}
