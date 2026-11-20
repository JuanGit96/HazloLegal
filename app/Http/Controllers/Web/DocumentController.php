<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Services\AddonOfferService;
use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Settings\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentController
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly PricingService $pricing,
        private readonly AddonOfferService $offers,
        private readonly SeoManager $seo,
        private readonly SettingsService $settings,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $documents = $this->catalog->documents()
            ->when($search !== '', fn ($docs) => $docs->filter(
                fn (LegalDocument $doc) => str_contains(
                    mb_strtolower($doc->name.' '.$doc->summary.' '.implode(' ', $doc->seo_keywords ?? [])),
                    mb_strtolower($search),
                )
            ))->values();

        return view('documents.index', [
            'meta' => $this->seo->catalog($documents),
            'documents' => $documents,
            'grouped' => $this->catalog->groupedByArea(),
            // Solo las áreas con documentos: enlazar a una vacía daría 404.
            'areas' => $this->catalog->documents()
                ->map(fn (LegalDocument $doc) => $doc->practice_area)
                ->unique()
                ->sortBy(fn (PracticeArea $area) => $area->getLabel())
                ->values(),
            'freeDocuments' => $this->catalog->freeDocuments(),
            'search' => $search,
        ]);
    }

    /** Landing por área de práctica: captura búsquedas de cola media. */
    public function area(string $area): View
    {
        $practiceArea = PracticeArea::tryFrom($area)
            ?? throw new NotFoundHttpException("Área «{$area}» no existe.");

        $documents = LegalDocument::query()->active()->inArea($practiceArea)->ordered()->get();

        if ($documents->isEmpty()) {
            throw new NotFoundHttpException('Área sin documentos publicados.');
        }

        $meta = $this->seo->page(
            title: "{$practiceArea->getLabel()}: trámites y documentos legales | HazloLegal",
            description: "Documentos de {$practiceArea->getLabel()} listos en minutos. Algunos gratis, todos escritos por abogados colombianos.",
            canonical: route('documents.area', $practiceArea->value),
            crumbs: [
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Catálogo', 'url' => route('documents.index')],
                ['name' => $practiceArea->getLabel(), 'url' => route('documents.area', $practiceArea->value)],
            ],
        );

        return view('documents.area', [
            'meta' => $meta,
            'area' => $practiceArea,
            'documents' => $documents,
            'lawyers' => $this->catalog->directoryLawyersFor($practiceArea),
        ]);
    }

    public function show(LegalDocument $document): View
    {
        abort_unless($document->is_active, 404);

        // La cotización de la ficha es la del trámite «pelado»: lo que hay
        // que pagar para empezar. Los adicionales se muestran aparte, con su
        // precio, para que el número grande no mienta.
        $quote = $this->pricing->quote($document);

        $this->catalog->registerView($document);

        return view('documents.show', [
            'meta' => $this->seo->document($document, $quote),
            'document' => $document,
            'quote' => $quote,
            'offers' => $this->offers->offersFor($document),
            'showDiscounts' => $this->settings->bool('promo_enabled', true),
            'related' => $this->catalog->relatedDocuments($document),
            'testimonials' => $document->testimonials()->limit(3)->get(),
            'posts' => $document->posts()->published()->recent()->limit(3)->get(),
        ]);
    }
}
