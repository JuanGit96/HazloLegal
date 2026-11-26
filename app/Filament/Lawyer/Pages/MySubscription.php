<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Pages;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Subscriptions\Data\SubscriptionQuote;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Domain\Subscriptions\Services\SubscriptionPromoService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * Qué plan tiene y qué le da.
 *
 * Se muestra lo que el plan hace de verdad —si aparece en los instructivos,
 * si recibe avisos de casos, si ve contactos—, no una lista de adjetivos.
 * Un abogado que no entiende qué paga no renueva.
 */
class MySubscription extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Mi presencia';

    protected static ?string $navigationLabel = 'Mi suscripción';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Mi suscripción';

    protected string $view = 'filament.lawyer.pages.my-subscription';

    /** @var array<int, ?SubscriptionQuote> */
    private array $ofertas = [];

    public function subscription(): ?LawyerSubscription
    {
        return app(CurrentLawyer::class)->get()?->subscription;
    }

    /** Lo que incluye el plan, en términos de lo que el sistema hace. */
    public function features(): Collection
    {
        $plan = $this->subscription()?->plan;

        if (! $plan instanceof LawyerPlan) {
            return collect();
        }

        return collect([
            ['includes_profile_page', 'Perfil público en el directorio', 'Tu ficha con especialidad, ciudad y biografía, indexable en Google.'],
            ['includes_case_alerts', 'Avisos de casos nuevos', 'Te escribimos cuando llega un caso de tu área. Te postulas y el cliente elige.'],
            ['includes_pdf_placement', 'Tu nombre en los instructivos', 'El PDF que el cliente imprime y lleva a la ventanilla lleva tus datos.'],
            ['includes_lead_data', 'Acceso a contactos', 'Personas de tus áreas que autorizaron que un abogado las contacte.'],
            ['includes_featured', 'Destacado en el directorio', 'Apareces antes que los demás en tu área.'],
            ['includes_blog_placement', 'Presencia en los artículos', 'Tu perfil se ofrece al final de las guías de tu especialidad.'],
        ])->map(fn (array $row) => [
            'included' => (bool) $plan->{$row[0]},
            'title' => $row[1],
            'description' => $row[2],
        ]);
    }

    /** Lo que podría tener si subiera de plan. */
    public function upgrades(): Collection
    {
        $current = $this->subscription()?->plan;

        return LawyerPlan::query()
            ->where('is_active', true)
            ->when($current, fn ($q) => $q->where('price_monthly', '>', $current->price_monthly))
            ->orderBy('price_monthly')
            ->get();
    }

    /**
     * El periodo que se mantiene al cambiar de plan.
     *
     * Quien paga por año no debería terminar en mensual por pulsar un botón:
     * el enlace y el precio que se le enseña van los dos con su periodo.
     */
    public function period(): BillingPeriod
    {
        return $this->subscription()?->billing_period ?? BillingPeriod::Monthly;
    }

    public function periodLabel(): string
    {
        return $this->period() === BillingPeriod::Annual ? 'año' : 'mes';
    }

    /**
     * Lo que de verdad le van a cobrar en la pasarela por ese plan.
     *
     * Sin esto la pantalla enseña el precio de lista y la pasarela, dos
     * clics después, uno más barato. Que la cifra cambie entre una pantalla
     * y la siguiente mina la confianza aunque cambie a favor del abogado, y
     * además esconde la promoción justo donde serviría para decidir.
     *
     * Devuelve null cuando no hay promoción: entonces el precio de lista es
     * el precio, y no hay dos cifras que contar.
     */
    public function offerFor(LawyerPlan $plan): ?SubscriptionQuote
    {
        if (array_key_exists($plan->id, $this->ofertas)) {
            return $this->ofertas[$plan->id];
        }

        $promos = app(SubscriptionPromoService::class);

        $quote = $promos->quote(
            $plan,
            $this->period(),
            $promos->resolve($plan, null, app(CurrentLawyer::class)->get()),
        );

        return $this->ofertas[$plan->id] = $quote->hasPromo() ? $quote : null;
    }

    /**
     * ¿La renovación pasa por la pasarela?
     *
     * Con tarjeta comprobada y la suscripción al día no pasa: el cobro sale
     * solo y ofrecer un botón de «renovar» haría pensar que hay algo que
     * hacer. El botón aparece solo cuando de verdad hace falta la mano del
     * abogado: sin tarjeta con la que cobrar, o con un cobro que falló.
     */
    public function mustCheckout(): bool
    {
        $subscription = $this->subscription();

        if (! $subscription?->plan instanceof LawyerPlan) {
            return false;
        }

        return in_array($subscription->status?->value, ['past_due', 'cancelled'], true)
            || ! $subscription->hasStoredCard();
    }
}
