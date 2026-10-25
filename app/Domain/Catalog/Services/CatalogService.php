<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
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

    public function plans(): Collection
    {
        return ServicePlan::query()->active()->get();
    }

    public function plan(string $key): ?ServicePlan
    {
        return ServicePlan::query()->active()->where('key', $key)->first();
    }

    /** Los planes que se muestran en la grilla de precios del home. */
    public function publicPlans(): Collection
    {
        return $this->plans();
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
