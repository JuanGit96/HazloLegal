<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Support\Collection;

/**
 * Elige qué abogados aparecen en un instructivo PDF.
 *
 * Es inventario publicitario real: el PDF se descarga, se imprime, se manda
 * por WhatsApp y se radica en una entidad. Que tu firma aparezca ahí es
 * justamente lo que se le vende al abogado en la suscripción.
 *
 * El reparto es rotatorio entre quienes pagan el beneficio, para que no se
 * lo lleve siempre el mismo y la suscripción valga lo que promete.
 */
final class LawyerPlacementService
{
    /** @return Collection<int, Lawyer> */
    public function forArea(PracticeArea $area, int $limit = 2): Collection
    {
        $eligible = Lawyer::query()
            ->published()
            ->whereHas('subscription', fn ($q) => $q->active())
            ->with('subscription.plan')
            ->get()
            ->filter(fn (Lawyer $l) => $l->can('includes_pdf_placement'));

        $specialists = $eligible
            ->filter(fn (Lawyer $l) => in_array($area->value, $l->practice_areas ?? [], true));

        $pool = $specialists->isNotEmpty() ? $specialists : $eligible;

        // Rotación simple: el que lleva más tiempo sin salir va primero.
        return $pool
            ->sortBy(fn (Lawyer $l) => $l->last_placement_at ?? '1970-01-01')
            ->take($limit)
            ->values();
    }

    /** El abogado principal que firma el instructivo. */
    public function primaryForArea(PracticeArea $area): ?Lawyer
    {
        return $this->forArea($area, 1)->first();
    }
}
