<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Mail\OrderPaidMail;
use App\Domain\Orders\Mail\OrderReceivedMail;
use App\Domain\Orders\Models\Order;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\ServicePlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([ServicePlanSeeder::class, LegalDocumentSeeder::class]);
    }

    public function test_el_cliente_puede_recorrer_el_embudo_completo_hasta_el_pago(): void
    {
        Mail::fake();

        $plan = ServicePlan::query()->where('key', 'document')->firstOrFail();
        $document = LegalDocument::query()->where('slug', 'eliminar-reporte-datacredito')->firstOrFail();

        // Paso 1: elegir documento dentro del plan.
        $this->get(route('checkout.documents', $plan->key->value))
            ->assertOk()
            ->assertSee($document->name);

        // Paso 2: configurar velocidad y datos.
        $this->get(route('checkout.configure', [$plan->key->value, $document->slug]))
            ->assertOk()
            ->assertSee('Express');

        // Paso 3: crear el pedido.
        $response = $this->post(route('checkout.store', [$plan->key->value, $document->slug]), [
            'speed' => DeliverySpeed::Standard->value,
            'customer_name' => 'Juliana Restrepo Ortiz',
            'customer_email' => 'juliana@example.com',
            'customer_phone' => '3001234567',
            'customer_document_type' => 'CC',
            'customer_document_number' => '1020304050',
            'customer_city' => 'Bogotá',
            'case_summary' => 'Pagué la deuda en marzo de 2025 y el reporte negativo sigue apareciendo en Datacrédito.',
            'accepts_terms' => '1',
            'accepts_privacy' => '1',
        ]);

        $order = Order::query()->where('customer_email', 'juliana@example.com')->firstOrFail();

        $response->assertRedirect(route('checkout.payment', $order->reference));

        $this->assertSame(OrderStatus::AwaitingPayment, $order->status);
        $this->assertSame(90_000, $order->amount);
        $this->assertStringStartsWith('HL-', $order->reference);
        Mail::assertSent(OrderReceivedMail::class);

        // Paso 4: confirmar el pago.
        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'nequi'])
            ->assertRedirect(route('checkout.confirmation', $order->reference));

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->promised_at);
        Mail::assertSent(OrderPaidMail::class);

        $this->get(route('checkout.confirmation', $order->reference))
            ->assertOk()
            ->assertSee($order->reference);
    }

    public function test_el_plan_express_cobra_el_precio_express(): void
    {
        Mail::fake();

        $plan = ServicePlan::query()->where('key', 'document')->firstOrFail();
        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        $this->post(route('checkout.store', [$plan->key->value, $document->slug]), [
            'speed' => DeliverySpeed::Express->value,
            'customer_name' => 'Carlos Andrés Mejía',
            'customer_email' => 'carlos@example.com',
            'customer_phone' => '3109876543',
            'customer_document_type' => 'CC',
            'case_summary' => 'Necesito un NDA para mostrarle mi modelo de negocio a un inversionista la próxima semana.',
            'accepts_terms' => '1',
            'accepts_privacy' => '1',
        ]);

        $order = Order::query()->where('customer_email', 'carlos@example.com')->firstOrFail();

        $this->assertSame(DeliverySpeed::Express, $order->speed);
        $this->assertSame(140_000, $order->amount);
    }

    public function test_el_pedido_exige_aceptar_terminos_y_tratamiento_de_datos(): void
    {
        $plan = ServicePlan::query()->where('key', 'document')->firstOrFail();
        $document = LegalDocument::query()->first();

        $this->post(route('checkout.store', [$plan->key->value, $document->slug]), [
            'speed' => DeliverySpeed::Standard->value,
            'customer_name' => 'Persona De Prueba',
            'customer_email' => 'prueba@example.com',
            'customer_phone' => '3001112233',
            'customer_document_type' => 'CC',
            'case_summary' => 'Una descripción suficientemente larga para pasar la validación mínima.',
        ])->assertSessionHasErrors(['accepts_terms', 'accepts_privacy']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_un_pedido_ya_pagado_no_se_cobra_dos_veces(): void
    {
        Mail::fake();

        $plan = ServicePlan::query()->where('key', 'document')->firstOrFail();
        $document = LegalDocument::query()->first();

        $this->post(route('checkout.store', [$plan->key->value, $document->slug]), [
            'speed' => DeliverySpeed::Standard->value,
            'customer_name' => 'Doble Pago Prueba',
            'customer_email' => 'doble@example.com',
            'customer_phone' => '3001112233',
            'customer_document_type' => 'CC',
            'case_summary' => 'Una descripción suficientemente larga para pasar la validación mínima.',
            'accepts_terms' => '1',
            'accepts_privacy' => '1',
        ]);

        $order = Order::query()->where('customer_email', 'doble@example.com')->firstOrFail();

        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'nequi']);
        $firstPaidAt = $order->refresh()->paid_at;

        $this->post(route('checkout.pay', $order->reference), ['payment_method' => 'pse']);

        $this->assertEquals($firstPaidAt, $order->refresh()->paid_at);
        Mail::assertSent(OrderPaidMail::class, 1);
    }
}
