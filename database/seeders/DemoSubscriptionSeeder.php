<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Illuminate\Database\Seeder;

/**
 * ⚠️ DATOS DE DEMOSTRACIÓN.
 *
 * Suscripciones ficticias para que el portal del abogado y las métricas de
 * ingresos no se vean vacíos durante la demo.
 */
class DemoSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $plans = LawyerPlan::query()->get()->keyBy('key');
        $lawyers = Lawyer::query()->orderBy('id')->get();

        if ($plans->isEmpty() || $lawyers->isEmpty()) {
            return;
        }

        $assignments = [
            // email del abogado => [plan, estado, periodo]
            0 => ['crecimiento', SubscriptionStatus::Active, BillingPeriod::Monthly],
            1 => ['destacado', SubscriptionStatus::Active, BillingPeriod::Annual],
            2 => ['directorio', SubscriptionStatus::Trialing, BillingPeriod::Monthly],
        ];

        foreach ($assignments as $index => [$planKey, $status, $period]) {
            $lawyer = $lawyers->get($index);
            $plan = $plans->get($planKey) ?? $plans->first();

            if (! $lawyer) {
                continue;
            }

            LawyerSubscription::query()->updateOrCreate(
                ['lawyer_id' => $lawyer->id],
                [
                    'lawyer_plan_id' => $plan->id,
                    'status' => $status,
                    'billing_period' => $period,
                    'price' => $plan->priceFor($period),
                    'started_at' => now()->subMonths(2),
                    'trial_ends_at' => $status === SubscriptionStatus::Trialing
                        ? now()->addMonths(max(1, (int) $plan->free_trial_months))
                        : null,
                    'renews_at' => $status === SubscriptionStatus::Active
                        ? now()->addMonth()
                        : null,
                    'featured_until' => $plan->includes_featured ? now()->addMonths(6) : null,
                ],
            );

            $lawyer->forceFill([
                'is_published' => true,
                'is_featured' => (bool) $plan->includes_featured,
            ])->saveQuietly();
        }
    }
}
