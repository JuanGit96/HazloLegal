<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Services\SubscriptionPromoService;
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
    public function __construct(
        private readonly SeoManager $seo,
        private readonly SubscriptionPromoService $promos,
    ) {}

    /**
     * El enlace de invitación.
     *
     * Guarda el código en la sesión y sigue a la landing de siempre: así el
     * abogado ve la oferta en las tarjetas de plan antes de decidir, y el
     * código viaja con el formulario de registro para que siga valiendo
     * cuando pague —que es semanas después, tras verificar su tarjeta
     * profesional.
     *
     * Un código que ya no sirve no manda a un error: la landing se abre con
     * la oferta de lanzamiento, que es mejor que una puerta cerrada.
     */
    public function invitation(string $code, Request $request): RedirectResponse
    {
        $promo = $this->promos->byCode($code);

        if ($promo === null) {
            return redirect()->route('lawyers.join')->with(
                'paymentError',
                'Ese enlace de invitación ya no está disponible, pero el precio de lanzamiento sigue en pie.',
            );
        }

        $request->session()->put('invitation_code', $promo->code);

        return redirect()->route('lawyers.join')->with('status', $promo->tagline ?: $promo->name);
    }

    public function index(Request $request): View
    {
        $code = (string) $request->session()->get('invitation_code', '');
        $plans = LawyerPlan::query()->active()->get();

        // La condición se resuelve plan a plan: una promoción puede estar
        // atada a uno solo, y pintar «3 meses gratis» sobre un plan que no
        // la tiene sería prometer lo que no se va a cumplir.
        $offers = $plans->mapWithKeys(fn (LawyerPlan $plan) => [
            $plan->id => $this->promos->quote(
                $plan,
                BillingPeriod::Monthly,
                $this->promos->resolve($plan, $code ?: null),
            ),
        ]);

        return view('lawyers.join', [
            'meta' => $this->seo->page(
                title: 'Consigue más clientes como abogado en Colombia | HazloLegal para abogados',
                description: 'Miles de personas generan documentos legales en HazloLegal cada mes. Aparece en el directorio, en los instructivos que circulan y recibe los casos de tu especialidad. Precio de lanzamiento, sin permanencia.',
                canonical: route('lawyers.join'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Para abogados', 'url' => route('lawyers.join')],
                ],
            ),
            'plans' => $plans,
            'offers' => $offers,
            'invitationCode' => $code ?: null,
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
            'invitation_code' => ['nullable', 'string', 'max:40'],
            'accepts_terms' => ['accepted'],
        ], [
            'nit.required_if' => 'Necesitamos el NIT para verificar que la firma existe.',
            'contact_name.required_if' => 'Dinos con quién hablamos en la firma.',
        ]);

        // Entra inactivo y sin publicar: la tarjeta profesional se verifica a
        // mano antes de que el perfil salga al directorio. Publicar sin
        // verificar sería prestar la marca a cualquiera.
        // El código se vuelve a validar aquí: entre que se cargó la landing
        // y se envió el formulario la promoción puede haberse agotado, y
        // guardar uno muerto solo aplazaría la mala noticia.
        $invitacion = filled($data['invitation_code'] ?? null)
            ? $this->promos->byCode((string) $data['invitation_code'])?->code
            : null;

        Lawyer::query()->create([
            ...collect($data)->except(['accepts_terms', 'lawyer_plan_id', 'invitation_code'])->all(),
            'invitation_code' => $invitacion,
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
