<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Services;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Shared\Concerns\RoundsPrices;
use App\Domain\Subscriptions\Data\SubscriptionQuote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Mail\SubscriptionActivatedMail;
use App\Domain\Subscriptions\Mail\SubscriptionScheduledMail;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Support\Facades\Mail;

/**
 * Altas y renovaciones de la suscripción de un abogado.
 *
 * El cobro recurrente va contra una tarjeta registrada en Wompi: el abogado
 * la valida al entrar y autoriza expresamente los cobros siguientes, con
 * fecha. Sin esa autorización no se cobra, y por eso se guarda aparte del
 * identificador de la tarjeta.
 *
 * Registrar la tarjeta antes de empezar no es un capricho: es lo que separa
 * a un abogado suscrito de un mes de perfil publicado gratis.
 */
final class SubscriptionService
{
    use RoundsPrices;

    /**
     * Crea o reutiliza la suscripción pendiente de un abogado.
     *
     * El precio que se guarda es el de la promoción, no el de lista: es lo
     * que de verdad se le va a cobrar en el primer periodo. El de lista vive
     * en el plan y se recalcula cuando la promoción se acaba.
     */
    public function start(Lawyer $lawyer, LawyerPlan $plan, BillingPeriod $period, ?SubscriptionQuote $quote = null): LawyerSubscription
    {
        $datos = [
            'lawyer_plan_id' => $plan->id,
            'billing_period' => $period,
            'price' => $quote?->promoPrice ?? $plan->priceFor($period),
            'subscription_promo_id' => $quote?->promo?->id,
            'promo_code' => $quote?->promo?->code,
            'discount_percentage' => $quote?->discountPercentage ?? 0,
        ];

        $existing = LawyerSubscription::query()
            ->where('lawyer_id', $lawyer->id)
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::PastDue])
            ->first();

        if ($existing) {
            // A quien ya está dentro no se le reescribe la condición que se
            // le prometió: solo se completa si nunca tuvo ninguna.
            if ($existing->promo_code !== null || $existing->subscription_promo_id !== null) {
                unset($datos['subscription_promo_id'], $datos['promo_code'], $datos['discount_percentage']);
            }

            $existing->update($datos);

            return $existing->refresh();
        }

        return LawyerSubscription::query()->create([
            ...$datos,
            'lawyer_id' => $lawyer->id,
            'status' => SubscriptionStatus::PastDue,
        ]);
    }

    /**
     * Guarda la tarjeta, todavía sin dar por buena.
     *
     * Que la pasarela la acepte como fuente de pago no prueba que sirva:
     * devuelve «disponible» para tarjetas que el banco después rechaza. Por
     * eso aquí no se escribe `card_validated_at` ni la autorización del
     * cobro recurrente: eso lo firma `confirmCard()`, y solo cuando un cobro
     * real haya sido aprobado. Mientras tanto `hasStoredCard()` dice que no,
     * así que el cobro diario no la toca.
     *
     * @param  array{id: string, brand: ?string, last_four: ?string}  $card
     */
    public function attachCard(LawyerSubscription $subscription, array $card): LawyerSubscription
    {
        $subscription->update([
            'payment_source_id' => $card['id'],
            'card_brand' => $card['brand'],
            'card_last_four' => $card['last_four'],
            'card_validated_at' => null,
            'recurring_authorized_at' => null,
        ]);

        return $subscription->refresh();
    }

    /** El banco aprobó un cobro contra esa tarjeta: ya se puede volver a usar. */
    public function confirmCard(LawyerSubscription $subscription): LawyerSubscription
    {
        if ($subscription->card_validated_at !== null) {
            return $subscription;
        }

        $subscription->update([
            'card_validated_at' => now(),
            'recurring_authorized_at' => now(),
        ]);

        return $subscription->refresh();
    }

    /**
     * Olvida una tarjeta que no sirvió.
     *
     * Guardar una tarjeta que el banco rechaza no es inocuo: el cobro diario
     * la intentaría cada día y acumularía rechazos, que es justo lo que
     * hace que un banco bloquee un comercio.
     */
    public function forgetCard(LawyerSubscription $subscription): LawyerSubscription
    {
        $subscription->update([
            'payment_source_id' => null,
            'card_brand' => null,
            'card_last_four' => null,
            'card_validated_at' => null,
            'recurring_authorized_at' => null,
        ]);

        return $subscription->refresh();
    }

    /**
     * Arranca los meses gratis de una promoción, con la tarjeta ya registrada.
     *
     * Se separa de `startTrial()` porque aquí la prueba viene de una
     * promoción con fecha y con cobro al final, y hay que dejar escrito
     * cuándo deja de ser gratis y cuánto se cobrará entonces.
     */
    public function beginPromoPeriod(LawyerSubscription $subscription, SubscriptionQuote $quote): LawyerSubscription
    {
        $finGratis = now()->copy()->addMonths(max(1, $quote->freeMonths));

        $subscription->update([
            'status' => SubscriptionStatus::Trialing,
            'started_at' => $subscription->started_at ?? now(),
            'trial_ends_at' => $finGratis,
            'renews_at' => $finGratis,
            'discount_ends_at' => $quote->discountMonths > 0
                ? $finGratis->copy()->addMonths($quote->discountMonths * $quote->period->months())
                : null,
            'cancelled_at' => null,
        ]);

        if ($subscription->plan?->includes_profile_page) {
            $subscription->lawyer?->forceFill(['is_published' => true])->saveQuietly();
        }

        $quote->promo?->redeem();

        return $subscription->refresh();
    }

    /** Un cobro aprobado activa o extiende el periodo. */
    public function activate(LawyerSubscription $subscription, ?PaymentTransaction $transaction = null): LawyerSubscription
    {
        $period = $subscription->billing_period ?? BillingPeriod::Monthly;

        // Se extiende desde la fecha de renovación si todavía no venció, para
        // que pagar antes de tiempo no le quite días al abogado.
        $from = $subscription->renews_at?->isFuture()
            ? $subscription->renews_at
            : now();

        // La ventana de descuento se fija en el primer cobro y no se mueve:
        // si se recalculara en cada renovación, el «3 meses más barato» no
        // se acabaría nunca y el precio de lista no llegaría jamás.
        $finDescuento = $subscription->discount_ends_at;

        if ($finDescuento === null && (int) $subscription->discount_percentage > 0) {
            $meses = (int) ($subscription->promo?->discount_months ?: 1);
            $finDescuento = $from->copy()->addMonths($meses * $period->months());
        }

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'started_at' => $subscription->started_at ?? now(),
            'renews_at' => $from->copy()->addMonths($period->months()),
            'discount_ends_at' => $finDescuento,
            'cancelled_at' => null,
            'featured_until' => $subscription->plan?->includes_featured
                ? $from->copy()->addMonths($period->months())
                : $subscription->featured_until,
        ]);

        // El perfil se publica al activar: es lo primero que el abogado
        // compró y lo primero que va a ir a mirar.
        if ($subscription->plan?->includes_profile_page) {
            $subscription->lawyer?->forceFill([
                'is_published' => true,
                'is_featured' => (bool) $subscription->plan->includes_featured,
            ])->saveQuietly();
        }

        $subscription->refresh();

        if ($subscription->lawyer?->email) {
            Mail::to($subscription->lawyer->email)
                ->send(new SubscriptionActivatedMail($subscription, $transaction));
        }

        return $subscription;
    }

    /** Arranca la prueba gratis, sin cobrar nada. */
    public function startTrial(LawyerSubscription $subscription): LawyerSubscription
    {
        $months = max(1, (int) ($subscription->plan?->free_trial_months ?? 1));

        $subscription->update([
            'status' => SubscriptionStatus::Trialing,
            'started_at' => now(),
            'trial_ends_at' => now()->addMonths($months),
        ]);

        if ($subscription->plan?->includes_profile_page) {
            $subscription->lawyer?->forceFill(['is_published' => true])->saveQuietly();
        }

        return $subscription->refresh();
    }

    public function cancel(LawyerSubscription $subscription): LawyerSubscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $subscription->lawyer?->forceFill([
            'is_published' => false,
            'is_featured' => false,
        ])->saveQuietly();

        return $subscription->refresh();
    }

    /**
     * Bajarse de plan, desde la próxima renovación.
     *
     * No se aplica hoy a propósito: el periodo está pagado y quitarle ahora
     * lo que compró sería cobrarle por algo que no se entrega. Hasta el día
     * de la renovación sigue con todo lo de su plan actual.
     */
    public function scheduleChange(LawyerSubscription $subscription, LawyerPlan $plan): LawyerSubscription
    {
        $subscription->update([
            'scheduled_plan_id' => $plan->id,
            'ends_at' => null,
            'cancellation_reason' => null,
        ]);

        $this->announce($subscription->refresh());

        return $subscription;
    }

    /**
     * Irse al final de lo pagado.
     *
     * La suscripción no se cancela aquí: queda con fecha de fin. Cancelarla
     * hoy le apagaría el perfil a alguien que pagó el mes completo, y además
     * nos quitaría a nosotros las semanas en que todavía puede cambiar de
     * idea.
     */
    public function scheduleCancellation(LawyerSubscription $subscription, ?string $reason = null): LawyerSubscription
    {
        $subscription->update([
            'ends_at' => $subscription->renews_at ?? now(),
            'scheduled_plan_id' => null,
            'cancellation_reason' => $reason,
        ]);

        $this->announce($subscription->refresh());

        return $subscription;
    }

    /** Se arrepintió: queda como estaba, sin cambios ni fecha de fin. */
    public function resume(LawyerSubscription $subscription): LawyerSubscription
    {
        $subscription->update([
            'scheduled_plan_id' => null,
            'ends_at' => null,
            'cancellation_reason' => null,
        ]);

        return $subscription->refresh();
    }

    /**
     * Ejecuta el día de la renovación lo que se pidió semanas atrás.
     *
     * Lo llama el comando diario antes de decidir si cobra, y el orden
     * importa: a quien se va no se le cobra, y a quien se baja se le cobra
     * el plan nuevo, no el viejo.
     *
     * Devuelve true cuando la suscripción quedó cancelada, que es la señal
     * de que ya no hay nada que cobrar.
     */
    public function applyScheduled(LawyerSubscription $subscription): bool
    {
        if ($subscription->ends_at !== null && ! $subscription->ends_at->isFuture()) {
            $this->cancel($subscription);

            return true;
        }

        $plan = $subscription->scheduledPlan;

        if (! $plan instanceof LawyerPlan || $subscription->renews_at?->isFuture()) {
            return false;
        }

        $period = $subscription->billing_period ?? BillingPeriod::Monthly;
        $lista = $plan->priceFor($period);

        // El descuento se pactó en porcentaje y por un número de periodos,
        // no sobre una cifra: si todavía corre, sigue corriendo sobre el
        // plan nuevo. Quitárselo por bajarse sería cambiar lo acordado.
        $precio = $subscription->isInDiscountWindow()
            ? $this->roundToPricePoint($lista * (100 - (int) $subscription->discount_percentage) / 100)
            : $lista;

        $subscription->update([
            'lawyer_plan_id' => $plan->id,
            'scheduled_plan_id' => null,
            'price' => $precio,
            'featured_until' => $plan->includes_featured ? $subscription->featured_until : null,
        ]);

        $subscription->lawyer?->forceFill([
            'is_published' => (bool) $plan->includes_profile_page,
            'is_featured' => (bool) $plan->includes_featured,
        ])->saveQuietly();

        $subscription->refresh();

        return false;
    }

    /** El cambio se avisa por escrito el mismo día que se pide. */
    private function announce(LawyerSubscription $subscription): void
    {
        $correo = $subscription->lawyer?->email;

        if (filled($correo)) {
            Mail::to($correo)->send(new SubscriptionScheduledMail($subscription));
        }
    }
}
