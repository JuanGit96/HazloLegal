<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Shared\Models\GuaranteeClaim;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'speed' => DeliverySpeed::class,
            'payment_method' => PaymentMethod::class,
            'answers' => 'array',
            'wants_signature' => 'boolean',
            'paid_at' => 'datetime',
            'promised_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    // -------------------------------------------------- Relaciones

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function assignedLawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'assigned_lawyer_id');
    }

    public function guaranteeClaims(): HasMany
    {
        return $this->hasMany(GuaranteeClaim::class);
    }

    // -------------------------------------------------- Scopes

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', collect(OrderStatus::openStates())->map(fn ($s) => $s->value));
    }

    public function scopeRevenue(Builder $query): Builder
    {
        return $query->whereIn('status', collect(OrderStatus::revenueStates())->map(fn ($s) => $s->value));
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereNotNull('promised_at')->where('promised_at', '<', now());
    }

    // -------------------------------------------------- Comportamiento

    public function isOverdue(): bool
    {
        return $this->promised_at !== null
            && $this->promised_at->isPast()
            && in_array($this->status, OrderStatus::openStates(), true);
    }

    public function hoursLeft(): ?int
    {
        return $this->promised_at ? (int) now()->diffInHours($this->promised_at, false) : null;
    }

    public function formattedAmount(): string
    {
        return '$'.number_format($this->amount, 0, ',', '.');
    }

    public function trackingUrl(): string
    {
        return route('checkout.confirmation', $this->reference);
    }
}
