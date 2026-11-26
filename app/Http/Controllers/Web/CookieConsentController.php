<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Seo\Services\SeoManager;
use App\Domain\Shared\Enums\CookieCategory;
use App\Domain\Shared\Services\CookieConsentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * La decisión del visitante sobre las cookies.
 *
 * Es un POST con formulario normal a propósito: así la decisión se puede
 * tomar sin JavaScript, y «rechazar» cuesta exactamente lo mismo que
 * «aceptar». Un banner donde aceptar es un clic y rechazar son tres no
 * recoge una autorización libre.
 */
class CookieConsentController
{
    public function __construct(
        private readonly CookieConsentService $consent,
        private readonly SeoManager $seo,
    ) {}

    public function policy(): View
    {
        return view('pages.cookies', [
            'meta' => $this->seo->page(
                title: 'Política de cookies de HazloLegal',
                description: 'Qué cookies usa HazloLegal, para qué sirve cada una, cuánto duran y cómo autorizarlas o revocarlas en cualquier momento. Conforme a la Ley 1581 de 2012.',
                canonical: route('pages.cookies'),
                crumbs: [
                    ['name' => 'Inicio', 'url' => url('/')],
                    ['name' => 'Política de cookies', 'url' => route('pages.cookies')],
                ],
            ),
            'catalog' => $this->consent->catalog(),
            'choice' => $this->consent->current(),
            'consent' => $this->consent,
            'categories' => CookieCategory::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:all,necessary,custom'],
            'analytics' => ['nullable', 'boolean'],
            'marketing' => ['nullable', 'boolean'],
            'redirect' => ['nullable', 'string', 'max:300'],
        ]);

        [$analytics, $marketing] = match ($validated['decision']) {
            'all' => [true, true],
            'necessary' => [false, false],
            default => [
                $request->boolean('analytics'),
                $request->boolean('marketing'),
            ],
        };

        $source = $validated['decision'] === 'custom' ? 'preferencias' : 'banner';

        $this->consent->remember($analytics, $marketing, $source);

        $mensaje = $this->message($analytics, $marketing);

        // Con JavaScript el aviso se quita solo y la página no se recarga:
        // recargar a quien está leyendo un artículo o llenando un trámite es
        // peor que el aviso. Sin JavaScript sigue el camino de siempre.
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $mensaje]);
        }

        // Se vuelve a donde estaba. El destino se valida contra las rutas
        // propias: un «redirect» que acepte cualquier URL es una puerta
        // abierta para mandar visitantes a otro sitio desde un enlace nuestro.
        return redirect()->to($this->safeBack($validated['redirect'] ?? null))
            ->with('status', $mensaje);
    }

    private function message(bool $analytics, bool $marketing): string
    {
        if (! $analytics && ! $marketing) {
            return 'Listo: solo las necesarias. Puedes cambiarlo cuando quieras.';
        }

        return 'Gracias. Puedes cambiar tu decisión cuando quieras desde el pie de página.';
    }

    /** Solo se devuelve a una ruta de este sitio. */
    private function safeBack(?string $target): string
    {
        foreach ([$target, url()->previous()] as $candidato) {
            if (filled($candidato) && $this->isOurs((string) $candidato)) {
                return (string) $candidato;
            }
        }

        return route('home');
    }

    private function isOurs(string $url): bool
    {
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        return parse_url($url, PHP_URL_HOST) === request()->getHost();
    }
}
