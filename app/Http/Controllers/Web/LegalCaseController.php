<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Content\Models\FaqEntry;
use App\Domain\Lawyers\Services\LawyerDirectory;
use App\Domain\LegalCases\Enums\BudgetRange;
use App\Domain\LegalCases\Enums\CaseUrgency;
use App\Domain\LegalCases\Data\CaseIntakeData;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\LegalCases\Services\CaseAssignmentService;
use App\Domain\LegalCases\Services\CaseIntakeService;
use App\Domain\Seo\Services\SeoManager;
use App\Http\Requests\StoreCaseRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegalCaseController
{
    public function __construct(
        private readonly CaseIntakeService $intake,
        private readonly CaseAssignmentService $assignment,
        private readonly SeoManager $seo,
    ) {}

    public function create(LawyerDirectory $directory, Request $request): View
    {
        $faqs = FaqEntry::query()->inGroup('cases')->get();

        $meta = $this->seo->withFaqSchema(
            $this->seo->page(
                title: 'Abogado para un caso complejo en Colombia | Cotización sin costo',
                description: 'Demandas, sucesiones, embargos y conflictos laborales. Cuéntanos tu caso y un abogado de la red lo toma en menos de 48 horas. La cotización no cuesta nada.',
                canonical: route('cases.create'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Caso complejo', 'url' => route('cases.create')],
                ],
            ),
            $faqs->map(fn (FaqEntry $f) => ['question' => $f->question, 'answer' => $f->answer])->all(),
        );

        return view('cases.create', [
            'meta' => $meta,
            'areas' => PracticeArea::cases(),
            'urgencies' => CaseUrgency::cases(),
            'budgets' => BudgetRange::cases(),
            'team' => $directory->publicTeam(4),
            'lawyerCount' => $directory->publicTeam(100)->count(),
            'faqs' => $faqs,
            'preselectedArea' => PracticeArea::tryFrom((string) $request->query('area')),
        ]);
    }

    public function store(StoreCaseRequest $request): RedirectResponse
    {
        $case = $this->intake->register(CaseIntakeData::fromArray([
            ...$request->validated(),
            ...$request->only(['utm_source', 'utm_campaign']),
        ]));

        return redirect()->route('cases.received', $case->reference);
    }

    public function received(LegalCase $case): View
    {
        return view('cases.received', [
            'meta' => $this->seo->page(
                title: "Caso {$case->reference} recibido | Hazlo Legal",
                description: 'Tu caso ya está en manos de nuestros abogados.',
                canonical: route('cases.received', $case->reference),
                noindex: true,
            ),
            'case' => $case,
            'ttlHours' => (int) config('hazlolegal.cases.offer_ttl_hours'),
        ]);
    }

    /**
     * El abogado llega aquí desde el botón del correo. El token es la
     * credencial, así que no pedimos login: cada segundo cuenta en la carrera.
     */
    public function respond(string $token, string $decision, Request $request): View
    {
        $offer = CaseOffer::query()
            ->with(['legalCase.assignedLawyer', 'lawyer'])
            ->where('token', $token)
            ->firstOrFail();

        $result = $decision === 'accept'
            ? $this->assignment->accept($offer, $request->ip())
            : $this->assignment->decline($offer);

        return view('cases.offer-result', [
            'meta' => $this->seo->page(
                title: 'Respuesta registrada | Hazlo Legal',
                description: 'Resultado de tu respuesta al caso.',
                canonical: url()->current(),
                noindex: true,
            ),
            'result' => $result,
            'offer' => $result->offer,
            'case' => $result->case->load('assignedLawyer'),
            'lawyer' => $offer->lawyer,
            'decision' => $decision,
        ]);
    }

    public function decline(string $token, Request $request): View
    {
        $validated = $request->validate([
            'decline_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $offer = CaseOffer::query()->with(['legalCase', 'lawyer'])->where('token', $token)->firstOrFail();

        $result = $this->assignment->decline($offer, $validated['decline_reason'] ?? null);

        return view('cases.offer-result', [
            'meta' => $this->seo->page(
                title: 'Respuesta registrada | Hazlo Legal',
                description: 'Resultado de tu respuesta al caso.',
                canonical: url()->current(),
                noindex: true,
            ),
            'result' => $result,
            'offer' => $result->offer,
            'case' => $result->case,
            'lawyer' => $offer->lawyer,
            'decision' => 'decline',
        ]);
    }
}
