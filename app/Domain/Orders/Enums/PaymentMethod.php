<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Nequi = 'nequi';
    case Daviplata = 'daviplata';
    case Transfer = 'transfer';
    case Card = 'card';
    case Pse = 'pse';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nequi => 'Nequi',
            self::Daviplata => 'Daviplata',
            self::Transfer => 'Transferencia bancaria',
            self::Card => 'Tarjeta débito / crédito',
            self::Pse => 'PSE',
        };
    }

    public function helper(): string
    {
        return match ($this) {
            self::Nequi => 'Pago inmediato desde tu celular.',
            self::Daviplata => 'Pago inmediato desde tu celular.',
            self::Transfer => 'Bancolombia ahorros. Te confirmamos al recibir.',
            self::Card => 'Procesado por pasarela certificada PCI-DSS.',
            self::Pse => 'Débito desde tu cuenta de ahorros o corriente.',
        };
    }
}
