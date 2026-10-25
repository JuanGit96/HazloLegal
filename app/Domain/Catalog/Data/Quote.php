<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
use Carbon\CarbonImmutable;

/** Precio y promesa de entrega resueltos para una combinación concreta. */
final readonly class Quote
{
    public function __construct(
        public LegalDocument $document,
        public ServicePlan $plan,
        public DeliverySpeed $speed,
        public ?int $amount,
        public ?int $deliveryHours,
        public bool $isQuoteOnly,
        public array $includes = [],
    ) {}

    public function formattedAmount(): string
    {
        return $this->amount === null
            ? 'Cotización sin costo'
            : '$'.number_format($this->amount, 0, ',', '.');
    }

    public function promisedAt(): ?CarbonImmutable
    {
        return $this->deliveryHours ? CarbonImmutable::now()->addHours($this->deliveryHours) : null;
    }

    public function deliveryLabel(): string
    {
        return match (true) {
            $this->deliveryHours === null => 'Según el alcance del caso',
            $this->deliveryHours <= 4 => 'Menos de 4 horas',
            $this->deliveryHours <= 24 => '24 horas hábiles',
            default => "{$this->deliveryHours} horas",
        };
    }
}
