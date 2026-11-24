<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Models\SubscriptionPromo;
use App\Domain\Subscriptions\Services\SubscriptionPromoService;
use App\Models\User;
use Database\Seeders\LawyerPlanSeeder;
use Database\Seeders\LawyerSeeder;
use Database\Seeders\SubscriptionPromoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Las dos puertas de entrada a la suscripción.
 *
 * Con invitación: unos meses gratis. Sin ella: el precio de lanzamiento. En
 * las dos se registra y se valida la tarjeta antes de empezar, porque un
 * abogado que se va el día del primer cobro no era un suscrito, era un mes
 * de perfil publicado gratis.
 */
class SubscriptionPromoTest extends TestCase
{
    use RefreshDatabase;

    private Lawyer $lawyer;

    private LawyerPlan $plan;

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

        Cache::flush();

        $this->seed([LawyerSeeder::class, LawyerPlanSeeder::class, SubscriptionPromoSeeder::class]);

        $this->lawyer = Lawyer::query()->orderBy('id')->firstOrFail();
        $this->plan = LawyerPlan::query()->where('key', 'directorio')->firstOrFail();
    }

    private function entrarComo(Lawyer $lawyer): void
    {
        $user = User::factory()->create(['email' => $lawyer->email, 'is_staff' => false]);
        $lawyer->forceFill(['user_id' => $user->id])->saveQuietly();

        $this->actingAs($user);
    }

    /** Wompi responde lo que responde de verdad: comprobado contra el sandbox. */
    private function fingirWompi(bool $tarjetaValida = true): void
    {
        Http::fake([
            '*/merchants/*' => Http::response([
                'data' => [
                    'presigned_acceptance' => [
                        'acceptance_token' => 'tok_aceptacion',
                        'permalink' => 'https://wompi.com/reglamento.pdf',
                    ],
                    'presigned_personal_data_auth' => [
                        'acceptance_token' => 'tok_datos',
                        'permalink' => 'https://wompi.com/datos.pdf',
                    ],
                ],
            ]),
            '*/payment_sources' => $tarjetaValida
                ? Http::response([
                    'data' => [
                        'id' => 422145,
                        'status' => 'AVAILABLE',
                        'public_data' => ['brand' => 'VISA', 'last_four' => '4242'],
                    ],
                ])
                : Http::response([
                    'error' => ['type' => 'INPUT_VALIDATION_ERROR', 'messages' => ['number' => ['Tarjeta inválida.']]],
                ], 422),
            '*/transactions' => Http::response([
                'data' => ['id' => '12264626-1795465102-18981', 'status' => 'PENDING'],
            ]),
        ]);
    }

    // -------------------------------------------------- El cálculo

    public function test_sin_codigo_rige_el_precio_de_lanzamiento(): void
    {
        $quote = app(SubscriptionPromoService::class)->quote(
            $this->plan,
            BillingPeriod::Monthly,
            app(SubscriptionPromoService::class)->resolve($this->plan),
        );

        $this->assertSame(50, $quote->discountPercentage);
        $this->assertSame(3, $quote->discountMonths);
        $this->assertSame(0, $quote->freeMonths);

        // Se cobra hoy, y menos que el precio de lista.
        $this->assertGreaterThan(0, $quote->dueToday);
        $this->assertLessThan($quote->listPrice, $quote->dueToday);

        // Y el precio de lista vuelve, con fecha.
        $this->assertSame($this->plan->price_monthly, $quote->recurringPrice);
        $this->assertNotNull($quote->fullPriceFrom);
    }

    public function test_con_codigo_de_invitacion_los_primeros_meses_son_gratis(): void
    {
        $promos = app(SubscriptionPromoService::class);
        $quote = $promos->quote($this->plan, BillingPeriod::Monthly, $promos->resolve($this->plan, 'FUNDADORES'));

        $this->assertSame(3, $quote->freeMonths);
        $this->assertSame(0, $quote->dueToday);
        $this->assertTrue($quote->startsFree());
        $this->assertSame($this->plan->price_monthly, $quote->recurringPrice);
        $this->assertTrue($quote->firstChargeAt->isFuture());
    }

    public function test_el_codigo_no_distingue_mayusculas_ni_espacios(): void
    {
        $this->assertNotNull(app(SubscriptionPromoService::class)->byCode('  fundadores  '));
    }

    public function test_una_promocion_agotada_no_se_aplica(): void
    {
        SubscriptionPromo::query()->where('code', 'FUNDADORES')
            ->update(['max_redemptions' => 2, 'redeemed_count' => 2]);

        $this->assertNull(app(SubscriptionPromoService::class)->byCode('FUNDADORES'));
    }

    public function test_una_promocion_vencida_no_se_aplica(): void
    {
        SubscriptionPromo::query()->where('code', 'FUNDADORES')
            ->update(['expires_at' => now()->subDay()]);

        $this->assertNull(app(SubscriptionPromoService::class)->byCode('FUNDADORES'));
    }

    // -------------------------------------------------- El enlace

    public function test_el_enlace_de_invitacion_deja_la_oferta_puesta(): void
    {
        $this->get(route('lawyers.invitation', 'FUNDADORES'))
            ->assertRedirect(route('lawyers.join'))
            ->assertSessionHas('invitation_code', 'FUNDADORES');

        $this->withSession(['invitation_code' => 'FUNDADORES'])
            ->get(route('lawyers.join'))
            ->assertOk()
            ->assertSeeText('Tu invitación')
            ->assertSeeText('3 meses')
            // La fecha y el precio de después se dicen desde el primer momento.
            ->assertSeeText('precio normal');
    }

    public function test_un_enlace_muerto_no_deja_al_abogado_en_un_error(): void
    {
        SubscriptionPromo::query()->where('code', 'FUNDADORES')->update(['is_active' => false]);

        $this->get(route('lawyers.invitation', 'FUNDADORES'))
            ->assertRedirect(route('lawyers.join'))
            ->assertSessionHas('paymentError')
            ->assertSessionMissing('invitation_code');
    }

    public function test_quien_llega_sin_enlace_ve_el_precio_de_lanzamiento(): void
    {
        $this->get(route('lawyers.join'))
            ->assertOk()
            ->assertSeeText('Precio de lanzamiento')
            ->assertSeeText('50 % menos');
    }

    public function test_el_codigo_se_guarda_con_el_abogado_al_registrarse(): void
    {
        $this->post(route('lawyers.join.store'), [
            'name' => 'Mariana Ospina Vélez',
            'email' => 'mariana.ospina@example.com',
            'phone' => '3001234567',
            'city' => 'Medellín',
            'kind' => 'individual',
            'professional_card' => '123456',
            'practice_areas' => ['family'],
            'lawyer_plan_id' => $this->plan->id,
            'invitation_code' => 'FUNDADORES',
            'accepts_terms' => '1',
        ])->assertSessionHas('status');

        // Entre registrarse y pagar pasa la verificación a mano de la tarjeta
        // profesional: si la condición viviera en una cookie, se perdería.
        $this->assertSame(
            'FUNDADORES',
            Lawyer::query()->where('email', 'mariana.ospina@example.com')->value('invitation_code'),
        );
    }

    // -------------------------------------------------- La tarjeta

    public function test_la_pantalla_de_pago_dice_que_paga_hoy_y_que_pagara_despues(): void
    {
        $this->fingirWompi();
        $this->entrarComo($this->lawyer);

        $this->get(route('subscription.checkout', ['plan' => $this->plan->id]))
            ->assertOk()
            ->assertSeeText('Hoy pagas')
            ->assertSeeText('Después')
            ->assertSee('name="card_token"', escape: false)
            ->assertSee('name="accepts_recurring"', escape: false)
            // El reglamento de la pasarela y el nuestro, enlazados.
            ->assertSee('https://wompi.com/reglamento.pdf', escape: false);
    }

    public function test_con_meses_gratis_se_valida_la_tarjeta_y_no_se_cobra_nada(): void
    {
        $this->fingirWompi();
        $this->lawyer->forceFill(['invitation_code' => 'FUNDADORES'])->saveQuietly();
        $this->entrarComo($this->lawyer->refresh());

        $this->post(route('subscription.card'), [
            'card_token' => 'tok_test_ejemplo',
            'plan' => $this->plan->id,
            'periodo' => 'monthly',
            'invitacion' => 'FUNDADORES',
            'accepts_recurring' => '1',
        ])->assertRedirect(url('/portal'))->assertSessionHas('status');

        $subscription = LawyerSubscription::query()->where('lawyer_id', $this->lawyer->id)->sole();

        $this->assertSame(SubscriptionStatus::Trialing, $subscription->status);
        $this->assertSame('422145', $subscription->payment_source_id);
        $this->assertSame('VISA ·· 4242', $subscription->cardLabel());
        $this->assertNotNull($subscription->card_validated_at);
        $this->assertNotNull($subscription->recurring_authorized_at);
        $this->assertTrue($subscription->trial_ends_at->isFuture());

        // Nada que cobrar hoy: ni una transacción abierta.
        $this->assertSame(0, PaymentTransaction::query()->count());
    }

    public function test_con_precio_de_lanzamiento_se_cobra_el_descuento_contra_la_tarjeta(): void
    {
        $this->fingirWompi();
        $this->entrarComo($this->lawyer);

        $this->post(route('subscription.card'), [
            'card_token' => 'tok_test_ejemplo',
            'plan' => $this->plan->id,
            'periodo' => 'monthly',
            'accepts_recurring' => '1',
        ])->assertRedirect(url('/portal'))->assertSessionHas('status');

        $subscription = LawyerSubscription::query()->where('lawyer_id', $this->lawyer->id)->sole();
        $transaction = PaymentTransaction::query()->sole();

        $this->assertTrue($subscription->hasStoredCard());
        $this->assertSame(50, (int) $subscription->discount_percentage);

        // Se cobra el precio con descuento, no el de lista.
        $this->assertLessThan($this->plan->price_monthly * 100, (int) $transaction->amount_in_cents);
        $this->assertSame('12264626-1795465102-18981', $transaction->gateway_transaction_id);

        // Y no se da por pagado: eso lo confirma el webhook.
        $this->assertNotSame(SubscriptionStatus::Active, $subscription->status);
    }

    public function test_una_tarjeta_que_la_pasarela_rechaza_no_arranca_la_suscripcion(): void
    {
        $this->fingirWompi(tarjetaValida: false);
        $this->entrarComo($this->lawyer);

        $this->from(route('subscription.checkout', ['plan' => $this->plan->id]))
            ->post(route('subscription.card'), [
                'card_token' => 'tok_test_malo',
                'plan' => $this->plan->id,
                'accepts_recurring' => '1',
            ])
            ->assertRedirect(route('subscription.checkout', ['plan' => $this->plan->id]))
            ->assertSessionHas('paymentError');

        $subscription = LawyerSubscription::query()->where('lawyer_id', $this->lawyer->id)->sole();

        $this->assertFalse($subscription->hasStoredCard());
        $this->assertNull($subscription->card_validated_at);
        $this->assertSame(SubscriptionStatus::PastDue, $subscription->status);
    }

    public function test_sin_autorizacion_expresa_del_cobro_recurrente_no_se_registra_nada(): void
    {
        $this->fingirWompi();
        $this->entrarComo($this->lawyer);

        $this->post(route('subscription.card'), [
            'card_token' => 'tok_test_ejemplo',
            'plan' => $this->plan->id,
        ])->assertSessionHasErrors('accepts_recurring');

        $this->assertSame(0, LawyerSubscription::query()->whereNotNull('payment_source_id')->count());
    }

    public function test_el_cobro_automatico_solo_toca_a_quien_lo_autorizo(): void
    {
        $this->fingirWompi();

        $conTarjeta = LawyerSubscription::query()->create([
            'lawyer_id' => $this->lawyer->id,
            'lawyer_plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::Trialing,
            'billing_period' => BillingPeriod::Monthly,
            'price' => $this->plan->price_monthly,
            'renews_at' => now()->subDay(),
            'payment_source_id' => '422145',
            'recurring_authorized_at' => now()->subMonths(3),
        ]);

        $otro = Lawyer::query()->orderBy('id')->skip(1)->firstOrFail();

        // Quien pagó por Nequi o PSE no dejó tarjeta: a ese se le escribe,
        // no se le cobra.
        LawyerSubscription::query()->create([
            'lawyer_id' => $otro->id,
            'lawyer_plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::Active,
            'billing_period' => BillingPeriod::Monthly,
            'price' => $this->plan->price_monthly,
            'renews_at' => now()->subDay(),
        ]);

        $this->artisan('hl:suscripciones-cobrar')
            ->expectsOutputToContain('1 cobro(s) iniciado(s)')
            ->assertSuccessful();

        $transaction = PaymentTransaction::query()->sole();

        $this->assertSame($conTarjeta->id, (int) $transaction->payable_id);
        $this->assertSame($this->plan->price_monthly * 100, (int) $transaction->amount_in_cents);
    }

    public function test_el_cobro_diario_no_cobra_dos_veces_lo_mismo(): void
    {
        $this->fingirWompi();

        LawyerSubscription::query()->create([
            'lawyer_id' => $this->lawyer->id,
            'lawyer_plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::Active,
            'billing_period' => BillingPeriod::Monthly,
            'price' => $this->plan->price_monthly,
            'renews_at' => now()->subDay(),
            'payment_source_id' => '422145',
            'recurring_authorized_at' => now()->subMonth(),
        ]);

        // Wompi resuelve asíncrono y `renews_at` sigue en el pasado hasta que
        // llega el webhook: si el comando no mirara lo que ya está en vuelo,
        // mañana cobraría otra vez.
        $this->artisan('hl:suscripciones-cobrar')->assertSuccessful();
        $this->assertSame(1, PaymentTransaction::query()->count());

        $this->artisan('hl:suscripciones-cobrar')
            ->expectsOutputToContain('Ya hay un cobro lanzado')
            ->assertSuccessful();

        $this->assertSame(1, PaymentTransaction::query()->count());
    }

    public function test_el_ahorro_de_un_plan_anual_no_se_infla(): void
    {
        $plan = LawyerPlan::query()->where('key', 'crecimiento')->firstOrFail();
        $promos = app(SubscriptionPromoService::class);

        $quote = $promos->quote($plan, BillingPeriod::Annual, $promos->resolve($plan, 'FUNDADORES'));

        // Tres meses gratis de un plan anual se ahorran tres doceavos del
        // año, no el año entero. Inflarlo sería publicidad engañosa.
        $mensual = (int) round($plan->priceFor(BillingPeriod::Annual) / 12);

        $this->assertSame(3 * $mensual, $quote->savings());
        $this->assertLessThan($plan->priceFor(BillingPeriod::Annual), $quote->savings());
    }

    public function test_un_descuento_anual_no_se_anuncia_como_meses(): void
    {
        $promos = app(SubscriptionPromoService::class);
        $quote = $promos->quote($this->plan, BillingPeriod::Annual, $promos->resolve($this->plan));

        $this->assertStringContainsString('años', $quote->headline());
        $this->assertStringNotContainsString('meses', $quote->headline());
    }

    public function test_al_acabarse_el_descuento_se_cobra_el_precio_de_lista(): void
    {
        $subscription = LawyerSubscription::query()->create([
            'lawyer_id' => $this->lawyer->id,
            'lawyer_plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::Active,
            'billing_period' => BillingPeriod::Monthly,
            'price' => 25_000,
            'discount_percentage' => 50,
            'discount_ends_at' => now()->addMonth(),
            'renews_at' => now()->subDay(),
        ]);

        $this->assertSame(25_000, $subscription->nextChargeAmount());

        // Pasada la ventana, rige el precio del plan. Y se avisa antes: lo
        // que no se puede hacer es subirlo en silencio.
        $subscription->update(['discount_ends_at' => now()->subDay()]);

        $this->assertSame($this->plan->price_monthly, $subscription->refresh()->nextChargeAmount());
    }
}
