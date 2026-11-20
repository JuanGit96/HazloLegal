<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Subscriptions\Models\LawyerPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * La landing que capta abogados.
 *
 * El argumento no es «aparece en un directorio» —eso lo ofrece cualquiera—
 * sino «te ponemos delante de gente que acaba de escribir su problema».
 * Cada beneficio que se promete aquí tiene que existir en el panel del
 * abogado, o la suscripción se cancela al segundo mes.
 */
class LawyerLandingController
{
    public function __construct(private readonly SeoManager $seo) {}

    public function index(): View
    {
        return view('lawyers.join', [
            'meta' => $this->seo->page(
                title: 'Consigue más clientes como abogado en Colombia | HazloLegal para abogados',
                description: 'Miles de personas generan documentos legales en HazloLegal cada mes. Aparece en el directorio, en los instructivos que circulan y recibe los casos de tu especialidad. Dos meses gratis.',
                canonical: route('lawyers.join'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Para abogados', 'url' => route('lawyers.join')],
                ],
            ),
            'plans' => LawyerPlan::query()->active()->get(),
            'areas' => PracticeArea::cases(),
            'kinds' => \App\Domain\Lawyers\Enums\LawyerKind::cases(),
            'stats' => [
                'documents' => (int) \App\Domain\Orders\Models\Order::query()->count(),
                'lawyers' => Lawyer::query()->published()->count(),
                'areas' => count(PracticeArea::cases()),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:5', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160', 'unique:lawyers,email'],
            'phone' => ['required', 'string', 'min:7', 'max:30'],
            'city' => ['required', 'string', 'max:80'],
            'kind' => ['required', 'in:individual,firm'],
            'professional_card' => ['required', 'string', 'max:40'],
            'nit' => ['nullable', 'required_if:kind,firm', 'string', 'max:30'],
            'contact_name' => ['nullable', 'required_if:kind,firm', 'string', 'max:120'],
            'team_size' => ['nullable', 'integer', 'min:1', 'max:999'],
            'practice_areas' => ['required', 'array', 'min:1'],
            'practice_areas.*' => ['string'],
            'firm_name' => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:160'],
            'lawyer_plan_id' => ['required', 'exists:lawyer_plans,id'],
            'accepts_terms' => ['accepted'],
        ], [
            'nit.required_if' => 'Necesitamos el NIT para verificar que la firma existe.',
            'contact_name.required_if' => 'Dinos con quién hablamos en la firma.',
        ]);

        // Entra inactivo y sin publicar: la tarjeta profesional se verifica a
        // mano antes de que el perfil salga al directorio. Publicar sin
        // verificar sería prestar la marca a cualquiera.
        Lawyer::query()->create([
            ...collect($data)->except(['accepts_terms', 'lawyer_plan_id'])->all(),
            'status' => \App\Domain\Lawyers\Enums\LawyerStatus::Paused,
            'is_published' => false,
            'accepts_cases' => false,
        ]);

        return back()->with(
            'status',
            $data['kind'] === 'firm'
                ? 'Recibimos la solicitud de tu firma. Verificamos el NIT y la tarjeta del representante, y te escribimos en menos de 24 horas hábiles.'
                : 'Recibimos tu solicitud. Verificamos tu tarjeta profesional y te escribimos en menos de 24 horas hábiles para activar tu perfil.',
        );
    }
}
