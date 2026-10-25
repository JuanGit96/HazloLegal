<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CaseStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Dispatched = 'dispatched';
    case Assigned = 'assigned';
    case Closed = 'closed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Recibido',
            self::Dispatched => 'Enviado a abogados',
            self::Assigned => 'Abogado asignado',
            self::Closed => 'Cerrado',
            self::Expired => 'Sin respuesta',
            self::Cancelled => 'Anulado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Dispatched => 'warning',
            self::Assigned => 'success',
            self::Closed => 'info',
            self::Expired => 'danger',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::New => Heroicon::OutlinedInbox,
            self::Dispatched => Heroicon::OutlinedMegaphone,
            self::Assigned => Heroicon::OutlinedCheckBadge,
            self::Closed => Heroicon::OutlinedTrophy,
            self::Expired => Heroicon::OutlinedClock,
            self::Cancelled => Heroicon::OutlinedFlag,
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::Dispatched], true);
    }
}
