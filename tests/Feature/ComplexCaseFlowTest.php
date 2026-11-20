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
use App\Domain\LegalCases\Mail\CaseShortlistMail;
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

    public function test_aceptar_postula_al_abogado_y_avisa_al_cliente(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offer = $case->offers()->with('lawyer')->first();

        $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']))
            ->assertOk()
            ->assertSee('Te postulaste')
            // Postularse no da derecho a los datos del cliente: esos salen
            // solo cuando el cliente elige.
            ->assertDontSee($case->client_email)
            ->assertDontSee($case->client_phone);

        $case->refresh();

        $this->assertSame(CaseStatus::Shortlisted, $case->status);
        $this->assertNull($case->assigned_lawyer_id);
        $this->assertNotNull($case->shortlisted_at);
        $this->assertSame(CaseOfferStatus::Accepted, $offer->refresh()->status);

        Mail::assertSent(CaseShortlistMail::class, 1);
        Mail::assertNotSent(CaseWonMail::class);
    }

    public function test_varios_abogados_pueden_postularse_al_mismo_caso(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offers = $case->offers()->get()->take(3);

        foreach ($offers as $offer) {
            $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']))->assertOk();
        }

        $this->assertSame(3, $case->refresh()->acceptedOffers()->count());

        // Un correo al cliente, no uno por postulado: lo contrario sería ruido.
        Mail::assertSent(CaseShortlistMail::class, 1);
    }

    public function test_el_cliente_ve_los_postulados_y_elige(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offers = $case->offers()->with('lawyer')->get()->take(2);

        foreach ($offers as $offer) {
            $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']));
        }

        $case->refresh();
        $elegido = $offers->first();

        $this->get($case->clientUrl())
            ->assertOk()
            ->assertSee('Elige a tu abogado')
            ->assertSee($elegido->lawyer->name);

        $this->post(route('cases.pick', $case->reference), [
            't' => $case->client_token,
            'offer' => $elegido->id,
        ])->assertRedirect($case->refresh()->clientUrl());

        $case->refresh();

        $this->assertSame(CaseStatus::Assigned, $case->status);
        $this->assertSame($elegido->lawyer_id, $case->assigned_lawyer_id);
        $this->assertSame(CaseOfferStatus::Superseded, $offers->skip(1)->first()->refresh()->status);

        Mail::assertSent(LawyerAssignedMail::class, 1);
        Mail::assertSent(CaseWonMail::class, 1);
    }

    public function test_la_referencia_sola_no_abre_el_caso_de_otro(): void
    {
        Mail::fake();

        // CC-2026-00001 es correlativa: sin la llave, cambiando un número
        // cualquiera leería el caso ajeno.
        $case = $this->submitCase();

        $this->get(route('cases.choose', $case->reference))->assertForbidden();
        $this->get($case->clientUrl())->assertOk();
    }

    public function test_el_cliente_no_puede_elegir_dos_veces(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offers = $case->offers()->get()->take(2);

        foreach ($offers as $offer) {
            $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']));
        }

        $case->refresh();

        $this->post(route('cases.pick', $case->reference), [
            't' => $case->client_token,
            'offer' => $offers->first()->id,
        ]);

        // Un doble clic no puede dejar el caso con dos abogados.
        $this->post(route('cases.pick', $case->reference), [
            't' => $case->client_token,
            'offer' => $offers->skip(1)->first()->id,
        ]);

        $this->assertSame($offers->first()->lawyer_id, $case->refresh()->assigned_lawyer_id);
        Mail::assertSent(CaseWonMail::class, 1);
    }

    public function test_quien_no_fue_elegido_nunca_ve_los_datos_del_cliente(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offers = $case->offers()->get()->take(2);

        foreach ($offers as $offer) {
            $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']));
        }

        $case->refresh();
        $descartado = $offers->skip(1)->first();

        $this->post(route('cases.pick', $case->reference), [
            't' => $case->client_token,
            'offer' => $offers->first()->id,
        ]);

        $this->get(route('cases.offer.respond', ['token' => $descartado->token, 'decision' => 'accept']))
            ->assertOk()
            ->assertSee('eligió a otro colega')
            ->assertDontSee($case->client_email)
            ->assertDontSee($case->client_phone);

        // Se avisa a todos los invitados que el caso se cerró, no solo a los
        // que se habían postulado: el resto deja de esperarlo.
        Mail::assertSent(
            CaseTakenByOtherMail::class,
            $case->offers()->where('status', CaseOfferStatus::Superseded)->count(),
        );
    }

    public function test_recargar_el_enlace_de_postulacion_no_duplica_nada(): void
    {
        Mail::fake();

        $case = $this->submitCase();
        $offer = $case->offers()->first();

        $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']));
        $this->get(route('cases.offer.respond', ['token' => $offer->token, 'decision' => 'accept']))->assertOk();

        $this->assertSame(1, $case->refresh()->acceptedOffers()->count());
        Mail::assertSent(CaseShortlistMail::class, 1);
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
