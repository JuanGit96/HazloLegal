<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Subscriptions\Data\CardVerification;
use App\Domain\Subscriptions\Data\SubscriptionQuote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\CardCheck;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Services\CardVerifier;
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
        private readonly CardVerifier $verifier,
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

        // Aquí no se ofrece Nequi, PSE ni transferencia, y no es por pereza:
        // ninguno deja nada con qué cobrar el periodo siguiente. Un abogado
        // que paga por PSE simplemente dejaría de renovar sin enterarse, y
        // nosotros perseguiríamos a mano cada mes. La suscripción es con
        // tarjeta —débito o crédito— o no es.
        return view('subscriptions.checkout', [
            'lawyer' => $lawyer,
            'plan' => $plan,
            'period' => $period,
            'quote' => $quote,
            'subscription' => $subscription,
            'wompi' => $this->wompi,
            'acceptance' => $this->wompi->isEnabled() ? $this->wompi->acceptanceTokens() : null,
            'verifier' => $this->verifier,
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
        return $quote->dueToday === 0
            ? $this->startFreePeriod($subscription)
            : $this->chargeFirstPeriod($subscription, $quote, (string) $lawyer->email);
    }

    /**
     * Arranca los meses de invitación, pero solo con la tarjeta comprobada.
     *
     * Aquí no hay cobro que pruebe nada, así que se cobra el mínimo y se
     * anula. Sin eso, una tarjeta falsa pasaría el alta y nos enteraríamos
     * tres meses después, cuando el abogado ya lleva un trimestre publicado.
     */
    private function startFreePeriod(LawyerSubscription $subscription): RedirectResponse
    {
        $verificacion = $this->verifier->verify($subscription);

        if ($verificacion->outcome === CardCheck::Declined || $verificacion->outcome === CardCheck::Failed) {
            $this->subscriptions->forgetCard($subscription);

            return back()->with('paymentError', $verificacion->message());
        }

        if ($verificacion->outcome === CardCheck::Pending) {
            // El webhook lo cierra. No se arranca el plan todavía: publicar
            // el perfil de alguien cuya tarjeta no sabemos si sirve es
            // exactamente lo que esto venía a evitar.
            return redirect()->to(url('/portal'))->with('status', $verificacion->message());
        }

        // Anular, dar la tarjeta por buena y arrancar los meses lo hizo ya el
        // procesador de pagos, que es el mismo que atiende al webhook.
        $aviso = $verificacion->pendingRefund
            ? ' Hicimos un cobro de '.$this->verifier->formattedAmount()
                .' para comprobarla y no se pudo anular al instante; te lo devolvemos y te escribimos.'
            : ' El cobro de '.$this->verifier->formattedAmount()
                .' con el que la comprobamos ya quedó anulado; si alcanza a aparecer en tu extracto, se cae solo.';

        return redirect()->to(url('/portal'))->with(
            'status',
            'Listo. Tu plan arranca hoy y el primer cobro es el '
                .$subscription->refresh()->trial_ends_at?->translatedFormat('j \d\e F \d\e Y')
                .'. Puedes cancelar antes sin costo.'.$aviso,
        );
    }

    /** El primer periodo con precio de lanzamiento: ese cobro ya es la prueba. */
    private function chargeFirstPeriod(LawyerSubscription $subscription, SubscriptionQuote $quote, string $email): RedirectResponse
    {
        $transaction = $this->wompi->open($subscription, $quote->dueToday, $email, 'SUB');

        if ($this->wompi->chargeStoredCard($transaction, (string) $subscription->payment_source_id) === null) {
            $this->subscriptions->forgetCard($subscription);

            return back()->with('paymentError', 'No pudimos iniciar el cobro con esa tarjeta. Revísala o prueba con otra; no te hemos cobrado nada.');
        }

        // Se espera un momento al banco para poder contestarle en esta misma
        // pantalla. Si tarda, lo cierra el webhook.
        $desenlace = $this->verifier->awaitOutcome($transaction);

        if ($desenlace === CardCheck::Declined) {
            $this->subscriptions->forgetCard($subscription);

            return back()->with(
                'paymentError',
                (new CardVerification($desenlace, $transaction, $transaction->refresh()->status_message))->message(),
            );
        }

        $quote->promo?->redeem();

        return redirect()->to(url('/portal'))->with(
            'status',
            $desenlace === CardCheck::Approved
                ? 'Listo: cobramos '.$quote->formattedDueToday().' y tu plan ya está activo.'
                : 'Registramos tu tarjeta y el cobro de '.$quote->formattedDueToday()
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
