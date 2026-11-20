<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum RiskRuleKind: string implements HasLabel
{
    /** Una pregunta de sí o no, antes de redactar. Es la capa principal. */
    case Question = 'question';

    /** Una palabra en el texto libre. Red de seguridad, nunca bloquea sola. */
    case Keyword = 'keyword';

    /** Groserías y amenazas: impiden generar hasta que se reescriba. */
    case Blocked = 'blocked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Question => 'Pregunta del formulario',
            self::Keyword => 'Palabra en el texto libre',
            self::Blocked => 'Palabra que bloquea',
        };
    }
}
