<?php

declare(strict_types=1);

namespace App\Domain\Payments\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Para qué se abrió un cobro.
 *
 * `Verification` no es un cobro: son $1.500 —el mínimo que admite Wompi—
 * que se anulan en el mismo segundo. Existe porque registrar la tarjeta no
 * prueba que sirva: la pasarela acepta como «disponible» una tarjeta que
 * luego el banco rechaza, y lo único que distingue una de otra es intentar
 * cobrarla de verdad.
 */
enum PaymentPurpose: string implements HasColor, HasLabel
{
    case Payment = 'payment';
    case Verification = 'verification';

    public function getLabel(): string
    {
        return match ($this) {
            self::Payment => 'Cobro',
            self::Verification => 'Verificación de tarjeta',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Payment => 'primary',
            self::Verification => 'gray',
        };
    }
}
