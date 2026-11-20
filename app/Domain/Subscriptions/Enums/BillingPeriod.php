<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Enums;

use Filament\Support\Contracts\HasLabel;

enum BillingPeriod: string implements HasLabel
{
    case Monthly = 'monthly';
    case Annual = 'annual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Monthly => 'Mensual',
            self::Annual => 'Anual',
        };
    }

    public function months(): int
    {
        return $this === self::Annual ? 12 : 1;
    }
}
