<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Data\Quote;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use App\Domain\Orders\Enums\OrderTier;
use Illuminate\Support\Collection;

/**
 * Un solo lugar decide cuánto cuesta algo.
 *
 * El modelo es «base + adicionales»: el trámite tiene un precio de partida
 * (que puede ser cero) y cada adicional suma. El precio de un adicional
 * puede variar por trámite, porque acompañar una tutela no cuesta lo mismo
 * que acompañar un acuerdo de confidencialidad.
 */
final class PricingService
{
    /** @param iterable<int|string> $addonKeys Claves o ids de los adicionales elegidos */
    public function quote(LegalDocument $document, iterable $addonKeys = []): Quote
    {
        $document->loadMissing('availableAddons');

        $selected = $this->resolveAddons($document, $addonKeys);

        $base = (int) $document->base_price;
        $addonsAmount = $selected->sum(fn (ServiceAddon $addon) => $addon->priceFor($document));

        // El precio tachado solo existe si algo lo justifica: o el trámite
        // tiene descuento configurado, o alguno de los adicionales lo tiene.
        $compareAtBase = $document->compareAtPrice() ?? $base;
        $compareAtAddons = $selected->sum(
            fn (ServiceAddon $addon) => $addon->compareAtPrice($document) ?? $addon->priceFor($document)
        );
        $compareAtTotal = $compareAtBase + $compareAtAddons;

        $total = $base + $addonsAmount;
        $discount = max(0, $compareAtTotal - $total);

        return new Quote(
            document: $document,
            addons: $selected,
            baseAmount: $base,
            addonsAmount: $addonsAmount,
            discountAmount: $discount,
            total: $total,
            compareAtTotal: $discount > 0 ? $compareAtTotal : null,
            deliveryHours: $this->deliveryHours($document, $selected),
            tier: $this->tier($total, $selected),
        );
    }

    /** @return Collection<int, ServiceAddon> */
    private function resolveAddons(LegalDocument $document, iterable $keys): Collection
    {
        $wanted = collect($keys)->filter()->map(fn ($k) => (string) $k)->unique();

        if ($wanted->isEmpty()) {
            return collect();
        }

        $selected = $document->availableAddons
            ->filter(fn (ServiceAddon $addon) => $wanted->contains($addon->key)
                || $wanted->contains((string) $addon->id))
            ->sortBy(fn (ServiceAddon $addon) => $addon->kind->offerOrder())
            ->values();

        return self::withoutIncompatible($selected);
    }

    /**
     * Parejas de familias que no pueden convivir en un mismo pedido.
     *
     * Si un abogado escribe el documento sobre los hechos del cliente, no
     * puede estar listo «ahora mismo»: cobrar por entrega inmediata encima
     * sería cobrar por algo que no va a pasar.
     *
     * El primero de cada pareja es el que manda cuando llegan los dos, y es
     * el abogado, porque es lo que el cliente valora más y lo que cuesta
     * más.
     *
     * @var list<array{string, string}>
     */
    private const INCOMPATIBLES = [
        ['review', 'delivery'],
    ];

    /**
     * Quita las combinaciones que no se pueden cumplir.
     *
     * @param  Collection<int, ServiceAddon>  $selected
     * @return Collection<int, ServiceAddon>
     */
    public static function withoutIncompatible(Collection $selected): Collection
    {
        foreach (self::INCOMPATIBLES as [$manda, $cede]) {
            if ($selected->contains(fn (ServiceAddon $a) => $a->kind->value === $manda)) {
                $selected = $selected->reject(fn (ServiceAddon $a) => $a->kind->value === $cede);
            }
        }

        return $selected->values();
    }

    /**
     * Las familias que ya no se pueden ofrecer, dado lo que el pedido lleva.
     *
     * A diferencia de `withoutIncompatible`, aquí la relación es simétrica:
     * para cobrar manda el abogado, pero para *ofrecer* da igual cuál llegó
     * primero. Quien ya pagó entrega inmediata no debe ver «lo escribe un
     * abogado» —tendría que renunciar a la prisa que acaba de pagar— ni al
     * contrario. Ofrecer algo que vamos a tener que anular después no es una
     * venta, es una queja.
     *
     * @param  Collection<int, ServiceAddon>  $selected
     * @return list<string>
     */
    public static function blockedKinds(Collection $selected): array
    {
        $puestas = $selected->map(fn (ServiceAddon $a) => $a->kind->value)->all();
        $bloqueadas = [];

        foreach (self::INCOMPATIBLES as $pareja) {
            foreach ($pareja as $familia) {
                if (in_array($familia, $puestas, true)) {
                    $bloqueadas = [...$bloqueadas, ...array_diff($pareja, [$familia])];
                }
            }
        }

        return array_values(array_unique($bloqueadas));
    }

    /**
     * El express manda sobre el plazo; si no se contrata, rige el del trámite.
     *
     * Con acompañamiento el express ya se descartó antes de llegar aquí, así
     * que el plazo es el del trámite.
     */
    private function deliveryHours(LegalDocument $document, Collection $addons): int
    {
        return $addons->contains('key', 'express')
            ? (int) $document->express_delivery_hours
            : (int) $document->delivery_hours;
    }

    private function tier(int $total, Collection $addons): OrderTier
    {
        if ($addons->contains(fn (ServiceAddon $a) => $a->kind->value === 'review')) {
            return OrderTier::Accompanied;
        }

        return $total === 0 ? OrderTier::Free : OrderTier::SelfService;
    }

    /** El «desde $X» que se anuncia en el catálogo. */
    public function entryPrice(): int
    {
        return (int) LegalDocument::query()->active()->min('base_price');
    }

    public function formatCop(?int $amount): string
    {
        return match (true) {
            $amount === null => 'Cotizar',
            $amount === 0 => 'Gratis',
            default => Quote::cop($amount),
        };
    }
}
