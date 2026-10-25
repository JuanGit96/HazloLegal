<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\LegalDocument;
use Database\Seeders\BlogSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LawyerSeeder;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\ServicePlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ServicePlanSeeder::class,
            LawyerSeeder::class,
            LegalDocumentSeeder::class,
            FaqSeeder::class,
            BlogSeeder::class,
        ]);
    }

    public function test_la_home_trae_los_datos_estructurados_que_google_necesita(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('application/ld+json', escape: false)
            ->assertSee('"@type":"LegalService"', escape: false)
            ->assertSee('"@type":"WebSite"', escape: false)
            ->assertSee('"@type":"FAQPage"', escape: false)
            ->assertSee('"@type":"ItemList"', escape: false)
            ->assertSee('<link rel="canonical"', escape: false)
            ->assertSee('og:image', escape: false)
            ->assertSee('max-image-preview:large', escape: false);
    }

    public function test_la_landing_de_un_documento_declara_servicio_oferta_y_faq(): void
    {
        $document = LegalDocument::query()->where('slug', 'accion-de-tutela-salud')->firstOrFail();

        $this->get($document->url())
            ->assertOk()
            ->assertSee('"@type":"Service"', escape: false)
            ->assertSee('"@type":"Offer"', escape: false)
            ->assertSee('"priceCurrency":"COP"', escape: false)
            ->assertSee('"@type":"BreadcrumbList"', escape: false)
            ->assertSee('"@type":"FAQPage"', escape: false)
            ->assertSee($document->metaTitle());
    }

    public function test_un_articulo_declara_autor_con_tarjeta_profesional(): void
    {
        // La autoría verificable es la señal E-E-A-T clave en contenido legal.
        $this->get(route('blog.show', 'como-hacer-una-tutela-por-salud'))
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', escape: false)
            ->assertSee('"reviewedBy"', escape: false)
            ->assertSee('EducationalOccupationalCredential', escape: false)
            ->assertSee('Consejo Superior de la Judicatura');
    }

    public function test_el_sitemap_lista_el_catalogo_y_el_blog(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', escape: false)
            ->assertSee(route('documents.index'))
            ->assertSee(route('pages.guarantee'))
            ->assertSee('/documentos/accion-de-tutela-salud')
            ->assertSee('/blog/como-salir-de-datacredito');
    }

    public function test_robots_bloquea_el_embudo_y_publica_el_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'))
            ->assertSee('Disallow: /contratar')
            ->assertSee('Disallow: /admin');
    }

    public function test_las_paginas_del_embudo_no_se_indexan(): void
    {
        $document = LegalDocument::query()->first();

        $this->get(route('checkout.configure', ['document', $document->slug]))
            ->assertOk()
            ->assertSee('noindex, nofollow', escape: false);
    }

    public function test_la_home_muestra_la_garantia_y_el_whatsapp_flotante(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Certificado de Garantía Hazlo Legal')
            ->assertSee('wa-float', escape: false)
            ->assertSee('wa.me', escape: false);
    }

    public function test_la_seccion_de_precios_muestra_un_solo_plan_de_documento(): void
    {
        // El Express no es una tarjeta aparte: es una opción dentro del plan.
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Documento listo para usar')
            ->assertSee('Express (menos de 4 horas)')
            ->assertDontSee('Trámite Express');
    }
}
