<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Una condición de entrada a la suscripción: meses gratis, descuento, o las
 * dos cosas.
 *
 * La que no tiene código es la que recibe quien llega por su cuenta.
 */
class SubscriptionPromo extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LawyerPlan::class, 'lawyer_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(LawyerSubscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * ¿Se puede usar todavía?
     *
     * El cupo se comprueba aquí y no en la consulta porque una promoción
     * agotada tiene que poder explicarse («se acabaron los cupos») en vez de
     * desaparecer sin más.
     */
    public function isRedeemable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at?->isFuture() || $this->expires_at?->isPast()) {
            return false;
        }

        return $this->max_redemptions === null
            || $this->redeemed_count < $this->max_redemptions;
    }

    public function appliesTo(LawyerPlan $plan): bool
    {
        return $this->lawyer_plan_id === null || $this->lawyer_plan_id === $plan->id;
    }

    public function hasFreeMonths(): bool
    {
        return (int) $this->free_months > 0;
    }

    public function hasDiscount(): bool
    {
        return (int) $this->discount_percentage > 0 && (int) $this->discount_months > 0;
    }

    /** ¿Ofrece algo de verdad, o es una fila vacía? */
    public function isWorthShowing(): bool
    {
        return $this->hasFreeMonths() || $this->hasDiscount();
    }

    public function isInvitation(): bool
    {
        return filled($this->code);
    }

    public function url(): ?string
    {
        return $this->code ? route('lawyers.invitation', $this->code) : null;
    }

    public function redeem(): void
    {
        $this->increment('redeemed_count');
    }
}
