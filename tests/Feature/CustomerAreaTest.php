<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Orders\Mail\CustomerAccessMail;
use App\Domain\Orders\Models\Order;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\ServiceAddonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([ServiceAddonSeeder::class, LegalDocumentSeeder::class, PricingSeeder::class]);
    }

    private function pedidoDe(string $email): Order
    {
        return Order::query()->create([
            'reference' => 'HL-2026-0'.random_int(1000, 9999),
            'access_token' => bin2hex(random_bytes(16)),
            'legal_document_id' => LegalDocument::query()->firstOrFail()->id,
            'status' => 'delivered',
            'tier' => 'free',
            'customer_name' => 'Lucía Fernanda Cano',
            'customer_email' => $email,
            'customer_phone' => '3001234567',
            'customer_document_type' => 'CC',
            'case_summary' => 'Un caso de prueba.',
            'amount' => 0,
        ]);
    }

    public function test_el_cliente_pide_un_enlace_y_le_llega(): void
    {
        Mail::fake();
        $this->pedidoDe('lucia@example.com');

        $this->post(route('account.request.send'), ['email' => 'lucia@example.com'])
            ->assertSessionHas('accountStatus');

        Mail::assertSent(CustomerAccessMail::class);
    }

    public function test_un_correo_sin_pedidos_recibe_la_misma_respuesta(): void
    {
        Mail::fake();

        // Decir «ese correo no tiene pedidos» dejaría averiguar quién nos
        // compró y quién no.
        $this->post(route('account.request.send'), ['email' => 'desconocido@example.com'])
            ->assertSessionHas('accountStatus');

        Mail::assertNothingSent();
    }

    public function test_el_enlace_firmado_abre_los_tramites(): void
    {
        $order = $this->pedidoDe('lucia@example.com');

        $url = URL::temporarySignedRoute('account.show', now()->addMinutes(30), ['email' => 'lucia@example.com']);

        $this->get($url)
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('lucia@example.com');
    }

    public function test_sin_firma_no_se_entra(): void
    {
        $this->pedidoDe('lucia@example.com');

        $this->get(route('account.show', ['email' => 'lucia@example.com']))
            ->assertForbidden();
    }

    public function test_un_enlace_vencido_no_sirve(): void
    {
        $this->pedidoDe('lucia@example.com');

        $url = URL::temporarySignedRoute('account.show', now()->subMinute(), ['email' => 'lucia@example.com']);

        $this->get($url)->assertForbidden();
    }

    public function test_no_se_ven_los_tramites_de_otro(): void
    {
        $mio = $this->pedidoDe('lucia@example.com');
        $ajeno = $this->pedidoDe('otro@example.com');

        $url = URL::temporarySignedRoute('account.show', now()->addMinutes(30), ['email' => 'lucia@example.com']);

        $this->get($url)
            ->assertOk()
            ->assertSee($mio->reference)
            ->assertDontSee($ajeno->reference);
    }
}
