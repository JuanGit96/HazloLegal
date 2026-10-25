<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Enums;

use Filament\Support\Contracts\HasLabel;

enum BudgetRange: string implements HasLabel
{
    case Under1M = 'under_1m';
    case From1To3M = '1m_3m';
    case From3To10M = '3m_10m';
    case Over10M = 'over_10m';
    case Unsure = 'unsure';

    public function getLabel(): string
    {
        return match ($this) {
            self::Under1M => 'Hasta $1.000.000',
            self::From1To3M => 'Entre $1.000.000 y $3.000.000',
            self::From3To10M => 'Entre $3.000.000 y $10.000.000',
            self::Over10M => 'Más de $10.000.000',
            self::Unsure => 'Necesito que me orienten',
        };
    }
}
