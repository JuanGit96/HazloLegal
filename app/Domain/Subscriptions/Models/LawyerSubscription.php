<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LawyerSubscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'billing_period' => BillingPeriod::class,
            'trial_ends_at' => 'datetime',
            'discount_ends_at' => 'datetime',
            'card_validated_at' => 'datetime',
            'recurring_authorized_at' => 'datetime',
            'started_at' => 'datetime',
            'renews_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'featured_until' => 'datetime',
        ];
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LawyerPlan::class, 'lawyer_plan_id');
    }

    /** El plan al que se baja en la próxima renovación, si pidió bajarse. */
    public function scheduledPlan(): BelongsTo
    {
        return $this->belongsTo(LawyerPlan::class, 'scheduled_plan_id');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPromo::class, 'subscription_promo_id');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(PaymentTransaction::class, 'payable');
    }

    /**
     * Hay un cobro lanzado y sin resolver.
     *
     * Wompi resuelve asíncrono: la transacción nace en PENDING y la confirma
     * el webhook. Entre una cosa y otra, `renews_at` sigue en el pasado, así
     * que sin esta comprobación el cobro diario volvería a cobrar mañana lo
     * mismo. Cobrar dos veces se arregla devolviendo dinero y perdiendo al
     * abogado.
     */
    public function hasChargeInFlight(): bool
    {
        return $this->transactions()
            ->where('status', PaymentStatus::Pending)
            ->where('created_at', '>=', now()->subDays(3))
            ->exists();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SubscriptionStatus::Trialing->value,
            SubscriptionStatus::Active->value,
        ]);
    }

    public function isActive(): bool
    {
        return $this->status->grantsAccess();
    }

    public function isFeatured(): bool
    {
        return $this->featured_until !== null && $this->featured_until->isFuture();
    }

    public function trialDaysLeft(): ?int
    {
        if ($this->status !== SubscriptionStatus::Trialing || $this->trial_ends_at === null) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function hasStoredCard(): bool
    {
        return filled($this->payment_source_id) && $this->recurring_authorized_at !== null;
    }

    /** «VISA ·· 4242», para que el abogado reconozca qué tarjeta registró. */
    public function cardLabel(): ?string
    {
        return $this->card_last_four
            ? trim(($this->card_brand ?: 'Tarjeta').' ·· '.$this->card_last_four)
            : null;
    }

    /** ¿Pidió bajarse de plan y todavía no le toca? */
    public function hasScheduledPlan(): bool
    {
        return $this->scheduled_plan_id !== null;
    }

    /** ¿Pidió irse, y lo pagado le dura hasta una fecha? */
    public function isEnding(): bool
    {
        return $this->ends_at !== null && $this->status !== SubscriptionStatus::Cancelled;
    }

    /**
     * El día en que el cambio pedido entra a regir.
     *
     * Es el de la renovación y no el de hoy: el periodo está pagado y
     * quitarlo antes sería cobrar por algo que no se entregó.
     */
    public function changesOn(): ?CarbonInterface
    {
        return $this->ends_at ?? $this->renews_at;
    }

    public function isInDiscountWindow(): bool
    {
        return (int) $this->discount_percentage > 0
            && $this->discount_ends_at !== null
            && $this->discount_ends_at->isFuture();
    }

    /**
     * Cuánto toca cobrar en la próxima renovación.
     *
     * Mientras dure la ventana de descuento, el precio pactado; después, el
     * de lista del plan. Se calcula y no se guarda porque el precio de lista
     * puede cambiar, y lo que se prometió fue el descuento, no la cifra.
     */
    public function nextChargeAmount(): int
    {
        $lista = $this->plan?->priceFor($this->billing_period ?? BillingPeriod::Monthly) ?? (int) $this->price;

        return $this->isInDiscountWindow() ? (int) $this->price : $lista;
    }

    /** Permiso efectivo: el plan lo concede y la suscripción está vigente. */
    public function allows(string $feature): bool
    {
        return $this->isActive() && (bool) ($this->plan?->{$feature} ?? false);
    }
}
