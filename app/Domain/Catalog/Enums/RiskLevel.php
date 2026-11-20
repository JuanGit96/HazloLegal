<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * El semáforo de un caso.
 *
 * No mide la gravedad jurídica: mide si un documento automático le sirve a
 * esta persona. Alguien en urgencias no necesita un derecho de petición
 * mejor redactado, necesita una tutela.
 */
enum RiskLevel: string implements HasColor, HasLabel
{
    case Green = 'green';
    case Amber = 'amber';
    case Red = 'red';
    case Blocked = 'blocked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Green => 'Sin señales',
            self::Amber => 'Con señales de alerta',
            self::Red => 'Necesita otra vía',
            self::Blocked => 'Bloqueado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Green => 'success',
            self::Amber => 'warning',
            self::Red => 'danger',
            self::Blocked => 'gray',
        };
    }

    /** ¿Se puede generar el documento estándar? */
    public function allowsDocument(): bool
    {
        return in_array($this, [self::Green, self::Amber], true);
    }

    /** ¿Hay que pedirle al usuario que acepte la advertencia? */
    public function requiresAcknowledgement(): bool
    {
        return $this === self::Amber;
    }
}
