<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Subscriptions\Data\SubscriptionQuote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Services\SubscriptionPromoService;
use App\Domain\Subscriptions\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Alta de la suscripción de un abogado.
 *
 * Vive detrás del portal: solo paga quien ya está verificado y dentro. Cobrar
 * antes de comprobar la tarjeta profesional sería cobrarle a cualquiera por
 * aparecer como abogado.
 *
 * Dos caminos y una sola condición común. Con invitación empieza gratis unos
 * meses; sin ella rige el precio de lanzamiento, más barato durante unos
 * meses. En los dos se registra y valida una tarjeta antes de empezar, y el
 * número de esa tarjeta no pasa nunca por este servidor: el navegador lo
 * cambia por un token contra Wompi y aquí solo llega el token.
 */
class SubscriptionCheckoutController
{
    public function __construct(
        private readonly CurrentLawyer $current,
        private readonly SubscriptionService $subscriptions,
        private readonly SubscriptionPromoService $promos,
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

        $quote = $this->quoteFor($request, $plan, $period, $lawyer);
        $subscription = $this->subscriptions->start($lawyer, $plan, $period, $quote);

        // El cobro de hoy solo se abre si hay algo que cobrar hoy.
        $transaction = $this->wompi->isEnabled() && $quote->dueToday > 0
            ? $this->wompi->open($subscription, $quote->dueToday, (string) $lawyer->email, 'SUB')
            : null;

        return view('subscriptions.checkout', [
            'lawyer' => $lawyer,
            'plan' => $plan,
            'period' => $period,
            'quote' => $quote,
            'subscription' => $subscription,
            'wompi' => $this->wompi,
            'acceptance' => $this->wompi->isEnabled() ? $this->wompi->acceptanceTokens() : null,
            'transaction' => $transaction,
            'checkoutFields' => $transaction
                ? $this->wompi->checkoutFields($transaction, route('wompi.return', $transaction->reference))
                : [],
            'plans' => LawyerPlan::query()->active()->get(),
        ]);
    }

    /**
     * Registra la tarjeta y arranca la suscripción.
     *
     * Lo que llega es un token de un solo uso que el navegador obtuvo de
     * Wompi. Registrarlo como fuente de pago es lo que valida la tarjeta: si
     * el número no existe, está vencido o la red lo rechaza, Wompi no
     * devuelve nada y aquí no se arranca nada.
     */
    public function registerCard(Request $request): RedirectResponse
    {
        $lawyer = $this->current->get();

        abort_unless($lawyer !== null, 403);

        $validated = $request->validate([
            'card_token' => ['required', 'string', 'max:120'],
            'plan' => ['required', 'exists:lawyer_plans,id'],
            'periodo' => ['nullable', 'in:monthly,annual'],
            'invitacion' => ['nullable', 'string', 'max:40'],
            'accepts_recurring' => ['accepted'],
        ], [
            'card_token.required' => 'No pudimos leer los datos de la tarjeta. Revísalos e inténtalo otra vez.',
            'accepts_recurring.accepted' => 'Necesitamos tu autorización para cobrar la renovación en esa tarjeta.',
        ]);

        $plan = LawyerPlan::query()->findOrFail((int) $validated['plan']);
        $period = BillingPeriod::tryFrom((string) ($validated['periodo'] ?? '')) ?? BillingPeriod::Monthly;

        $quote = $this->quoteFor($request, $plan, $period, $lawyer);
        $subscription = $this->subscriptions->start($lawyer, $plan, $period, $quote);

        $tokens = $this->wompi->acceptanceTokens();

        if ($tokens === null) {
            return back()->with('paymentError', 'No pudimos conectarnos con la pasarela de pagos. Inténtalo en unos minutos.');
        }

        $card = $this->wompi->registerCard(
            cardToken: $validated['card_token'],
            email: (string) $lawyer->email,
            acceptanceToken: $tokens['acceptance'],
            personalDataToken: $tokens['personal_data'],
        );

        if ($card === null) {
            return back()->with('paymentError', 'La pasarela no aceptó esa tarjeta. Revisa el número, la fecha y el código, o prueba con otra.');
        }

        $this->subscriptions->attachCard($subscription, $card);

        // Con meses gratis no se cobra nada hoy; con precio de lanzamiento, el
        // primer periodo se cobra contra la tarjeta que acaba de registrar.
        if ($quote->dueToday === 0) {
            $this->subscriptions->beginPromoPeriod($subscription, $quote);

            return redirect()->to(url('/portal'))->with(
                'status',
                'Listo. Tu plan arranca hoy y el primer cobro es el '
                    .$subscription->refresh()->trial_ends_at?->translatedFormat('j \d\e F \d\e Y')
                    .'. Puedes cancelar antes sin costo.',
            );
        }

        $transaction = $this->wompi->open($subscription, $quote->dueToday, (string) $lawyer->email, 'SUB');
        $charged = $this->wompi->chargeStoredCard($transaction, (string) $subscription->payment_source_id);

        if ($charged === null) {
            return back()->with('paymentError', 'La tarjeta quedó registrada, pero el cobro no se pudo iniciar. Escríbenos y lo resolvemos sin que pierdas la promoción.');
        }

        $quote->promo?->redeem();

        return redirect()->to(url('/portal'))->with(
            'status',
            'Registramos tu tarjeta y el cobro de '.$quote->formattedDueToday()
                .' está en proceso. Te avisamos por correo en cuanto el banco lo confirme.',
        );
    }

    /** La condición que le toca a este abogado, con el código que traiga. */
    private function quoteFor(Request $request, LawyerPlan $plan, BillingPeriod $period, ?Lawyer $lawyer): SubscriptionQuote
    {
        $code = $request->string('invitacion')->trim()->toString()
            ?: (string) $request->session()->get('invitation_code', '');

        return $this->promos->quote(
            $plan,
            $period,
            $this->promos->resolve($plan, $code ?: null, $lawyer),
        );
    }
}
