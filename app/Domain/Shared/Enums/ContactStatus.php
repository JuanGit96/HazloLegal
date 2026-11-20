<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContactStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Sin atender',
            self::InProgress => 'En trámite',
            self::Resolved => 'Resuelta',
            self::Rejected => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'danger',
            self::InProgress => 'warning',
            self::Resolved => 'success',
            self::Rejected => 'gray',
        };
    }

    public static function openStates(): array
    {
        return [self::New, self::InProgress];
    }
}
