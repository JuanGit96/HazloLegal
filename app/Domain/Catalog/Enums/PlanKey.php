<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum PlanKey: string implements HasLabel
{
    case Document = 'document';
    case Advisory = 'advisory';
    case Complex = 'complex';

    public function getLabel(): string
    {
        return match ($this) {
            self::Document => 'Documento listo para usar',
            self::Advisory => 'Documento + asesoría',
            self::Complex => 'Caso complejo',
        };
    }

    /** ¿El plan permite elegir entre velocidad estándar y express? */
    public function hasSpeedChoice(): bool
    {
        return $this === self::Document;
    }

    public function isQuoteOnly(): bool
    {
        return $this === self::Complex;
    }
}
