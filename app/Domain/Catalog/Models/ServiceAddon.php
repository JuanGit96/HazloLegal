<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\AddonKind;
use App\Domain\Shared\Concerns\RoundsPrices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Un «topping»: lo que el cliente puede sumarle a cualquier trámite.
 *
 * El precio por defecto vive aquí; el precio real para un trámite concreto
 * puede sobrescribirse en la tabla pivote, porque acompañar una tutela no
 * cuesta lo mismo que acompañar un NDA.
 */
class ServiceAddon extends Model
{
    use HasFactory;
    use RoundsPrices;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kind' => AddonKind::class,
            'bullets' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(LegalDocument::class)
            ->withPivot(['price', 'is_available', 'is_recommended', 'is_preselected', 'sort_order'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Precio para un trámite concreto: el del pivote si existe, si no el propio. */
    public function priceFor(?LegalDocument $document = null): int
    {
        $override = $document?->loadMissing('addons')->addons->firstWhere('id', $this->id)?->pivot?->price;

        return (int) ($override ?? $this->default_price);
    }

    /** Precio tachado, solo si hay descuento configurado. */
    public function compareAtPrice(?LegalDocument $document = null): ?int
    {
        if ($this->discount_percentage <= 0) {
            return null;
        }

        $price = $this->priceFor($document);

        return $price > 0
            ? $this->roundToPricePoint($price / (1 - $this->discount_percentage / 100))
            : null;
    }

    public function isFree(?LegalDocument $document = null): bool
    {
        return $this->priceFor($document) === 0;
    }
}
