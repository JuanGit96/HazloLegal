<?php

declare(strict_types=1);

namespace App\Domain\Lawyers\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum LawyerKind: string implements HasColor, HasIcon, HasLabel
{
    case Individual = 'individual';
    case Firm = 'firm';

    public function getLabel(): string
    {
        return match ($this) {
            self::Individual => 'Abogado independiente',
            self::Firm => 'Firma de abogados',
        };
    }

    /** Cómo se le nombra en el sitio público. */
    public function publicLabel(): string
    {
        return match ($this) {
            self::Individual => 'Abogado',
            self::Firm => 'Firma',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Individual => 'info',
            self::Firm => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Individual => Heroicon::OutlinedUser,
            self::Firm => Heroicon::OutlinedBuildingOffice2,
        };
    }
}
