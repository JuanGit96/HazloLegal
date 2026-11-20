<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\Shared\Models\Lead;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Models\User;
use Database\Seeders\LawyerPlanSeeder;
use Database\Seeders\LawyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawyerPortalTest extends TestCase
{
    use RefreshDatabase;

    private Lawyer $lawyer;

    private Lawyer $otro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LawyerSeeder::class, LawyerPlanSeeder::class]);

        [$this->lawyer, $this->otro] = Lawyer::query()->orderBy('id')->take(2)->get()->all();
    }

    private function conPlan(Lawyer $lawyer, string $planKey): Lawyer
    {
        $plan = LawyerPlan::query()->where('key', $planKey)->firstOrFail();

        LawyerSubscription::query()->create([
            'lawyer_id' => $lawyer->id,
            'lawyer_plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'billing_period' => BillingPeriod::Monthly,
            'price' => $plan->price_monthly,
            'started_at' => now()->subMonth(),
            'renews_at' => now()->addMonth(),
        ]);

        return $lawyer->refresh();
    }

    private function entrarComo(Lawyer $lawyer): User
    {
        $user = User::factory()->create(['email' => $lawyer->email, 'is_staff' => false]);
        $lawyer->forceFill(['user_id' => $user->id])->saveQuietly();

        $this->actingAs($user);

        return $user;
    }

    public function test_un_abogado_entra_a_su_portal_pero_no_al_de_administracion(): void
    {
        $this->entrarComo($this->lawyer);

        $this->get(route('filament.lawyer.pages.dashboard'))->assertOk();

        // El panel de administración muestra pedidos, ingresos y clientes de
        // todos: un abogado suscrito no tiene nada que hacer ahí.
        $this->get(route('filament.admin.resources.orders.index'))->assertForbidden();
    }

    public function test_un_usuario_del_equipo_no_entra_al_portal_de_abogados(): void
    {
        $this->actingAs(User::factory()->create(['is_staff' => true]));

        $this->get(route('filament.lawyer.pages.dashboard'))->assertForbidden();
    }

    public function test_solo_ve_los_casos_que_el_mismo_gano(): void
    {
        $mio = LegalCase::query()->create([
            'reference' => 'CC-2026-00001',
            'client_name' => 'Cliente Mío',
            'client_email' => 'mio@example.com',
            'client_phone' => '3001112222',
            'practice_area' => PracticeArea::cases()[0]->value,
            'title' => 'Un caso que acepté primero',
            'description' => 'Descripción del caso que sí me corresponde.',
            'status' => CaseStatus::Assigned,
            'assigned_lawyer_id' => $this->lawyer->id,
            'assigned_at' => now(),
        ]);

        $ajeno = LegalCase::query()->create([
            'reference' => 'CC-2026-00002',
            'client_name' => 'Cliente Ajeno',
            'client_email' => 'ajeno@example.com',
            'client_phone' => '3003334444',
            'practice_area' => PracticeArea::cases()[0]->value,
            'title' => 'Un caso que ganó otro abogado',
            'description' => 'Descripción del caso que no me corresponde.',
            'status' => CaseStatus::Assigned,
            'assigned_lawyer_id' => $this->otro->id,
            'assigned_at' => now(),
        ]);

        $this->entrarComo($this->lawyer);

        $this->get(route('filament.lawyer.resources.my-cases.index'))
            ->assertOk()
            ->assertSee($mio->reference)
            ->assertDontSee($ajeno->reference)
            ->assertDontSee('ajeno@example.com');

        // Ni siquiera adivinando la URL: el caso del otro no se abre.
        $this->get(route('filament.lawyer.resources.my-cases.view', $ajeno))->assertNotFound();
        $this->get(route('filament.lawyer.resources.my-cases.view', $mio))->assertOk();
    }

    public function test_sin_la_caracteristica_en_el_plan_no_hay_contactos(): void
    {
        // El plan de entrada no incluye acceso a contactos.
        $this->conPlan($this->lawyer, 'directorio');
        $this->entrarComo($this->lawyer);

        $this->get(route('filament.lawyer.resources.my-leads.index'))->assertForbidden();
    }

    public function test_solo_ve_contactos_que_autorizaron_y_de_sus_areas(): void
    {
        $this->conPlan($this->lawyer, 'crecimiento');

        $miArea = collect($this->lawyer->practice_areas)->first();
        $miArea = $miArea instanceof PracticeArea ? $miArea->value : (string) $miArea;

        $autorizado = Lead::query()->create([
            'email' => 'autorizo@example.com',
            'name' => 'Persona Que Autorizó',
            'source' => 'free_document',
            'practice_area' => $miArea,
            'consent_share_with_lawyers' => true,
            'consented_at' => now(),
        ]);

        $sinAutorizar = Lead::query()->create([
            'email' => 'no-autorizo@example.com',
            'name' => 'Persona Que No Autorizó',
            'source' => 'free_document',
            'practice_area' => $miArea,
            'consent_share_with_lawyers' => false,
        ]);

        $revocado = Lead::query()->create([
            'email' => 'revoque@example.com',
            'name' => 'Persona Que Revocó',
            'source' => 'free_document',
            'practice_area' => $miArea,
            'consent_share_with_lawyers' => true,
            'consented_at' => now()->subMonth(),
            'consent_revoked_at' => now(),
        ]);

        $this->entrarComo($this->lawyer);

        $response = $this->get(route('filament.lawyer.resources.my-leads.index'))->assertOk();

        $response->assertSee('Persona Que');
        $response->assertDontSee($sinAutorizar->email);
        $response->assertDontSee($revocado->email);

        // Ni siquiera el que sí autorizó enseña su correo en el listado:
        // hay que abrirlo, y esa apertura queda registrada.
        $response->assertDontSee($autorizado->email);
    }

    public function test_el_abogado_edita_su_perfil_pero_no_su_tarjeta_profesional(): void
    {
        $this->conPlan($this->lawyer, 'crecimiento');
        $this->entrarComo($this->lawyer);

        $this->get(route('filament.lawyer.pages.my-profile'))
            ->assertOk()
            ->assertSee('Mi perfil público')
            // La tarjeta profesional es dato interno: ni se publica ni se
            // edita desde aquí.
            ->assertDontSee('Tarjeta profesional')
            ->assertDontSee($this->lawyer->professional_card);
    }

    public function test_la_suscripcion_dice_que_incluye_y_que_no(): void
    {
        $this->conPlan($this->lawyer, 'directorio');
        $this->entrarComo($this->lawyer);

        $this->get(route('filament.lawyer.pages.my-subscription'))
            ->assertOk()
            ->assertSee('Perfil público en el directorio')
            ->assertSee('Acceso a contactos');
    }

    public function test_el_portal_exige_autenticacion(): void
    {
        $this->get(route('filament.lawyer.pages.dashboard'))
            ->assertRedirect(route('filament.lawyer.auth.login'));
    }
}
