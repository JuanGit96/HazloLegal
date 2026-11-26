<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Services\AddonOfferService;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\OrderTier;
use App\Domain\Orders\Mail\OrderPaidMail;
use App\Domain\Orders\Mail\OrderReceivedMail;
use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Models\Lead;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ServiceAddonSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->seed([
            ServiceAddonSeeder::class,
            LegalDocumentSeeder::class,
            PricingSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }

    /** @return array<string, mixed> */
    private function datosDelCliente(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Juliana Restrepo Ortiz',
            'customer_email' => 'juliana@example.com',
            'customer_phone' => '3001234567',
            'customer_document_type' => 'CC',
            'customer_document_number' => '1020304050',
            'customer_city' => 'Bogotá',
            'case_summary' => 'Pagué la deuda en marzo de 2025 y el reporte negativo sigue apareciendo en Datacrédito.',
            'accepts_terms' => '1',
            'accepts_privacy' => '1',
        ], $overrides);
    }

    public function test_un_tramite_gratuito_se_entrega_sin_pasar_por_pago(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->get(route('checkout.configure', $document->slug))
            ->assertOk()
            ->assertSee('Gratis');

        $response = $this->post(route('checkout.store', $document->slug), $this->datosDelCliente());

        $order = Order::query()->where('customer_email', 'juliana@example.com')->firstOrFail();

        // Meter una pantalla de pago de cero pesos es la forma más rápida de
        // perder a quien venía por algo gratis.
        $response->assertRedirect($order->trackingUrl());

        $this->assertSame(0, (int) $order->amount);
        $this->assertSame(OrderTier::Free, $order->tier);
        // Sin entrega inmediata, el documento entra a la cola y se promete
        // para el plazo configurado del trámite.
        $this->assertSame(OrderStatus::InReview, $order->status);
        $this->assertNotNull($order->promised_at);
        $this->assertNull($order->document_path);

        Mail::assertSent(OrderReceivedMail::class);
        // Queda encolado para el plazo prometido, no entregado al instante.
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_la_entrega_inmediata_no_espera_a_la_cola(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'addons' => ['express'],
        ]));

        $order = Order::query()->where('customer_email', 'juliana@example.com')->firstOrFail();

        // La inmediatez cuesta, así que primero hay que pagarla.
        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'nequi']);

        $order->refresh();

        // Y entonces sí: sale de una, sin pasar por la cola.
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertNotNull($order->document_path);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_los_adicionales_se_suman_al_precio_base_y_quedan_congelados(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'carlos@example.com',
            'addons' => ['instructivo', 'firma_digital', 'express'],
            'case_summary' => 'Necesito un NDA para mostrarle mi modelo de negocio a un inversionista la próxima semana.',
        ]));

        $order = Order::query()->where('customer_email', 'carlos@example.com')->firstOrFail();

        // 49.900 de base + 0 del instructivo + 19.900 de firma + 40.000 express.
        $this->assertSame(49_900, (int) $order->base_amount);
        $this->assertSame(59_900, (int) $order->addons_amount);
        $this->assertSame(109_800, (int) $order->amount);
        $this->assertSame(OrderTier::SelfService, $order->tier);
        $this->assertTrue($order->isExpress());

        // El precio viaja congelado en la línea del pedido: si mañana sube el
        // adicional, este pedido tiene que seguir diciendo lo que se cobró.
        $this->assertSame(19_900, (int) $order->addons->firstWhere('key', 'firma_digital')->price);
    }

    public function test_el_resumen_de_movil_se_puede_desplegar(): void
    {
        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $html = (string) $this->get(route('checkout.configure', $document->slug))->assertOk()->getContent();

        // En el teléfono los adicionales ocupan varias pantallas: al llegar
        // abajo el total ha subido y, sin poder desplegar el detalle, no se
        // ve por qué. Son dos resúmenes y los dos los llena el mismo guion.
        $this->assertSame(2, substr_count($html, 'data-addon-lines>'), 'El resumen de la barra de móvil ya no está.');
        $this->assertSame(2, substr_count($html, 'data-delivery>'));
        $this->assertStringContainsString('data-bar-count', $html);
        $this->assertStringContainsString('<details class="checkout-bar__detail">', $html);
    }

    public function test_un_tramite_que_no_se_arma_solo_va_a_cotizacion(): void
    {
        // Ofrecerle «entrega inmediata» o «instructivo» a algo que todavía no
        // existe no significa nada: esos trámites van directo al abogado.
        $document = LegalDocument::query()->where('slug', 'accion-de-tutela-salud')->firstOrFail();

        $this->assertFalse($document->allowsSelfService());

        $this->get(route('checkout.configure', $document->slug))
            ->assertRedirect(route('cases.create', [
                'area' => $document->practice_area->value,
                'tramite' => $document->slug,
            ]));

        // Y tampoco se puede forzar por POST.
        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente())
            ->assertNotFound();
    }

    public function test_todo_tramite_de_autoservicio_ofrece_sus_adicionales(): void
    {
        // Un producto nuevo que entre sin adicionales sale al aire con el
        // checkout vacío: pasó al crear los cinco últimos trámites.
        $this->seed(ProductSeeder::class);
        $this->seed(PricingSeeder::class);

        $sinAdicionales = LegalDocument::query()
            ->with('availableAddons')
            ->where('is_active', true)
            ->where('self_service_enabled', true)
            ->get()
            ->filter(fn (LegalDocument $d) => $d->availableAddons->isEmpty())
            ->pluck('slug')
            ->all();

        $this->assertSame([], $sinAdicionales, 'Estos trámites no ofrecen ningún adicional.');
    }

    public function test_pedir_abogado_y_entrega_inmediata_no_cobra_las_dos(): void
    {
        Mail::fake();

        // Nadie redacta sobre hechos ajenos al instante: cobrar la inmediatez
        // encima sería cobrar por algo que no va a pasar.
        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'ambos@example.com',
            'addons' => ['acompanamiento', 'express'],
        ]));

        $order = Order::query()->where('customer_email', 'ambos@example.com')->firstOrFail();

        $this->assertTrue($order->hasAddon('acompanamiento'));
        $this->assertFalse($order->isExpress(), 'La entrega inmediata no debería haberse cobrado.');
        $this->assertSame(49_900 + 90_100, (int) $order->amount);
    }

    public function test_la_pagina_de_entrega_no_ofrece_lo_que_choca_con_lo_ya_comprado(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        // Con abogado contratado, la entrega inmediata no se puede cumplir:
        // ofrecerla después sería vender algo que vamos a tener que anular.
        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'conabogado@example.com',
            'addons' => ['acompanamiento'],
        ]));

        $order = Order::query()->where('customer_email', 'conabogado@example.com')->firstOrFail();
        $order->forceFill(['paid_at' => now(), 'status' => OrderStatus::InReview, 'promised_at' => now()->addDay()])->save();

        $respuesta = $this->get($order->trackingUrl());

        $respuesta->assertOk()
            ->assertDontSeeText('Pasar a entrega inmediata')
            ->assertDontSeeText('Entrega inmediata');

        $this->assertTrue($order->refresh()->hasLawyerWork());
    }

    public function test_a_quien_pago_la_prisa_no_se_le_ofrece_el_abogado(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'conprisa@example.com',
            'addons' => ['express'],
        ]));

        $order = Order::query()->where('customer_email', 'conprisa@example.com')->firstOrFail();
        $order->forceFill(['paid_at' => now(), 'status' => OrderStatus::InReview])->save();

        // El acompañamiento obligaría a renunciar a la prisa que acaba de
        // pagar; la oferta siguiente tiene que ser otra.
        $siguiente = app(AddonOfferService::class)
            ->nextBestOffer($order->document, $order->addons->pluck('key'));

        $this->assertNotNull($siguiente);
        $this->assertNotSame('review', $siguiente->addon->kind->value);
    }

    public function test_el_servidor_rechaza_sumar_un_adicional_incompatible(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'reenviado@example.com',
            'addons' => ['acompanamiento'],
        ]));

        $order = Order::query()->where('customer_email', 'reenviado@example.com')->firstOrFail();
        $order->forceFill(['paid_at' => now(), 'status' => OrderStatus::InReview])->save();

        $antes = (int) $order->amount;

        // Esconder el botón no basta: el enlace se puede reenviar, y entonces
        // habría que devolver un dinero que no debimos cobrar.
        $this->post(route('checkout.add-addon', $order->reference), [
            'addon' => 'express',
            't' => $order->access_token,
        ])->assertRedirect();

        $order->refresh()->load('addons');

        $this->assertFalse($order->isExpress());
        $this->assertSame($antes, (int) $order->amount);
    }

    public function test_el_acompanamiento_marca_el_pedido_como_atendido_por_abogado(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'nda@example.com',
            'addons' => ['acompanamiento'],
        ]));

        $order = Order::query()->where('customer_email', 'nda@example.com')->firstOrFail();

        $this->assertSame(OrderTier::Accompanied, $order->tier);
    }

    public function test_el_embudo_de_pago_llega_hasta_la_confirmacion(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $response = $this->post(route('checkout.store', $document->slug), $this->datosDelCliente());

        $order = Order::query()->where('customer_email', 'juliana@example.com')->firstOrFail();

        $response->assertRedirect(route('checkout.payment', $order->reference));
        $this->assertSame(OrderStatus::AwaitingPayment, $order->status);
        Mail::assertSent(OrderReceivedMail::class);

        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'nequi'])
            ->assertRedirect($order->trackingUrl());

        $order->refresh();

        $this->assertNotNull($order->paid_at);
        // Pagado y en preparación: la entrega va por cola salvo que haya
        // contratado la inmediata.
        $this->assertSame(OrderStatus::InReview, $order->status);
        Mail::assertSent(OrderPaidMail::class);

        $this->get($order->trackingUrl())
            ->assertOk()
            ->assertSee($order->reference);
    }

    public function test_el_pedido_exige_aceptar_terminos_y_tratamiento_de_datos(): void
    {
        $document = LegalDocument::query()->first();

        $this->post(route('checkout.store', $document->slug), [
            'customer_name' => 'Persona De Prueba',
            'customer_email' => 'prueba@example.com',
            'customer_phone' => '3001112233',
            'customer_document_type' => 'CC',
            'case_summary' => 'Una descripción suficientemente larga para pasar la validación mínima.',
        ])->assertSessionHasErrors(['accepts_terms', 'accepts_privacy']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_el_consentimiento_de_datos_queda_registrado_tal_como_lo_marco_el_cliente(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'shareable_with_lawyers' => '1',
        ]));

        $lead = Lead::query()->where('email', 'juliana@example.com')->firstOrFail();

        $this->assertTrue($lead->consent_share_with_lawyers);
        // No marcó la de marketing: autorizar una cosa no autoriza la otra.
        $this->assertFalse($lead->consent_marketing);
        $this->assertNotNull($lead->consented_at);
    }

    public function test_la_referencia_sola_no_abre_el_pedido_de_otro(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente());

        $order = Order::query()->where('customer_email', 'juliana@example.com')->firstOrFail();

        // Otro visitante, sin la llave: la referencia es correlativa y por
        // tanto adivinable, así que no puede servir de credencial.
        $this->flushSession();

        $this->get(route('checkout.confirmation', $order->reference))->assertForbidden();
        $this->get(route('checkout.download', [$order->reference, 'documento']))->assertForbidden();

        $this->get($order->trackingUrl())->assertOk();
    }

    public function test_se_puede_sumar_un_adicional_a_un_pedido_ya_entregado(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente());

        $order = Order::query()->where('customer_email', 'juliana@example.com')->firstOrFail();

        $this->post(route('checkout.add-addon', $order->reference), ['addon' => 'instructivo'])
            ->assertRedirect(route('checkout.payment', $order->reference));

        $order->refresh();

        $this->assertTrue($order->hasAddon('instructivo'));
        $this->assertSame(19_900, (int) $order->amount);
        $this->assertNull($order->paid_at, 'Si el adicional cuesta, el pedido vuelve a cobro.');
    }

    public function test_un_pedido_ya_pagado_no_se_cobra_dos_veces(): void
    {
        Mail::fake();

        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', $document->slug), $this->datosDelCliente([
            'customer_email' => 'doble@example.com',
        ]));

        $order = Order::query()->where('customer_email', 'doble@example.com')->firstOrFail();

        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'nequi']);
        $firstPaidAt = $order->refresh()->paid_at;

        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'pse']);

        $this->assertEquals($firstPaidAt, $order->refresh()->paid_at);
        Mail::assertSent(OrderPaidMail::class, 1);
    }
}
