<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Seo\Services\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController
{
    public function index(SitemapBuilder $builder): Response
    {
        return response()
            ->view('seo.sitemap', ['urls' => $builder->urls()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            // El embudo y los comprobantes no aportan nada al índice.
            'Disallow: /contratar',
            'Disallow: /pago/',
            'Disallow: /pedido/',
            'Disallow: /casos/oferta/',
            'Disallow: /caso-complejo/recibido/',
            'Disallow: /admin',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
