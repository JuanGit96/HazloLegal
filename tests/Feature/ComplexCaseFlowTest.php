<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Enums\CaseUrgency;
use App\Domain\LegalCases\Mail\CaseOfferMail;
use App\Domain\LegalCases\Mail\CaseReceivedMail;
use App\Domain\LegalCases\Mail\CaseTakenByOtherMail;
use App\Domain\LegalCases\Mail\CaseWonMail;
use App\Domain\LegalCases\Mail\LawyerAssignedMail;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use Database\Seeders\LawyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ComplexCaseFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LawyerSeeder::class);
    }

    private function submitCase(array $overrides = []): LegalCase
    {
        $this->post(route('cases.store'), [
            'client_name' => 'Álvaro Sepúlveda Rincón',
            'client_email' => 'alvaro@example.com',
            'client_phone' => '3214567890',
            'client_city' => 'Bogotá',
            'practice_area' => PracticeArea::Laboral->value,
            'title' => 'Demanda laboral por despido indirecto y acoso',
            'description' => 'Renuncié en septiembre después de seis meses de presión del jefe directo. Tengo correos, testigos y los desprendibles de pago. Quiero reclamar la indemnización por despido indirecto y evaluar el acoso laboral.',
            'urgency' => CaseUrgency::High->value,
            'accepts_privacy' => '1',
            ...$overrides,
        ]);

        return LegalCase::query()->where('client_email', $overrides['client_email'] ?? 'alvaro@example.com')->firstOrFail();
    }

    public function test_al_enviar_un_caso_se_acusa_recibo_y_se_reparte_a_los_abogados(): void
    {
        Mail::fake();

        $case = $this->submitCase();

        $this->assertStringStartsWith('CC-', $case->reference);
        $this->assertSame(CaseStatus::Dispatched, $case->status);
        $this->assertNotNull($case->dispatched_at);
        $this->assertNotNull($case->expires_at);
        $this->assertGreaterThan(0, $case->offers_sent);

        // El cliente recibe acuse de recibo.
        Mail::assertSent(CaseReceivedMail::class, 1);

        // Cada abogado invitado recibe la oferta con sus botones.
        Mail::assertSent(CaseOfferMail::class, $case->offers()->count());

        $this->assertTrue(
            $case->offers()->get()->every(fn (CaseOffer $o) => $o->status === CaseOfferStatus::Pending),
        );
        $this->assertTrue($case->offers()->get()->every(fn (CaseOffer $o) => filled($o->token)));
    }

    public function test_el_especialista_del_area_recibe_la_invitacion_primero(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $specialist = Lawyer::query()->where('email', 'maria.ocampo@hazlolegal.co')->firstOrFail();

        $this->assertTrue(
            $case->offers()->where('lawyer_id', $specialist->id)->exists(),
            'La laboralista debe estar entre los invitados de un caso laboral.',
        );
    }

    public function test_el_primer_abogado_que_acepta_se_queda_con_el_caso(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offers = $case->offers()->with('lawyer')->get();
        $winnerOffer = $offers->first();
        $loserOffer = $offers->skip(1)->first();

        $this->get(route('cases.offer.respond', ['token' => $winnerOffer->token, 'decision' => 'accept']))
            ->assertOk()
            ->assertSee('El caso es tuyo')
            // Al ganador sí se le muestran los datos de contacto.
            ->assertSee($case->client_email);

        $case->refresh();

        $this->assertSame(CaseStatus::Assigned, $case->status);
        $this->assertSame($winnerOffer->lawyer_id, $case->assigned_lawyer_id);
        $this->assertNotNull($case->assigned_at);
        $this->assertSame(CaseOfferStatus::Accepted, $winnerOffer->refresh()->status);

        // El resto queda marcado como "lo tomó otro".
        $this->assertSame(CaseOfferStatus::Superseded, $loserOffer->refresh()->status);

        Mail::assertSent(LawyerAssignedMail::class, 1);
        Mail::assertSent(CaseWonMail::class, 1);
        Mail::assertSent(CaseTakenByOtherMail::class, $offers->count() - 1);
    }

    public function test_el_segundo_abogado_que_llega_ve_que_ya_fue_tomado_y_no_recibe_datos_del_cliente(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offers = $case->offers()->get();

        $this->get(route('cases.offer.respond', ['token' => $offers->first()->token, 'decision' => 'accept']));

        $response = $this->get(route('cases.offer.respond', [
            'token' => $offers->skip(1)->first()->token,
            'decision' => 'accept',
        ]));

        $response->assertOk()
            ->assertSee('Otro abogado llegó primero')
            ->assertDontSee($case->client_email)
            ->assertDontSee($case->client_phone);

        // El caso sigue asignado al primero: no se reasigna.
        $this->assertSame($offers->first()->lawyer_id, $case->refresh()->assigned_lawyer_id);
    }

    public function test_recargar_el_enlace_de_aceptacion_no_rompe_nada(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offer = $case->offers()->first();

        $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']));
        $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']))
            ->assertOk()
            ->assertSee('ya estaba asignado a ti');

        // No se duplican los correos de asignación.
        Mail::assertSent(LawyerAssignedMail::class, 1);
        $this->assertSame(1, $case->offers()->where('status', CaseOfferStatus::Accepted)->count());
    }

    public function test_un_abogado_puede_rechazar_el_caso_con_motivo(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offer = $case->offers()->first();

        $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'decline']))
            ->assertOk()
            ->assertSee('no tomas este caso');

        $this->post(route('cases.offer.decline', $offer->token), [
            'decline_reason' => 'No tengo cupo esta semana.',
        ])->assertOk();

        $offer->refresh();

        $this->assertSame(CaseOfferStatus::Declined, $offer->status);
        $this->assertSame('No tengo cupo esta semana.', $offer->decline_reason);

        // Rechazar no asigna el caso ni lo cierra.
        $this->assertNull($case->refresh()->assigned_lawyer_id);
        $this->assertSame(CaseStatus::Dispatched, $case->status);
    }

    public function test_un_token_invalido_devuelve_404(): void
    {
        $this->get(route('cases.offer.respond', ['token' => 'token-que-no-existe', 'decision' => 'accept']))
            ->assertNotFound();
    }

    public function test_el_formulario_exige_autorizacion_de_datos(): void
    {
        $this->post(route('cases.store'), [
            'client_name' => 'Persona De Prueba',
            'client_email' => 'prueba@example.com',
            'client_phone' => '3214567890',
            'practice_area' => PracticeArea::Laboral->value,
            'title' => 'Un caso de prueba cualquiera',
            'description' => str_repeat('Descripción suficientemente larga para pasar la validación mínima. ', 2),
            'urgency' => CaseUrgency::Normal->value,
        ])->assertSessionHasErrors('accepts_privacy');

        $this->assertDatabaseCount('legal_cases', 0);
    }
}
