<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Models\Post;
use Database\Seeders\BlogSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\LawyerSeeder;
use Database\Seeders\CalculatorSeeder;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\ServiceAddonSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ServiceAddonSeeder::class,
            LawyerSeeder::class,
            LegalDocumentSeeder::class,
            PricingSeeder::class,
            SiteSettingSeeder::class,
            CalculatorSeeder::class,
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

    public function test_un_articulo_declara_autor_acreditado(): void
    {
        // La autoría acreditada es la señal E-E-A-T clave en contenido legal.
        $this->get(route('blog.show', 'como-hacer-una-tutela-por-salud'))
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', escape: false)
            ->assertSee('"reviewedBy"', escape: false)
            ->assertSee('"@type":"Person"', escape: false)
            ->assertSee('Daniela Restrepo Gómez');
    }

    public function test_un_articulo_del_equipo_declara_a_la_marca_como_autora(): void
    {
        // No todo lo que se publica es jurídico: una guía de uso o una
        // comparativa la firma la casa, y el marcado tiene que decirlo sin
        // inventar un abogado que no lo escribió.
        $post = \App\Domain\Content\Models\Post::query()->published()->firstOrFail();
        $post->update(['author_lawyer_id' => null]);

        $this->get($post->url())
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', escape: false)
            ->assertSee('Escrito por el equipo de')
            ->assertDontSee('"reviewedBy"', escape: false);
    }

    public function test_la_tarjeta_profesional_nunca_se_publica(): void
    {
        // Es dato interno: publicarla comprometería al abogado con una
        // revisión individual que el servicio automatizado no hace.
        $lawyer = \App\Domain\Lawyers\Models\Lawyer::query()
            ->whereNotNull('professional_card')
            ->firstOrFail();

        foreach ([
            route('home'),
            route('pages.about'),
            route('cases.create'),
            route('blog.show', 'como-hacer-una-tutela-por-salud'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee($lawyer->professional_card)
                ->assertDontSee('Consejo Superior de la Judicatura')
                ->assertDontSee('tarjeta profesional');
        }
    }

    public function test_el_sitio_no_promete_revision_de_cada_documento(): void
    {
        foreach ([route('home'), route('documents.index'), route('pages.how-it-works')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('abogado titulado')
                ->assertDontSee('audita el PDF');
        }
    }

    public function test_la_home_no_nombra_alternativas_gratuitas(): void
    {
        // Mencionar la IA o las plantillas gratis dentro del embudo le planta
        // la idea a quien no la traía. Ese contenido vive en el blog.
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('inteligencia artificial')
            ->assertDontSee('IA gratuita')
            ->assertDontSee('Plantilla de internet');
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
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /portal');
    }

    public function test_ningun_estatico_tapa_las_rutas_de_seo(): void
    {
        // El servidor sirve public/ antes de llegar a PHP: un robots.txt o un
        // sitemap.xml ahí dentro se come la ruta sin que ningún test de
        // integración se entere, porque los tests van directo al router.
        foreach (['robots.txt', 'sitemap.xml'] as $file) {
            $this->assertFileDoesNotExist(
                public_path($file),
                "public/{$file} taparía la ruta que lo genera."
            );
        }
    }

    public function test_las_paginas_del_embudo_no_se_indexan(): void
    {
        // Uno que sí pase por el checkout: los que solo se hacen con abogado
        // redirigen a cotización.
        $document = LegalDocument::query()->where('self_service_enabled', true)->firstOrFail();

        $this->get(route('checkout.configure', $document->slug))
            ->assertOk()
            ->assertSee('noindex, nofollow', escape: false);
    }

    public function test_la_home_muestra_la_garantia_y_el_whatsapp_flotante(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Certificado de Garantía HazloLegal')
            ->assertSee('wa-float', escape: false)
            ->assertSee('wa.me', escape: false);
    }

    public function test_la_home_ofrece_lo_gratis_antes_que_lo_pago(): void
    {
        // El modelo entero depende de esto: si lo gratuito no se ve primero,
        // no hay quien entre al embudo.
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('hoy y gratis', escape: false)
            ->assertSee('Tú armas el trámite');
    }

    public function test_cada_adicional_muestra_su_precio_antes_de_pagar(): void
    {
        // Un adicional sin precio a la vista es una sorpresa en la factura.
        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->get(route('checkout.configure', $document->slug))
            ->assertOk()
            ->assertSee('Instructivo', escape: false)
            ->assertSee('$19.900');
    }

    public function test_la_home_pide_consentimiento_de_datos_de_forma_visible(): void
    {
        // Regalar el documento a cambio del dato solo es legítimo si el
        // titular sabe qué está autorizando (Ley 1581 de 2012).
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ley 1581 de 2012')
            ->assertSee('autorización de tratamiento de datos');
    }

    public function test_el_consentimiento_de_abogados_es_una_casilla_aparte(): void
    {
        // Autorizar el correo no autoriza que un abogado llame: son dos
        // finalidades distintas y tienen que marcarse por separado.
        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->get(route('checkout.configure', $document->slug))
            ->assertOk()
            ->assertSee('name="accepts_marketing"', escape: false)
            ->assertSee('name="shareable_with_lawyers"', escape: false);
    }

    public function test_un_articulo_dice_que_el_tramite_es_gratis_en_vez_de_esconderlo(): void
    {
        // Quien acaba de leer la guía está decidiendo si vale el clic.
        // «Ver precio y plazo» le hace pensar que cuesta; si es gratis, se
        // dice ahí mismo.
        $post = Post::query()
            ->with('relatedDocument')
            ->get()
            ->first(fn (Post $p) => $p->relatedDocument?->isFreeTier());

        $this->assertNotNull($post, 'Debería haber al menos un artículo que lleve a un trámite gratuito.');

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSeeText('¡Es gratis! Hacerlo ahora')
            ->assertDontSeeText('Ver precio y plazo')
            // Un trámite que se arma solo no lo escribe un abogado para cada
            // cliente, así que tampoco se insinúa.
            ->assertDontSeeText('¿Prefieres que lo haga un abogado?');
    }

    public function test_un_articulo_de_un_tramite_pago_sigue_mostrando_precio_y_plazo(): void
    {
        $post = Post::query()
            ->with('relatedDocument')
            ->get()
            ->first(fn (Post $p) => $p->relatedDocument !== null && ! $p->relatedDocument->isFreeTier());

        $this->assertNotNull($post);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSeeText('Ver precio y plazo');
    }

    public function test_el_caso_complejo_promete_que_elige_el_cliente(): void
    {
        // El reparto cambió: los abogados se postulan y elige el cliente.
        // Prometer una carrera que ya no existe es prometer otro servicio.
        $this->get(route('cases.create'))
            ->assertOk()
            ->assertSeeText('eliges tú')
            ->assertDontSeeText('el primero que lo acepta se queda con él')
            ->assertDontSeeText('El primero que acepta lo toma');
    }
}
