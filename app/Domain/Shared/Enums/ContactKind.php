<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ContactKind: string implements HasColor, HasIcon, HasLabel
{
    case Contact = 'contact';
    case DataDeletion = 'data_deletion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contact => 'Mensaje de contacto',
            self::DataDeletion => 'Eliminación de datos',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Contact => 'info',
            self::DataDeletion => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Contact => Heroicon::OutlinedChatBubbleLeftRight,
            self::DataDeletion => Heroicon::OutlinedTrash,
        };
    }

    /** Días hábiles para responder, según la norma o nuestra promesa. */
    public function responseDays(): int
    {
        // La Ley 1581 de 2012 da 15 días hábiles para los reclamos sobre
        // datos personales. El contacto comercial lo prometemos a 1 día.
        return $this === self::DataDeletion ? 15 : 1;
    }
}
