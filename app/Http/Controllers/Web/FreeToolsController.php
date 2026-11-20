<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Settings\Services\SettingsService;
use App\Domain\Tools\Models\Calculator;
use Illuminate\Contracts\View\View;

/**
 * El hub de lo gratuito.
 *
 * Es la página que más tráfico debería traer: quien busca «calculadora de
 * liquidación» o «derecho de petición gratis» llega aquí, usa la herramienta
 * y deja su correo a cambio del resultado.
 */
class FreeToolsController
{
    public function __invoke(): View
    {
        return $this->index();
    }

    public function index(SeoManager $seo, ?SettingsService $settings = null): View
    {
        $settings ??= app(SettingsService::class);

        $freeDocuments = LegalDocument::query()
            ->active()
            ->where('is_free', true)
            ->ordered()
            ->get();

        $calculators = Calculator::query()->active()->get();

        $meta = $seo->page(
            title: 'Herramientas legales gratis para Colombia | '.$settings->get('brand_name', 'HazloLegal'),
            description: 'Genera derechos de petición gratis, calcula tu liquidación laboral y resuelve tus trámites con herramientas basadas en la ley colombiana. Sin registro y sin costo.',
            canonical: route('tools.index'),
            crumbs: [
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Herramientas gratis', 'url' => route('tools.index')],
            ],
        );

        return view('tools.index', [
            'meta' => $meta,
            'freeDocuments' => $freeDocuments,
            'calculators' => $calculators,
            'totalTools' => $freeDocuments->count() + $calculators->count(),
            'generatedCount' => $settings->get('hook_free_count', '0'),
        ]);
    }
}
