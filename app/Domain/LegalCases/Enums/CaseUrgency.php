<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CaseUrgency: string implements HasColor, HasLabel
{
    case Critical = 'critical';
    case High = 'high';
    case Normal = 'normal';
    case Exploring = 'exploring';

    public function getLabel(): string
    {
        return match ($this) {
            self::Critical => 'Tengo un término venciendo (días)',
            self::High => 'Urgente, esta semana',
            self::Normal => 'Pronto, este mes',
            self::Exploring => 'Estoy explorando opciones',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Critical => 'Término venciendo',
            self::High => 'Urgente',
            self::Normal => 'Normal',
            self::Exploring => 'Explorando',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Critical => 'danger',
            self::High => 'warning',
            self::Normal => 'info',
            self::Exploring => 'gray',
        };
    }
}
