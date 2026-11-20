<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Nivel efectivo de un pedido, deducido de los adicionales contratados.
 *
 * No se elige: se calcula. Si el pedido lleva «acompañamiento», es
 * acompañado; si no cuesta nada, es gratuito. Sirve para medir cuántos
 * gratuitos acaban convirtiendo, que es la métrica del modelo.
 */
enum OrderTier: string implements HasColor, HasLabel
{
    case Free = 'free';
    case SelfService = 'self_service';
    case Accompanied = 'accompanied';

    public function getLabel(): string
    {
        return match ($this) {
            self::Free => 'Gratuito',
            self::SelfService => 'Autoservicio',
            self::Accompanied => 'Con abogado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Free => 'gray',
            self::SelfService => 'info',
            self::Accompanied => 'success',
        };
    }

    public function requiresLawyer(): bool
    {
        return $this === self::Accompanied;
    }
}
