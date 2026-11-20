<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Payments\Services\WompiGateway;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\ServiceAddonSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WompiPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const EVENTS_SECRET = 'test_events_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();

        config([
            'wompi.enabled' => true,
            'wompi.mode' => 'sandbox',
            'wompi.public_key' => 'pub_test_llave',
            'wompi.integrity_secret' => 'test_integrity_secret',
            'wompi.events_secret' => self::EVENTS_SECRET,
        ]);

        $this->seed([
            ServiceAddonSeeder::class,
            LegalDocumentSeeder::class,
            PricingSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }

    private function pedidoPendiente(): Order
    {
        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        return Order::query()->create([
            'reference' => 'HL-2026-07777',
            'access_token' => bin2hex(random_bytes(16)),
            'legal_document_id' => $document->id,
            'status' => OrderStatus::AwaitingPayment,
            'tier' => 'self_service',
            'customer_name' => 'Andrés Felipe Quintero',
            'customer_email' => 'andres@example.com',
            'customer_phone' => '3001234567',
            'customer_document_type' => 'CC',
            'case_summary' => 'Necesito un acuerdo de confidencialidad.',
            'amount' => 49_900,
            'base_amount' => 49_900,
            'promised_at' => now()->addDay(),
        ]);
    }

    /** Arma un evento firmado como lo haría Wompi. */
    private function evento(PaymentTransaction $transaction, string $status): array
    {
        $data = [
            'transaction' => [
                'id' => 'wompi-tx-123',
                'reference' => $transaction->reference,
                'status' => $status,
                'amount_in_cents' => $transaction->amount_in_cents,
                'currency' => 'COP',
                'payment_method_type' => 'NEQUI',
            ],
        ];

        $timestamp = time();
        $properties = ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'];

        $concatenated = '';
        foreach ($properties as $property) {
            $concatenated .= (string) data_get($data, $property, '');
        }
        $concatenated .= $timestamp.self::EVENTS_SECRET;

        return [
            'event' => 'transaction.updated',
            'data' => $data,
            'timestamp' => $timestamp,
            'signature' => [
                'properties' => $properties,
                'checksum' => hash('sha256', $concatenated),
            ],
        ];
    }

    public function test_la_pantalla_de_pago_sale_hacia_wompi_con_firma_de_integridad(): void
    {
        $order = $this->pedidoPendiente();

        $response = $this->withSession(['orders' => [$order->reference]])
            ->get(route('checkout.payment', $order->reference))
            ->assertOk()
            ->assertSee('checkout.wompi.co', escape: false)
            ->assertSee('signature:integrity', escape: false);

        $transaction = PaymentTransaction::query()->firstOrFail();

        // La firma es lo que impide que alguien edite el monto en el enlace.
        $esperada = hash('sha256', $transaction->reference.$transaction->amount_in_cents.'COP'.'test_integrity_secret');
        $response->assertSee($esperada, escape: false);

        $this->assertSame(4_990_000, (int) $transaction->amount_in_cents);
    }

    public function test_un_evento_aprobado_pone_el_pedido_en_marcha(): void
    {
        $order = $this->pedidoPendiente();
        $transaction = app(WompiGateway::class)->open($order, 49_900, $order->customer_email);

        $this->postJson(route('wompi.webhook'), $this->evento($transaction, 'APPROVED'))
            ->assertOk();

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(PaymentStatus::Approved, $transaction->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(OrderStatus::InReview, $order->status);
    }

    public function test_un_evento_sin_firma_valida_no_cambia_nada(): void
    {
        $order = $this->pedidoPendiente();
        $transaction = app(WompiGateway::class)->open($order, 49_900, $order->customer_email);

        $evento = $this->evento($transaction, 'APPROVED');
        $evento['signature']['checksum'] = str_repeat('0', 64);

        // Sin esto, cualquiera que conozca la URL podría marcar pedidos como
        // pagados.
        $this->postJson(route('wompi.webhook'), $evento)->assertStatus(401);

        $this->assertSame(PaymentStatus::Pending, $transaction->refresh()->status);
        $this->assertNull($order->refresh()->paid_at);
    }

    public function test_un_evento_repetido_no_entrega_dos_veces(): void
    {
        $order = $this->pedidoPendiente();
        $transaction = app(WompiGateway::class)->open($order, 49_900, $order->customer_email);

        $evento = $this->evento($transaction, 'APPROVED');

        $this->postJson(route('wompi.webhook'), $evento)->assertOk();
        $primerPago = $order->refresh()->paid_at;

        $this->postJson(route('wompi.webhook'), $evento)->assertOk();

        $this->assertEquals($primerPago, $order->refresh()->paid_at);
        $this->assertSame(1, PaymentTransaction::query()->count());
    }

    public function test_volver_sin_transaccion_local_no_deja_al_cliente_en_un_404(): void
    {
        // Pasó de verdad: alguien pagó, volvió, y se encontró un «no
        // encontrado». Quien acaba de pagar da por hecho que perdió su
        // dinero, así que esta pantalla nunca puede ser un 404.
        $order = $this->pedidoPendiente();

        $this->get(route('wompi.return', 'HL-'.$order->id.'-ABC123'))
            ->assertRedirect($order->trackingUrl());
    }

    public function test_una_referencia_irreconocible_muestra_una_pantalla_de_espera(): void
    {
        $this->get(route('wompi.return', 'REF-DE-OTRO-ENTORNO'))
            ->assertOk()
            ->assertSee('Estamos confirmando tu pago')
            ->assertSee('No vuelvas a pagar');
    }

    public function test_la_api_de_pruebas_apunta_al_entorno_de_pruebas(): void
    {
        // Apuntar al entorno equivocado devuelve 401 sin decir por qué, y los
        // pagos aprobados se quedan sin confirmar.
        $this->assertSame('https://sandbox.wompi.co/v1', app(WompiGateway::class)->apiUrl());

        config(['wompi.mode' => 'production']);

        $this->assertSame('https://production.wompi.co/v1', app(WompiGateway::class)->apiUrl());
    }

    public function test_no_se_puede_aprobar_un_pedido_con_la_transaccion_de_otro(): void
    {
        // Sin comprobar la referencia, pasar el identificador de cualquier
        // transacción aprobada marcaría como pagado el pedido que se quisiera.
        $order = $this->pedidoPendiente();
        $transaction = app(WompiGateway::class)->open($order, 49_900, $order->customer_email);

        $evento = $this->evento($transaction, 'APPROVED');
        $evento['data']['transaction']['reference'] = 'HL-OTRO-PEDIDO';

        // La firma del evento es válida; lo que no cuadra es la referencia.
        $timestamp = $evento['timestamp'];
        $concatenated = '';
        foreach ($evento['signature']['properties'] as $property) {
            $concatenated .= (string) data_get($evento['data'], $property, '');
        }
        $evento['signature']['checksum'] = hash('sha256', $concatenated.$timestamp.self::EVENTS_SECRET);

        $this->postJson(route('wompi.webhook'), $evento)->assertOk();

        $this->assertSame(PaymentStatus::Pending, $transaction->refresh()->status);
        $this->assertNull($order->refresh()->paid_at);
    }

    public function test_un_monto_distinto_al_abierto_no_aprueba_el_pedido(): void
    {
        $order = $this->pedidoPendiente();
        $transaction = app(WompiGateway::class)->open($order, 49_900, $order->customer_email);

        $evento = $this->evento($transaction, 'APPROVED');
        $evento['data']['transaction']['amount_in_cents'] = 100;

        $timestamp = $evento['timestamp'];
        $concatenated = '';
        foreach ($evento['signature']['properties'] as $property) {
            $concatenated .= (string) data_get($evento['data'], $property, '');
        }
        $evento['signature']['checksum'] = hash('sha256', $concatenated.$timestamp.self::EVENTS_SECRET);

        $this->postJson(route('wompi.webhook'), $evento)->assertOk();

        $this->assertNull($order->refresh()->paid_at);
    }

    public function test_un_pago_rechazado_deja_el_pedido_sin_pagar(): void
    {
        $order = $this->pedidoPendiente();
        $transaction = app(WompiGateway::class)->open($order, 49_900, $order->customer_email);

        $this->postJson(route('wompi.webhook'), $this->evento($transaction, 'DECLINED'))->assertOk();

        $this->assertSame(PaymentStatus::Declined, $transaction->refresh()->status);
        $this->assertNull($order->refresh()->paid_at);
        $this->assertSame(OrderStatus::AwaitingPayment, $order->refresh()->status);
    }
}
