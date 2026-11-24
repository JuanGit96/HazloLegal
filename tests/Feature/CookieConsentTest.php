<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Models\CookieConsent;
use App\Domain\Shared\Services\CookieConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El consentimiento de cookies, que es una obligación legal y no una barra
 * decorativa. Lo que se protege aquí:
 *
 * - Que nada opcional se cargue antes de la autorización.
 * - Que rechazar sea un clic, igual que aceptar.
 * - Que quede prueba de la decisión, porque la ley la exige.
 * - Que se pueda revocar.
 */
class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_aviso_se_pinta_mientras_nadie_haya_decidido(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="aviso-cookies"', false)
            ->assertSeeText('Solo las necesarias')
            ->assertSeeText('Aceptar todas');
    }

    public function test_sin_autorizacion_no_sale_ninguna_etiqueta_de_medicion(): void
    {
        config([
            'hazlolegal.seo.ga_measurement_id' => 'G-PRUEBA123',
            'hazlolegal.seo.meta_pixel_id' => '999888777',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('connect.facebook.net', false);
    }

    public function test_aceptar_todo_guarda_la_prueba_y_deja_de_preguntar(): void
    {
        config(['hazlolegal.seo.ga_measurement_id' => 'G-PRUEBA123']);

        $respuesta = $this->post(route('cookies.store'), [
            'decision' => 'all',
            'redirect' => '/',
        ]);

        $respuesta->assertRedirect('/')->assertSessionHas('status');

        $registro = CookieConsent::query()->sole();
        $this->assertTrue($registro->analytics);
        $this->assertTrue($registro->marketing);
        $this->assertSame('all', $registro->decision);
        $this->assertNotNull($registro->token);
        $this->assertSame(config('hazlolegal.cookies.version'), $registro->policy_version);

        // Ya con la cookie puesta: no se vuelve a preguntar y la etiqueta sí carga.
        $this->withCookie(CookieConsentService::COOKIE, (string) json_encode([
            'analytics' => true,
            'marketing' => true,
            'token' => $registro->token,
            'v' => config('hazlolegal.cookies.version'),
        ]))
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="aviso-cookies"', false)
            ->assertSee('googletagmanager.com', false);
    }

    public function test_rechazar_queda_registrado_y_no_carga_nada_opcional(): void
    {
        config(['hazlolegal.seo.ga_measurement_id' => 'G-PRUEBA123']);

        $this->post(route('cookies.store'), ['decision' => 'necessary', 'redirect' => '/']);

        $registro = CookieConsent::query()->sole();
        $this->assertFalse($registro->analytics);
        $this->assertFalse($registro->marketing);
        $this->assertSame('necessary', $registro->decision);

        $this->withCookie(CookieConsentService::COOKIE, (string) json_encode([
            'analytics' => false,
            'marketing' => false,
            'token' => $registro->token,
            'v' => config('hazlolegal.cookies.version'),
        ]))
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="aviso-cookies"', false)
            ->assertDontSee('googletagmanager.com', false);
    }

    public function test_se_puede_autorizar_una_familia_y_no_la_otra(): void
    {
        $this->post(route('cookies.store'), [
            'decision' => 'custom',
            'analytics' => '1',
            'redirect' => route('pages.cookies'),
        ])->assertRedirect(route('pages.cookies'));

        $registro = CookieConsent::query()->sole();
        $this->assertTrue($registro->analytics);
        $this->assertFalse($registro->marketing);
        $this->assertSame('custom', $registro->decision);
    }

    public function test_revocar_deja_rastro_de_las_dos_decisiones(): void
    {
        $this->post(route('cookies.store'), ['decision' => 'all']);
        $primera = CookieConsent::query()->sole();

        $this->withCookie(CookieConsentService::COOKIE, (string) json_encode([
            'analytics' => true,
            'marketing' => true,
            'token' => $primera->token,
            'v' => config('hazlolegal.cookies.version'),
        ]))->post(route('cookies.store'), ['decision' => 'necessary']);

        // Dos filas, el mismo token: se puede demostrar qué autorizó y cuándo
        // lo retiró. Si se editara la fila, esa segunda prueba no existiría.
        $this->assertSame(2, CookieConsent::query()->count());
        $this->assertSame(2, CookieConsent::query()->where('token', $primera->token)->count());
        $this->assertSame('necessary', CookieConsent::query()->latest('id')->first()->decision);
    }

    public function test_al_cambiar_la_version_de_la_politica_se_vuelve_a_preguntar(): void
    {
        $this->withCookie(CookieConsentService::COOKIE, (string) json_encode([
            'analytics' => true,
            'marketing' => true,
            'token' => 'cualquiera',
            'v' => 'una-version-vieja',
        ]))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="aviso-cookies"', false);
    }

    public function test_el_redirect_no_sirve_para_mandar_a_otro_dominio(): void
    {
        $this->post(route('cookies.store'), [
            'decision' => 'all',
            'redirect' => 'https://sitio-ajeno.example/phishing',
        ])->assertRedirect(route('home'));
    }

    public function test_la_politica_publica_el_detalle_de_cada_cookie(): void
    {
        $this->get(route('pages.cookies'))
            ->assertOk()
            ->assertSeeText('Política de cookies')
            // Nombre, finalidad, duración y responsable: informar «usamos
            // cookies» no cumple con la Ley 1581.
            ->assertSeeText(config('session.cookie'))
            ->assertSeeText('_ga, _ga_*')
            ->assertSeeText('transferencia internacional')
            ->assertSeeText('Guardar mis preferencias')
            ->assertSeeText('Revocar todo y dejar solo las necesarias');
    }
}
