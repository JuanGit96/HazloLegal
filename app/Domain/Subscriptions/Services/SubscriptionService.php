<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Services;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Mail\SubscriptionActivatedMail;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Support\Facades\Mail;

/**
 * Altas y renovaciones de la suscripción de un abogado.
 *
 * El cobro recurrente automático no está: Wompi lo permite guardando una
 * fuente de pago tokenizada, y eso es otro flujo con su propio consentimiento
 * explícito. Mientras tanto cada periodo se cobra con un enlace, que es
 * honesto —el abogado decide cada vez— aunque dé más trabajo.
 */
final class SubscriptionService
{
    /** Crea o reutiliza la suscripción pendiente de un abogado. */
    public function start(Lawyer $lawyer, LawyerPlan $plan, BillingPeriod $period): LawyerSubscription
    {
        $existing = LawyerSubscription::query()
            ->where('lawyer_id', $lawyer->id)
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::PastDue])
            ->first();

        if ($existing) {
            $existing->update([
                'lawyer_plan_id' => $plan->id,
                'billing_period' => $period,
                'price' => $plan->priceFor($period),
            ]);

            return $existing->refresh();
        }

        return LawyerSubscription::query()->create([
            'lawyer_id' => $lawyer->id,
            'lawyer_plan_id' => $plan->id,
            'billing_period' => $period,
            'price' => $plan->priceFor($period),
            'status' => SubscriptionStatus::PastDue,
        ]);
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

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'started_at' => $subscription->started_at ?? now(),
            'renews_at' => $from->copy()->addMonths($period->months()),
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
