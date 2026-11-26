<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Seo\Services\SeoManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * El directorio público.
 *
 * Para el ciudadano es una forma de encontrar abogado; para el negocio es
 * el inventario que justifica la suscripción. Por eso el orden lo decide el
 * plan contratado y no un criterio oculto: los destacados van marcados como
 * tales, que es lo honesto y además lo que exige la ley de publicidad.
 */
class LawyerDirectoryController
{
    public function __construct(
        private readonly SeoManager $seo,
        private readonly MetricRecorder $metrics,
    ) {}

    public function index(Request $request): View
    {
        $area = PracticeArea::tryFrom((string) $request->query('area'));
        $city = trim((string) $request->query('ciudad'));

        $lawyers = Lawyer::query()
            ->published()
            ->when($area, fn ($q) => $q->inArea($area))
            ->when($city !== '', fn ($q) => $q->where('city', 'like', "%{$city}%"))
            ->directoryOrder()
            ->paginate(12)
            ->withQueryString();

        // Salir en un listado también es visibilidad, y es la que explica
        // por qué unas fichas se abren y otras no.
        $this->metrics->recordMany($lawyers->getCollection(), MetricKind::DirectoryImpression);

        $title = $area
            ? "{$area->getLabel()}: abogados en Colombia"
            : 'Directorio de abogados verificados en Colombia';

        return view('lawyers.index', [
            'meta' => $this->seo->page(
                title: $title.' | HazloLegal',
                description: 'Encuentra abogados por especialidad y ciudad. Perfiles con experiencia, áreas de práctica y contacto directo. Sin comisiones.',
                canonical: route('lawyers.index'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Abogados', 'url' => route('lawyers.index')],
                ],
            ),
            'lawyers' => $lawyers,
            'areas' => PracticeArea::cases(),
            'selectedArea' => $area,
            'city' => $city,
            'cities' => Lawyer::query()->published()->distinct()->orderBy('city')->pluck('city')->filter()->values(),
        ]);
    }

    public function show(Lawyer $lawyer): View
    {
        abort_unless($lawyer->is_published && $lawyer->status->canReceiveCases(), 404);

        $lawyer->incrementQuietly('profile_views');

        // Además del acumulado: el agregado del día es lo que luego dibuja
        // la gráfica del portal, y un acumulado no dibuja nada.
        $this->metrics->record($lawyer, MetricKind::ProfileView);

        return view('lawyers.show', [
            'meta' => $this->seo->page(
                title: $lawyer->seo_title ?: "{$lawyer->displayName()} · {$lawyer->headline} en {$lawyer->city}",
                description: $lawyer->seo_description ?: Str::limit((string) $lawyer->bio, 155),
                canonical: $lawyer->profileUrl(),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Abogados', 'url' => route('lawyers.index')],
                    ['name' => $lawyer->displayName(), 'url' => $lawyer->profileUrl()],
                ],
            ),
            'lawyer' => $lawyer,
            'related' => Lawyer::query()
                ->published()
                ->whereKeyNot($lawyer->id)
                ->where('city', $lawyer->city)
                ->directoryOrder()
                ->limit(3)
                ->get(),
            'posts' => $lawyer->posts()->published()->recent()->limit(3)->get(),
        ]);
    }

    /**
     * El clic para contactar, contado de paso.
     *
     * Es un salto por el servidor y no un evento de JavaScript: así
     * funciona con el JS desactivado, con bloqueadores puestos y para quien
     * rechazó las cookies, que es la mayoría. Lo que se guarda es un número
     * del día, no quién pulsó.
     */
    public function contact(Lawyer $lawyer, Request $request): RedirectResponse
    {
        abort_unless($lawyer->is_published && $lawyer->status->canReceiveCases(), 404);

        $lawyer->incrementQuietly('contact_clicks');
        $this->metrics->record($lawyer, MetricKind::ContactClick);

        $via = (string) $request->query('via', 'whatsapp');

        $whatsapp = preg_replace('/\D+/', '', (string) $lawyer->whatsapp);

        return redirect()->away(match (true) {
            $via === 'correo' || $whatsapp === '' => 'mailto:'.$lawyer->email,
            default => 'https://wa.me/'.$whatsapp,
        });
    }
}
