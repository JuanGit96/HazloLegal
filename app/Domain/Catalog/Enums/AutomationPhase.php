<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Fase 1 = plantilla maestra con inyección de variables (el abogado solo audita).
 * Fase 2 = redacción humana obligatoria (hechos, cálculos o cláusulas condicionales).
 */
enum AutomationPhase: string implements HasColor, HasLabel
{
    case Automated = 'automated';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Automated => 'Fase 1 · Automatizado',
            self::Manual => 'Fase 2 · Redacción manual',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Automated => 'success',
            self::Manual => 'warning',
        };
    }

    public function publicLabel(): string
    {
        return match ($this) {
            self::Automated => 'Entrega automatizada',
            self::Manual => 'Redacción a la medida',
        };
    }

    public function publicExplanation(): string
    {
        return match ($this) {
            self::Automated => 'Este documento se ensambla sobre una plantilla maestra validada y un abogado audita el PDF final antes de enviarlo.',
            self::Manual => 'Este documento exige análisis de tus hechos concretos, por lo que lo redacta un abogado desde cero. No usamos plantillas genéricas.',
        };
    }

    public function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])->all();
    }
}
