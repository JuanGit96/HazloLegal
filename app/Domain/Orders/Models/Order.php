<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Enums\OrderTier;
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
            'tier' => OrderTier::class,
            'ai_review' => 'array',
            'accepts_marketing' => 'boolean',
            'shareable_with_lawyers' => 'boolean',
            'signed_at' => 'datetime',
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

    public function assignedLawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'assigned_lawyer_id');
    }

    public function guaranteeClaims(): HasMany
    {
        return $this->hasMany(GuaranteeClaim::class);
    }

    /** Los adicionales tal como se vendieron, con su precio congelado. */
    public function addons(): HasMany
    {
        return $this->hasMany(OrderAddon::class);
    }

    public function signatureRequest(): HasMany
    {
        return $this->hasMany(\App\Domain\Documents\Models\SignatureRequest::class);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    public function featuredLawyer(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Lawyers\Models\Lawyer::class, 'featured_lawyer_id');
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

    public function hasAddon(string $key): bool
    {
        return $this->addons->contains('key', $key);
    }

    public function isExpress(): bool
    {
        return $this->hasAddon('express');
    }

    /**
     * ¿Lleva una familia de adicionales? Para decidir qué ya no se ofrece.
     *
     * Las líneas del pedido guardan la clave y el precio con los que se
     * cobró, no la familia: eso congela el precio aunque el catálogo cambie,
     * pero obliga a resolver la familia contra el catálogo del trámite.
     */
    public function hasAddonKind(string $kind): bool
    {
        $this->loadMissing(['addons', 'document.availableAddons']);

        $catalogo = $this->document?->availableAddons;

        return $catalogo !== null && $this->addons->contains(
            fn ($linea) => $catalogo->firstWhere('key', $linea->key)?->kind->value === $kind
        );
    }

    /**
     * Un abogado escribe este documento.
     *
     * Mientras eso esté contratado, la entrega inmediata no se puede ofrecer:
     * el abogado necesita leer los hechos.
     */
    public function hasLawyerWork(): bool
    {
        return $this->hasAddonKind('review');
    }

    public function isFree(): bool
    {
        return (int) $this->amount === 0;
    }

    /**
     * El enlace que se le manda al cliente.
     *
     * Lleva la llave de acceso: sin ella la página de seguimiento no muestra
     * datos personales, porque la referencia sola se puede adivinar.
     */
    public function trackingUrl(): string
    {
        return route('checkout.confirmation', [
            'order' => $this->reference,
            't' => $this->access_token,
        ]);
    }

    public function downloadUrl(string $kind = 'documento'): string
    {
        return route('checkout.download', [
            'order' => $this->reference,
            'kind' => $kind,
            't' => $this->access_token,
        ]);
    }

    /** ¿Quien pide esta página puede ver sus datos? */
    public function grantsAccessWith(?string $token): bool
    {
        return $this->access_token !== null
            && $token !== null
            && hash_equals($this->access_token, $token);
    }
}
