<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Content\Enums\PostStatus;
use App\Domain\Content\Models\Post;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Mail\SubscriptionScheduledMail;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Services\SubscriptionService;
use App\Filament\Lawyer\Pages\CancelSubscription;
use App\Models\User;
use Database\Seeders\LawyerPlanSeeder;
use Database\Seeders\LawyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bajarse de plan e irse.
 *
 * La regla que gobierna todo esto: subir cambia el plan hoy porque hoy se
 * cobra la diferencia; bajar y cancelar entran el día de la renovación,
 * porque el periodo ya está pagado. Quitarle antes de tiempo lo que compró
 * es cobrarle por algo que no se entrega, y se nota.
 */
class SubscriptionChangesTest extends TestCase
{
    use RefreshDatabase;

    private Lawyer $lawyer;

    private LawyerPlan $caro;

    private LawyerPlan $barato;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'wompi.enabled' => true,
            'wompi.mode' => 'sandbox',
            'wompi.public_key' => 'pub_test_llave',
            'wompi.private_key' => 'prv_test_llave',
            'wompi.integrity_secret' => 'test_integrity_secret',
        ]);

        Mail::fake();

        $this->seed([LawyerSeeder::class, LawyerPlanSeeder::class]);

        $this->lawyer = Lawyer::query()->orderBy('id')->firstOrFail();
        $this->caro = LawyerPlan::query()->where('key', 'destacado')->firstOrFail();
        $this->barato = LawyerPlan::query()->where('key', 'directorio')->firstOrFail();
    }

    private function suscripcion(array $extra = []): LawyerSubscription
    {
        $this->lawyer->forceFill(['is_published' => true, 'is_featured' => true])->saveQuietly();

        return LawyerSubscription::query()->create([
            'lawyer_id' => $this->lawyer->id,
            'lawyer_plan_id' => $this->caro->id,
            'status' => SubscriptionStatus::Active,
            'billing_period' => BillingPeriod::Monthly,
            'price' => $this->caro->price_monthly,
            'started_at' => now()->subMonths(4),
            'renews_at' => now()->addDays(12),
            ...$extra,
        ]);
    }

    private function entrar(): User
    {
        $user = User::factory()->create(['email' => $this->lawyer->email, 'is_staff' => false]);
        $this->lawyer->forceFill(['user_id' => $user->id])->saveQuietly();

        $this->actingAs($user);

        return $user;
    }

    // -------------------------------------------------- Bajarse de plan

    public function test_bajarse_de_plan_no_le_quita_nada_hoy(): void
    {
        $subscription = $this->suscripcion();

        app(SubscriptionService::class)->scheduleChange($subscription, $this->barato);

        $subscription->refresh();

        $this->assertSame($this->caro->id, (int) $subscription->lawyer_plan_id);
        $this->assertSame($this->barato->id, (int) $subscription->scheduled_plan_id);
        $this->assertSame($this->caro->price_monthly, (int) $subscription->price);

        // Lo que compró lo conserva: el destacado sigue encendido hasta que
        // llegue la renovación.
        $this->assertTrue((bool) $this->lawyer->fresh()->is_featured);
        $this->assertTrue((bool) $this->lawyer->fresh()->is_published);
    }

    public function test_la_bajada_entra_el_dia_de_la_renovacion(): void
    {
        $subscription = $this->suscripcion(['renews_at' => now()->subDay()]);

        app(SubscriptionService::class)->scheduleChange($subscription, $this->barato);
        app(SubscriptionService::class)->applyScheduled($subscription->refresh());

        $subscription->refresh();

        $this->assertSame($this->barato->id, (int) $subscription->lawyer_plan_id);
        $this->assertNull($subscription->scheduled_plan_id);
        $this->assertSame($this->barato->price_monthly, (int) $subscription->price);

        // El destacado era del plan caro y se apaga con él.
        $this->assertFalse((bool) $this->lawyer->fresh()->is_featured);
        $this->assertTrue((bool) $this->lawyer->fresh()->is_published);
    }

    public function test_el_descuento_pactado_sigue_corriendo_sobre_el_plan_nuevo(): void
    {
        $subscription = $this->suscripcion([
            'renews_at' => now()->subDay(),
            'discount_percentage' => 50,
            'discount_ends_at' => now()->addMonths(2),
        ]);

        app(SubscriptionService::class)->scheduleChange($subscription, $this->barato);
        app(SubscriptionService::class)->applyScheduled($subscription->refresh());

        // Se pactó «50 % menos durante N periodos», no una cifra: el
        // descuento sigue, pero sobre el plan al que se bajó. La mitad de
        // $50.000 son $25.000, que se escribe $24.900 como todos los
        // precios de la casa.
        $this->assertSame(24_900, (int) $subscription->refresh()->price);
        $this->assertSame(50_000, $this->barato->price_monthly);
    }

    public function test_el_cobro_diario_le_cobra_el_plan_nuevo_a_quien_se_bajo(): void
    {
        $this->fingirCobro();

        $subscription = $this->suscripcion([
            'renews_at' => now()->subDay(),
            'payment_source_id' => '422145',
            'recurring_authorized_at' => now()->subMonths(3),
        ]);

        app(SubscriptionService::class)->scheduleChange($subscription, $this->barato);

        $this->artisan('hl:suscripciones-cobrar')->assertSuccessful();

        $this->assertSame(
            $this->barato->price_monthly * 100,
            (int) PaymentTransaction::query()->sole()->amount_in_cents,
        );
    }

    // -------------------------------------------------- Irse

    public function test_cancelar_conserva_el_perfil_hasta_el_final_de_lo_pagado(): void
    {
        $subscription = $this->suscripcion();

        app(SubscriptionService::class)->scheduleCancellation($subscription, 'caro');

        $subscription->refresh();

        $this->assertNotNull($subscription->ends_at);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertSame('caro', $subscription->cancellation_reason);
        $this->assertTrue((bool) $this->lawyer->fresh()->is_published);
    }

    public function test_a_quien_se_va_no_se_le_cobra(): void
    {
        $this->fingirCobro();

        $subscription = $this->suscripcion([
            'renews_at' => now()->subDay(),
            'payment_source_id' => '422145',
            'recurring_authorized_at' => now()->subMonths(3),
        ]);

        app(SubscriptionService::class)->scheduleCancellation($subscription, 'caro');

        $this->artisan('hl:suscripciones-cobrar')
            ->expectsOutputToContain('se va hoy')
            ->assertSuccessful();

        $this->assertSame(0, PaymentTransaction::query()->count());
        $this->assertSame(SubscriptionStatus::Cancelled, $subscription->refresh()->status);
        $this->assertFalse((bool) $this->lawyer->fresh()->is_published);
    }

    public function test_deshacer_deja_todo_como_estaba(): void
    {
        $subscription = $this->suscripcion();

        $service = app(SubscriptionService::class);
        $service->scheduleCancellation($subscription, 'caro');
        $service->resume($subscription->refresh());

        $subscription->refresh();

        $this->assertNull($subscription->ends_at);
        $this->assertNull($subscription->scheduled_plan_id);
        $this->assertNull($subscription->cancellation_reason);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
    }

    public function test_el_cambio_se_avisa_por_correo_el_mismo_dia(): void
    {
        $subscription = $this->suscripcion();

        app(SubscriptionService::class)->scheduleCancellation($subscription, 'caro');

        Mail::assertSent(SubscriptionScheduledMail::class, fn ($mail) => $mail->hasTo($this->lawyer->email));
    }

    // -------------------------------------------------- La pantalla

    public function test_la_pantalla_de_salida_solo_ofrece_planes_mas_baratos(): void
    {
        $this->suscripcion(['lawyer_plan_id' => LawyerPlan::query()->where('key', 'crecimiento')->value('id')]);
        $this->entrar();

        Livewire::test(CancelSubscription::class)
            ->assertSee('Directorio')
            ->assertDontSee('Pasarme a Destacado');
    }

    public function test_el_servidor_rechaza_bajarse_a_un_plan_mas_caro(): void
    {
        $subscription = $this->suscripcion(['lawyer_plan_id' => $this->barato->id]);
        $this->entrar();

        // Esconder la opción no es impedirla: por aquí se cambia lo que se
        // cobra, así que el servidor tiene que decir que no.
        Livewire::test(CancelSubscription::class)
            ->set('plan', $this->caro->id)
            ->call('confirmChange');

        $this->assertNull($subscription->refresh()->scheduled_plan_id);
    }

    public function test_no_se_cancela_sin_decir_por_que(): void
    {
        $subscription = $this->suscripcion();
        $this->entrar();

        Livewire::test(CancelSubscription::class)
            ->set('motivo', 'inventado')
            ->call('confirmCancellation');

        $this->assertNull($subscription->refresh()->ends_at);
    }

    public function test_la_pantalla_avisa_del_cambio_pedido(): void
    {
        $subscription = $this->suscripcion();
        $this->entrar();

        app(SubscriptionService::class)->scheduleChange($subscription, $this->barato);

        // Lo primero que tiene que ver al entrar es qué cambia y cuándo.
        $this->get('/portal/my-subscription')
            ->assertOk()
            ->assertSee('pasas al plan Directorio', escape: false);
    }

    public function test_la_pantalla_avisa_de_la_cancelacion_pedida(): void
    {
        $subscription = $this->suscripcion();
        $this->entrar();

        app(SubscriptionService::class)->scheduleCancellation($subscription, 'caro');

        $this->get('/portal/my-subscription')
            ->assertOk()
            ->assertSee('Tu plan termina el', escape: false);
    }

    // -------------------------------------------------- Lo que se queda

    public function test_la_guia_sigue_publicada_cuando_el_abogado_se_va(): void
    {
        $subscription = $this->suscripcion();

        $post = Post::query()->create([
            'title' => 'Cómo pedir la reliquidación de la pensión',
            'slug' => 'reliquidacion-pension',
            'body' => '<p>El procedimiento ante Colpensiones.</p>',
            'excerpt' => 'Qué pedir y a quién.',
            'author_lawyer_id' => $this->lawyer->id,
            'status' => PostStatus::Published,
            'published_at' => now()->subMonth(),
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee($this->lawyer->name);

        app(SubscriptionService::class)->scheduleCancellation($subscription, 'caro');
        app(SubscriptionService::class)->applyScheduled($subscription->refresh()->forceFill(['ends_at' => now()->subDay()]));

        // La guía se queda, con su firma. Lo que desaparece es el enlace a
        // una ficha que ya no existe.
        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee($this->lawyer->name)
            ->assertDontSee('Ver su perfil');
    }

    /** Lo mínimo para que el comando diario pueda abrir un cobro. */
    private function fingirCobro(): void
    {
        Http::fake([
            '*/transactions' => Http::response(['data' => ['id' => '1234-tx', 'status' => 'PENDING']]),
            '*' => Http::response(['data' => []]),
        ]);
    }
}
