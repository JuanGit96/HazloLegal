<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Services;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Shared\Concerns\RoundsPrices;
use App\Domain\Subscriptions\Data\SubscriptionQuote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\SubscriptionPromo;

/**
 * Qué condición le toca a cada abogado y cuánto paga por ella.
 *
 * Dos caminos, uno de ellos sin que el abogado haga nada: si llegó con un
 * código de invitación vale el suyo, y si no, la promoción marcada por
 * defecto. Nunca se le ofrece el precio de lista a secas mientras haya
 * lanzamiento, porque entonces el lanzamiento no existiría para quien no
 * conoce a nadie.
 */
final class SubscriptionPromoService
{
    use RoundsPrices;

    /**
     * La promoción que aplica, por orden de prioridad.
     *
     * El código explícito manda sobre el que traiga guardado el abogado, para
     * que un enlace nuevo pueda corregir uno viejo; y si nada vale, queda la
     * oferta por defecto.
     */
    public function resolve(LawyerPlan $plan, ?string $code = null, ?Lawyer $lawyer = null): ?SubscriptionPromo
    {
        foreach ([$code, $lawyer?->invitation_code] as $candidato) {
            if (filled($candidato) && ($promo = $this->byCode((string) $candidato, $plan)) !== null) {
                return $promo;
            }
        }

        return $this->default($plan);
    }

    /** Una invitación concreta, solo si sigue viva y sirve para este plan. */
    public function byCode(string $code, ?LawyerPlan $plan = null): ?SubscriptionPromo
    {
        $promo = SubscriptionPromo::query()
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))])
            ->first();

        if (! $promo?->isRedeemable()) {
            return null;
        }

        return $plan === null || $promo->appliesTo($plan) ? $promo : null;
    }

    /** La oferta de lanzamiento: la que recibe quien llega por su cuenta. */
    public function default(?LawyerPlan $plan = null): ?SubscriptionPromo
    {
        return SubscriptionPromo::query()
            ->active()
            ->where('is_default', true)
            ->whereNull('code')
            ->get()
            ->first(fn (SubscriptionPromo $promo) => $promo->isRedeemable()
                && ($plan === null || $promo->appliesTo($plan)));
    }

    /**
     * Lo que paga hoy, lo que pagará después y cuándo.
     *
     * Los meses gratis y el descuento pueden convivir: primero corren los
     * meses gratis y, cuando se acaban, empieza el descuento. Es la forma
     * amable de bajar del cero al precio de lista.
     */
    public function quote(LawyerPlan $plan, BillingPeriod $period, ?SubscriptionPromo $promo = null): SubscriptionQuote
    {
        $lista = $plan->priceFor($period);

        // Cuando hay promoción, ella manda del todo. Sumarle además la
        // prueba que el plan trae por su cuenta daría meses gratis *y*
        // descuento: dos condiciones que nadie configuró y que el abogado no
        // vio escritas en ninguna parte. La prueba del plan solo rige cuando
        // no hay ninguna promoción vigente.
        $gratis = match (true) {
            $promo?->hasFreeMonths() === true => (int) $promo->free_months,
            $promo !== null => 0,
            default => (int) ($plan->free_trial_months ?? 0),
        };

        $porcentaje = $promo?->hasDiscount() ? (int) $promo->discount_percentage : 0;
        $mesesDescuento = $porcentaje > 0 ? (int) $promo->discount_months : 0;

        $conDescuento = $porcentaje > 0
            ? $this->roundToPricePoint($lista * (100 - $porcentaje) / 100)
            : $lista;

        $finGratis = $gratis > 0 ? now()->copy()->addMonths($gratis) : null;
        $primerCobro = $finGratis ?? now()->copy();

        $finDescuento = $mesesDescuento > 0
            ? $primerCobro->copy()->addMonths($mesesDescuento * $period->months())
            : null;

        return new SubscriptionQuote(
            plan: $plan,
            period: $period,
            promo: $promo,
            listPrice: $lista,
            dueToday: $gratis > 0 ? 0 : $conDescuento,
            promoPrice: $conDescuento,
            recurringPrice: $lista,
            freeMonths: $gratis,
            discountMonths: $mesesDescuento,
            discountPercentage: $porcentaje,
            firstChargeAt: $primerCobro,
            fullPriceFrom: $finDescuento ?? $finGratis,
        );
    }
}
