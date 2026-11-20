<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Pago de la suscripción de un abogado.
 *
 * Vive detrás del portal: solo paga quien ya está verificado y dentro. Cobrar
 * antes de comprobar la tarjeta profesional sería cobrarle a cualquiera por
 * aparecer como abogado.
 */
class SubscriptionCheckoutController
{
    public function __construct(
        private readonly CurrentLawyer $current,
        private readonly SubscriptionService $subscriptions,
        private readonly WompiGateway $wompi,
    ) {}

    public function show(Request $request): View
    {
        $lawyer = $this->current->get();

        abort_unless($lawyer !== null, 403);

        $plan = LawyerPlan::query()->findOrFail(
            $request->integer('plan') ?: $lawyer->subscription?->lawyer_plan_id
        );

        $period = BillingPeriod::tryFrom((string) $request->query('periodo')) ?? BillingPeriod::Monthly;

        $subscription = $this->subscriptions->start($lawyer, $plan, $period);

        $transaction = $this->wompi->isEnabled()
            ? $this->wompi->open($subscription, $plan->priceFor($period), (string) $lawyer->email, 'SUB')
            : null;

        return view('subscriptions.checkout', [
            'lawyer' => $lawyer,
            'plan' => $plan,
            'period' => $period,
            'subscription' => $subscription,
            'wompi' => $this->wompi,
            'transaction' => $transaction,
            'checkoutFields' => $transaction
                ? $this->wompi->checkoutFields($transaction, route('wompi.return', $transaction->reference))
                : [],
            'plans' => LawyerPlan::query()->active()->get(),
        ]);
    }
}
