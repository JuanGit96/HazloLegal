<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PracticeArea: string implements HasColor, HasIcon, HasLabel
{
    case Salud = 'salud';
    case Financiero = 'financiero';
    case Transito = 'transito';
    case Inmobiliario = 'inmobiliario';
    case Laboral = 'laboral';
    case Empresarial = 'empresarial';
    case Datos = 'datos';
    case Familia = 'familia';
    case Consumo = 'consumo';

    public function getLabel(): string
    {
        return match ($this) {
            self::Salud => 'Salud y EPS',
            self::Financiero => 'Financiero y crédito',
            self::Transito => 'Tránsito y movilidad',
            self::Inmobiliario => 'Inmobiliario y arriendos',
            self::Laboral => 'Laboral',
            self::Empresarial => 'Empresarial y societario',
            self::Datos => 'Datos personales',
            self::Familia => 'Familia y sucesiones',
            self::Consumo => 'Consumo y servicios',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Salud => 'danger',
            self::Financiero => 'warning',
            self::Transito => 'info',
            self::Inmobiliario => 'success',
            self::Laboral => 'primary',
            self::Empresarial => 'gray',
            self::Datos => 'info',
            self::Familia => 'warning',
            self::Consumo => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Salud => Heroicon::OutlinedShieldCheck,
            self::Financiero => Heroicon::OutlinedBanknotes,
            self::Transito => Heroicon::OutlinedFlag,
            self::Inmobiliario => Heroicon::OutlinedBuildingOffice2,
            self::Laboral => Heroicon::OutlinedBriefcase,
            self::Empresarial => Heroicon::OutlinedSquares2x2,
            self::Datos => Heroicon::OutlinedIdentification,
            self::Familia => Heroicon::OutlinedUserGroup,
            self::Consumo => Heroicon::OutlinedReceiptPercent,
        };
    }

    /** Emoji usado en el sitio público (más cálido que un ícono SVG en tarjetas). */
    public function emoji(): string
    {
        return match ($this) {
            self::Salud => '⚕️',
            self::Financiero => '💳',
            self::Transito => '🚗',
            self::Inmobiliario => '🏠',
            self::Laboral => '💼',
            self::Empresarial => '🏢',
            self::Datos => '🔒',
            self::Familia => '👨‍👩‍👧',
            self::Consumo => '🧾',
        };
    }

    public function slug(): string
    {
        return $this->value;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])
            ->all();
    }
}
