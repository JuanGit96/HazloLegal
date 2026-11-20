<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/** Familias de adicionales. Define cómo se ordenan y se ofrecen al cliente. */
enum AddonKind: string implements HasColor, HasIcon, HasLabel
{
    case Delivery = 'delivery';
    case Guidance = 'guidance';
    case Review = 'review';
    case Signature = 'signature';
    case Session = 'session';

    public function getLabel(): string
    {
        return match ($this) {
            self::Delivery => 'Velocidad de entrega',
            self::Guidance => 'Guía y acompañamiento',
            self::Review => 'Revisión de abogado',
            self::Signature => 'Firma',
            self::Session => 'Sesión con abogado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Delivery => 'warning',
            self::Guidance => 'info',
            self::Review => 'primary',
            self::Signature => 'success',
            self::Session => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Delivery => Heroicon::OutlinedBolt,
            self::Guidance => Heroicon::OutlinedBookOpen,
            self::Review => Heroicon::OutlinedScale,
            self::Signature => Heroicon::OutlinedPencilSquare,
            self::Session => Heroicon::OutlinedChatBubbleLeftRight,
        };
    }

    /**
     * Orden en que se le muestran al cliente.
     *
     * Primero lo barato y concreto (instructivo), luego la urgencia
     * (express), y al final lo caro (abogado). Empezar por lo caro hace que
     * todo lo demás parezca un gasto más; empezar por lo barato construye
     * el hábito de ir sumando.
     */
    public function offerOrder(): int
    {
        return match ($this) {
            self::Guidance => 1,
            self::Signature => 2,
            self::Delivery => 3,
            self::Review => 4,
            self::Session => 5,
        };
    }
}
