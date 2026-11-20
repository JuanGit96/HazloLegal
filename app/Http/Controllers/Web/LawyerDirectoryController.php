<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Seo\Services\SeoManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
    public function __construct(private readonly SeoManager $seo) {}

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

        return view('lawyers.show', [
            'meta' => $this->seo->page(
                title: $lawyer->seo_title ?: "{$lawyer->displayName()} · {$lawyer->headline} en {$lawyer->city}",
                description: $lawyer->seo_description ?: \Illuminate\Support\Str::limit((string) $lawyer->bio, 155),
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
}
