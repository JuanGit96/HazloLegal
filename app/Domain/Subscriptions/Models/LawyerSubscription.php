<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'started_at' => 'datetime',
            'renews_at' => 'datetime',
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

    /** Permiso efectivo: el plan lo concede y la suscripción está vigente. */
    public function allows(string $feature): bool
    {
        return $this->isActive() && (bool) ($this->plan?->{$feature} ?? false);
    }
}
