<?php

declare(strict_types=1);

namespace App\Domain\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case InReview = 'in_review';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::AwaitingPayment => 'Esperando pago',
            self::Paid => 'Pago confirmado',
            self::InReview => 'En redacción / auditoría',
            self::Delivered => 'Entregado',
            self::Cancelled => 'Anulado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::AwaitingPayment => 'warning',
            self::Paid => 'info',
            self::InReview => 'primary',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::AwaitingPayment => Heroicon::OutlinedClock,
            self::Paid => Heroicon::OutlinedBanknotes,
            self::InReview => Heroicon::OutlinedScale,
            self::Delivered => Heroicon::OutlinedCheckBadge,
            self::Cancelled => Heroicon::OutlinedFlag,
        };
    }

    /** Estados que cuentan como venta cerrada para los indicadores. */
    public static function revenueStates(): array
    {
        return [self::Paid, self::InReview, self::Delivered];
    }

    /** Estados que el equipo debe atender hoy. */
    public static function openStates(): array
    {
        return [self::Paid, self::InReview];
    }
}
