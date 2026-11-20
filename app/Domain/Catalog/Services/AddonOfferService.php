<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Data\AddonOffer;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use Illuminate\Support\Collection;

/**
 * Decide qué adicionales ofrecer, en qué orden y con qué argumento.
 *
 * El orden no es estético, es comercial. Se ofrece primero lo barato y
 * concreto (el instructivo), después lo que resuelve una urgencia (express)
 * y al final lo caro (el abogado). Abrir con lo caro hace que el resto
 * parezca un gasto añadido; abrir con lo barato construye el hábito de ir
 * sumando, y cada «sí» hace más probable el siguiente.
 *
 * Nada de esto es un truco: cada adicional tiene que entregar lo que
 * promete, el precio se muestra siempre y quitar uno es tan fácil como
 * ponerlo. Un cliente engañado no vuelve, y aquí el negocio depende de que
 * vuelva y de que nos recomiende.
 */
final class AddonOfferService
{
    /**
     * @param  iterable<string>  $selectedKeys
     * @return Collection<int, AddonOffer>
     */
    public function offersFor(LegalDocument $document, iterable $selectedKeys = []): Collection
    {
        $selected = collect($selectedKeys)->filter()->map(fn ($k) => (string) $k);

        return $document->availableAddons
            // Un trámite que no se arma solo no tiene documento al que sumarle
            // instructivo, firma o entrega inmediata: lo único que aplica es
            // el trabajo del abogado.
            ->when(
                ! $document->allowsSelfService(),
                fn ($addons) => $addons->filter(
                    fn (ServiceAddon $addon) => in_array($addon->kind->value, ['review', 'session'], true)
                )
            )
            ->sortBy([
                fn (ServiceAddon $a, ServiceAddon $b) => $a->kind->offerOrder() <=> $b->kind->offerOrder(),
                fn (ServiceAddon $a, ServiceAddon $b) => $a->priceFor($document) <=> $b->priceFor($document),
            ])
            // Lo que se pinta como marcado es lo que de verdad se va a
            // cobrar: si pidió abogado y express, el express no va.
            ->pipe(function (Collection $addons) use ($document, $selected) {
                $cobrables = \App\Domain\Catalog\Services\PricingService::withoutIncompatible(
                    $addons->filter(fn (ServiceAddon $a) => $selected->contains($a->key))->values()
                )->pluck('key');

                return $addons->map(fn (ServiceAddon $addon) => tap($addon, function (ServiceAddon $a) use ($cobrables) {
                    $a->setAttribute('hl_selected', $cobrables->contains($a->key));
                }));
            })
            ->map(fn (ServiceAddon $addon) => new AddonOffer(
                addon: $addon,
                price: $addon->priceFor($document),
                compareAtPrice: $addon->compareAtPrice($document),
                isRecommended: (bool) $addon->pivot?->is_recommended,
                isPreselected: (bool) $addon->pivot?->is_preselected,
                isSelected: (bool) $addon->getAttribute('hl_selected'),
                reason: $this->reasonFor($addon, $document),
            ))
            ->values();
    }

    /**
     * El argumento concreto de por qué conviene ese adicional.
     *
     * Se prefiere el texto que el equipo haya escrito en el panel; el
     * respaldo por familia existe para que nunca salga una tarjeta muda.
     */
    private function reasonFor(ServiceAddon $addon, LegalDocument $document): ?string
    {
        if (filled($addon->tagline)) {
            return $addon->tagline;
        }

        return match ($addon->kind->value) {
            'guidance' => 'Te decimos exactamente dónde radicarlo, qué anexar y qué plazo tiene la entidad.',
            'delivery' => "Sin esto lo recibes en {$document->deliveryLabel()}. Con esto, de una.",
            'review' => 'Un abogado lee tu caso y escribe el documento para ti, no sobre un formato.',
            'signature' => 'Firmas desde el celular y el documento queda listo para enviar.',
            'session' => 'Resuelves tus dudas con un abogado antes de radicar.',
            default => null,
        };
    }

    /**
     * El siguiente adicional a proponer tras una elección.
     *
     * Se propone de uno en uno: una lista de seis casillas abruma y la
     * respuesta por defecto pasa a ser «ninguna». Uno solo, con su motivo,
     * es una decisión que se puede tomar.
     */
    public function nextBestOffer(LegalDocument $document, iterable $selectedKeys = []): ?AddonOffer
    {
        return $this->offersFor($document, $selectedKeys)
            ->reject(fn (AddonOffer $offer) => $offer->isSelected)
            ->sortByDesc(fn (AddonOffer $offer) => $offer->isRecommended ? 1 : 0)
            ->first();
    }

    /** Los que llegan marcados por defecto en un trámite. */
    public function preselectedKeys(LegalDocument $document): array
    {
        return $document->availableAddons
            ->filter(fn (ServiceAddon $addon) => (bool) $addon->pivot?->is_preselected)
            ->pluck('key')
            ->all();
    }

    /**
     * Lo que el cliente se está perdiendo si no añade nada.
     *
     * Enmarcar en pérdida mueve más que enmarcar en ganancia, pero solo es
     * legítimo si lo que se enumera es cierto y ya estaba dicho en la ficha.
     */
    public function missingOut(LegalDocument $document, iterable $selectedKeys = []): Collection
    {
        return $this->offersFor($document, $selectedKeys)
            ->reject(fn (AddonOffer $offer) => $offer->isSelected)
            ->filter(fn (AddonOffer $offer) => $offer->isRecommended)
            ->values();
    }
}
