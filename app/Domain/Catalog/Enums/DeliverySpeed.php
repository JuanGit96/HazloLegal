<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliverySpeed: string implements HasColor, HasLabel
{
    case Standard = 'standard';
    case Express = 'express';

    public function getLabel(): string
    {
        return match ($this) {
            self::Standard => 'Estándar (24 h)',
            self::Express => 'Express (4 h)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Standard => 'info',
            self::Express => 'warning',
        };
    }

    public function hours(): int
    {
        return match ($this) {
            self::Standard => 24,
            self::Express => 4,
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Standard => 'Estándar',
            self::Express => 'Express',
        };
    }
}
