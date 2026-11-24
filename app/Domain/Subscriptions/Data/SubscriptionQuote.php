<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Data;

use App\Domain\Catalog\Data\Quote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\SubscriptionPromo;
use Carbon\CarbonInterface;

/**
 * Qué paga hoy un abogado, qué pagará después y cuándo.
 *
 * Las tres cosas juntas y en el mismo sitio a propósito: una promoción que
 * dice «gratis» sin decir qué pasa el día que se acaba es la receta de la
 * queja y, además, publicidad engañosa.
 */
final readonly class SubscriptionQuote
{
    public function __construct(
        public LawyerPlan $plan,
        public BillingPeriod $period,
        public ?SubscriptionPromo $promo,
        /** El precio de lista del periodo, sin promoción. */
        public int $listPrice,
        /** Lo que se cobra hoy. Cero si hay meses gratis. */
        public int $dueToday,
        /** Lo que se cobrará en cada periodo mientras dure la promoción. */
        public int $promoPrice,
        /** Lo que se cobrará cuando la promoción termine. */
        public int $recurringPrice,
        public int $freeMonths,
        public int $discountMonths,
        public int $discountPercentage,
        /** Cuándo cae el primer cobro. Null si no hay nada que cobrar nunca. */
        public ?CarbonInterface $firstChargeAt,
        /** Cuándo empieza a regir el precio de lista. */
        public ?CarbonInterface $fullPriceFrom,
    ) {}

    public function hasPromo(): bool
    {
        return $this->promo !== null && ($this->freeMonths > 0 || $this->discountPercentage > 0);
    }

    public function startsFree(): bool
    {
        return $this->dueToday === 0;
    }

    /**
     * Lo que el abogado se ahorra mientras dure la promoción.
     *
     * Los meses gratis se cuentan en meses aunque el plan se cobre por año:
     * tres meses de un plan anual se ahorran tres doceavos, no un año. Inflar
     * esta cifra sería publicidad engañosa, y encima la más fácil de pillar.
     */
    public function savings(): int
    {
        $mensual = $this->period === BillingPeriod::Annual
            ? (int) round($this->listPrice / 12)
            : $this->listPrice;

        $gratis = $this->freeMonths * $mensual;

        // El descuento se cuenta en periodos de cobro, no en meses: con plan
        // anual, «3» son tres años a precio rebajado.
        $rebaja = $this->discountMonths * ($this->listPrice - $this->promoPrice);

        return max(0, $gratis + $rebaja);
    }

    public function formattedListPrice(): string
    {
        return Quote::cop($this->listPrice);
    }

    public function formattedDueToday(): string
    {
        return $this->dueToday === 0 ? 'Gratis' : Quote::cop($this->dueToday);
    }

    public function formattedPromoPrice(): string
    {
        return Quote::cop($this->promoPrice);
    }

    public function formattedRecurringPrice(): string
    {
        return Quote::cop($this->recurringPrice);
    }

    public function formattedSavings(): string
    {
        return Quote::cop($this->savings());
    }

    public function periodLabel(): string
    {
        return $this->period === BillingPeriod::Annual ? 'año' : 'mes';
    }

    /** La frase que resume la oferta en una línea. */
    public function headline(): string
    {
        if ($this->freeMonths > 0) {
            return $this->freeMonths === 1
                ? 'Tu primer mes es gratis'
                : "Tus primeros {$this->freeMonths} meses son gratis";
        }

        if ($this->discountPercentage > 0) {
            // El descuento se cuenta en periodos de cobro: decir «meses» con
            // un plan anual prometería once meses que no son.
            $unidad = $this->period === BillingPeriod::Annual ? 'año' : 'mes';

            $cuando = $this->discountMonths === 1
                ? "el primer {$unidad}"
                : "los primeros {$this->discountMonths} {$unidad}s";

            return "{$this->discountPercentage} % menos ".$cuando;
        }

        return $this->plan->name;
    }
}
