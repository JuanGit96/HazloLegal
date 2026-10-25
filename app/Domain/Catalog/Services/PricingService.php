<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Data\Quote;
use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;

/**
 * Un solo lugar decide cuánto cuesta algo. Si mañana entran cupones,
 * recargos por complejidad o IVA, se tocan aquí y en ningún otro sitio.
 */
final class PricingService
{
    public function quote(LegalDocument $document, ServicePlan $plan, DeliverySpeed $speed = DeliverySpeed::Standard): Quote
    {
        if (! $document->allowsSpeed($speed)) {
            $speed = DeliverySpeed::Standard;
        }

        $base = $plan->priceFor($speed);
        $amount = $base === null ? null : max(0, $base + (int) $document->price_modifier);

        return new Quote(
            document: $document,
            plan: $plan,
            speed: $speed,
            amount: $amount,
            deliveryHours: $plan->deliveryHoursFor($speed),
            isQuoteOnly: $plan->is_quote_only,
            includes: $plan->includesFor($speed),
        );
    }

    /** Precio de entrada que se anuncia en el home ("desde $X"). */
    public function entryPrice(): int
    {
        $plan = ServicePlan::query()->active()->whereNotNull('price')->orderBy('price')->first();

        return (int) ($plan?->price ?? 0);
    }

    public function formatCop(?int $amount): string
    {
        return $amount === null ? 'Cotizar' : '$'.number_format($amount, 0, ',', '.');
    }

    /** Cuánto cuesta subir de estándar a express, para mostrar el upsell. */
    public function expressUpgradeCost(ServicePlan $plan): ?int
    {
        if ($plan->price === null || $plan->express_price === null) {
            return null;
        }

        return $plan->express_price - $plan->price;
    }
}
