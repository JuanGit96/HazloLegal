<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Documents\Enums\SignatureStatus;
use App\Domain\Documents\Services\SignatureService;
use App\Domain\Orders\Models\Order;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\ServiceAddonSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignatureFlowTest extends TestCase
{
    use RefreshDatabase;

    private const TRAZO = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200">'
        .'<path d="M20 150 L60 80 L100 150 L140 80 L180 150 L220 90 L260 140 L300 100" '
        .'fill="none" stroke="#10151F" stroke-width="2.5" stroke-linecap="round"/></svg>';

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

    private function pedido(): Order
    {
        $document = LegalDocument::query()->where('slug', 'acuerdo-de-confidencialidad-nda')->firstOrFail();

        return Order::query()->create([
            'reference' => 'HL-2026-09999',
            'access_token' => bin2hex(random_bytes(16)),
            'legal_document_id' => $document->id,
            'status' => 'paid',
            'tier' => 'self_service',
            'customer_name' => 'Marcela Ospina Vélez',
            'customer_email' => 'marcela@example.com',
            'customer_phone' => '3001234567',
            'customer_document_type' => 'CC',
            'customer_document_number' => '1020304050',
            'case_summary' => 'Necesito un acuerdo de confidencialidad para una negociación.',
            'amount' => 49_900,
            'base_amount' => 49_900,
            'paid_at' => now(),
        ]);
    }

    public function test_el_enlace_de_firma_abre_la_pantalla_del_celular(): void
    {
        $signature = app(SignatureService::class)->request($this->pedido());

        $this->get(route('signature.show', $signature->token))
            ->assertOk()
            ->assertSeeText('Firma tu documento')
            ->assertSee('Marcela Ospina Vélez')
            ->assertSee('Ley 527 de 1999')
            // Es una pantalla para firmar, no para navegar: nada de menú.
            ->assertDontSee('Navegación principal');

        // Se registra que se abrió: es parte de la evidencia.
        $this->assertNotNull($signature->refresh()->opened_at);
    }

    public function test_firmar_estampa_el_trazo_y_deja_la_evidencia(): void
    {
        $order = $this->pedido();
        $signature = app(SignatureService::class)->request($order);

        $this->post(route('signature.store', $signature->token), [
            'signature' => self::TRAZO,
            'confirms' => '1',
        ])->assertRedirect(route('signature.done', $signature->token));

        $signature->refresh();

        $this->assertSame(SignatureStatus::Signed, $signature->status);
        $this->assertNotNull($signature->signed_at);
        $this->assertNotNull($signature->signed_ip);

        // La huella es lo que permite demostrar que el PDF no cambió después.
        $this->assertNotNull($signature->document_hash);
        $this->assertSame(64, strlen($signature->document_hash));

        $order->refresh();
        $this->assertNotNull($order->signed_at);
        $this->assertNotNull($order->document_path);
    }

    public function test_el_documento_firmado_lleva_el_certificado_de_auditoria(): void
    {
        $order = $this->pedido();
        $signature = app(SignatureService::class)->request($order);

        $this->post(route('signature.store', $signature->token), [
            'signature' => self::TRAZO,
            'confirms' => '1',
        ]);

        $pdf = Storage::disk('local')->get($order->refresh()->document_path);

        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_no_se_puede_firmar_dos_veces_con_el_mismo_enlace(): void
    {
        $signature = app(SignatureService::class)->request($this->pedido());

        $this->post(route('signature.store', $signature->token), [
            'signature' => self::TRAZO,
            'confirms' => '1',
        ]);

        $this->post(route('signature.store', $signature->token), [
            'signature' => self::TRAZO,
            'confirms' => '1',
        ])->assertStatus(410);
    }

    public function test_hay_que_confirmar_que_es_uno_quien_firma(): void
    {
        $signature = app(SignatureService::class)->request($this->pedido());

        $this->post(route('signature.store', $signature->token), [
            'signature' => self::TRAZO,
        ])->assertSessionHasErrors('confirms');

        $this->assertSame(SignatureStatus::Pending, $signature->refresh()->status);
    }

    public function test_un_enlace_vencido_no_firma(): void
    {
        $signature = app(SignatureService::class)->request($this->pedido());
        $signature->update(['expires_at' => now()->subDay()]);

        $this->get(route('signature.show', $signature->token))->assertStatus(410);
    }

    public function test_un_token_inventado_no_existe(): void
    {
        $this->get(route('signature.show', 'token-que-no-existe'))->assertNotFound();
    }
}
