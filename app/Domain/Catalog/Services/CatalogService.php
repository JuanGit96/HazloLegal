<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Support\Collection;

/** Única puerta de entrada al catálogo: controladores y Filament pasan por aquí. */
final class CatalogService
{
    public function documents(): Collection
    {
        return LegalDocument::query()->active()->ordered()->get();
    }

    public function featuredDocuments(int $limit = 6): Collection
    {
        return LegalDocument::query()->active()->featured()->ordered()->limit($limit)->get();
    }

    public function findBySlug(string $slug): ?LegalDocument
    {
        return LegalDocument::query()->active()->where('slug', $slug)->first();
    }

    /** Catálogo agrupado por área, como se muestra en el selector del checkout. */
    public function groupedByArea(): Collection
    {
        return $this->documents()
            ->groupBy(fn (LegalDocument $doc) => $doc->practice_area->value)
            ->map(fn (Collection $docs, string $area) => [
                'area' => PracticeArea::from($area),
                'documents' => $docs,
            ])
            ->sortBy(fn (array $group) => $group['area']->getLabel())
            ->values();
    }

    /**
     * Los trámites que no cuestan nada.
     *
     * Son la puerta de entrada del negocio: alguien llega por el derecho de
     * petición gratis, deja sus datos con autorización de tratamiento y, si
     * le sirve, vuelve a pagar por lo siguiente.
     */
    public function freeDocuments(int $limit = 6): Collection
    {
        return LegalDocument::query()
            ->active()
            ->where('is_free', true)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /** Trámites que sí se pueden pedir solo, sin abogado. */
    public function selfServiceDocuments(): Collection
    {
        return $this->documents()->filter(fn (LegalDocument $doc) => $doc->allowsSelfService())->values();
    }

    /** Abogados publicados del directorio para un área, ya priorizados por plan. */
    public function directoryLawyersFor(PracticeArea $area, int $limit = 3): Collection
    {
        return Lawyer::query()
            ->where('is_published', true)
            ->whereJsonContains('practice_areas', $area->value)
            ->orderByDesc('is_featured')
            ->orderBy('last_placement_at')
            ->limit($limit)
            ->get();
    }

    public function relatedDocuments(LegalDocument $document, int $limit = 3): Collection
    {
        return LegalDocument::query()
            ->active()
            ->whereKeyNot($document->getKey())
            ->where('practice_area', $document->practice_area)
            ->ordered()
            ->limit($limit)
            ->get()
            ->whenEmpty(fn () => LegalDocument::query()
                ->active()
                ->whereKeyNot($document->getKey())
                ->featured()
                ->ordered()
                ->limit($limit)
                ->get());
    }

    public function registerView(LegalDocument $document): void
    {
        $document->incrementQuietly('views_count');
    }
}
