<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Services;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Data\SubscriptionQuote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Mail\SubscriptionActivatedMail;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Models\SubscriptionPromo;
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
     * Deja la tarjeta registrada y anota la autorización del cobro recurrente.
     *
     * Las dos cosas van juntas porque por separado no sirven: una tarjeta
     * guardada sin autorización no se puede cobrar, y una autorización sin
     * tarjeta no se puede ejecutar.
     *
     * @param  array{id: string, brand: ?string, last_four: ?string}  $card
     */
    public function attachCard(LawyerSubscription $subscription, array $card): LawyerSubscription
    {
        $subscription->update([
            'payment_source_id' => $card['id'],
            'card_brand' => $card['brand'],
            'card_last_four' => $card['last_four'],
            'card_validated_at' => now(),
            'recurring_authorized_at' => now(),
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
}
