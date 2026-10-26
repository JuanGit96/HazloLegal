<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Seo\Services\SeoManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentController
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly PricingService $pricing,
        private readonly SeoManager $seo,
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
            'plans' => $this->catalog->plans(),
            'pricing' => $this->pricing,
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
            description: "Documentos de {$practiceArea->getLabel()} redactados por abogados colombianos. Precio fijo y entrega desde 4 horas.",
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
            'plans' => $this->catalog->plans(),
            'pricing' => $this->pricing,
        ]);
    }

    public function show(LegalDocument $document): View
    {
        abort_unless($document->is_active, 404);

        $documentPlan = $this->catalog->plan('document');
        $advisoryPlan = $this->catalog->plan('advisory');

        $standardQuote = $this->pricing->quote($document, $documentPlan);
        $expressQuote = $document->available_express
            ? $this->pricing->quote($document, $documentPlan, \App\Domain\Catalog\Enums\DeliverySpeed::Express)
            : null;

        $this->catalog->registerView($document);

        return view('documents.show', [
            'meta' => $this->seo->document($document, $standardQuote),
            'document' => $document,
            'standardQuote' => $standardQuote,
            'expressQuote' => $expressQuote,
            'advisoryQuote' => $advisoryPlan ? $this->pricing->quote($document, $advisoryPlan) : null,
            'documentPlan' => $documentPlan,
            'advisoryPlan' => $advisoryPlan,
            'related' => $this->catalog->relatedDocuments($document),
            'testimonials' => $document->testimonials()->limit(3)->get(),
            'posts' => $document->posts()->published()->recent()->limit(3)->get(),
            'pricing' => $this->pricing,
        ]);
    }
}
