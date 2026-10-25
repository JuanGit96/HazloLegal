<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Fase 1 = plantilla maestra con inyección de variables. La plantilla la
 *          escriben y mantienen nuestros abogados; el documento NO se revisa
 *          caso por caso.
 * Fase 2 = redacción humana obligatoria (hechos, cálculos o cláusulas
 *          condicionales): aquí sí lo escribe un abogado para ese caso.
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
            self::Automated => 'Documento automatizado',
            self::Manual => 'Lo escribe un abogado',
        };
    }

    public function publicExplanation(): string
    {
        return match ($this) {
            self::Automated => 'Este documento se arma sobre una plantilla que redactaron nuestros abogados y que actualizamos cuando cambia la norma. No se revisa caso por caso: por eso cuesta y tarda menos.',
            self::Manual => 'Este documento exige analizar tus hechos concretos, así que lo escribe un abogado para tu caso. No usamos plantillas genéricas.',
        };
    }

    public function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])->all();
    }
}
