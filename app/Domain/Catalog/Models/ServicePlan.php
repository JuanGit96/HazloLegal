<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Enums\PlanKey;
use App\Domain\Orders\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'key' => PlanKey::class,
            'includes' => 'array',
            'express_includes' => 'array',
            'is_quote_only' => 'boolean',
            'is_highlighted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function priceFor(DeliverySpeed $speed): ?int
    {
        return $speed === DeliverySpeed::Express
            ? ($this->express_price ?? $this->price)
            : $this->price;
    }

    public function deliveryHoursFor(DeliverySpeed $speed): ?int
    {
        return $speed === DeliverySpeed::Express
            ? ($this->express_delivery_hours ?? $this->delivery_hours)
            : $this->delivery_hours;
    }

    public function includesFor(DeliverySpeed $speed): array
    {
        return $speed === DeliverySpeed::Express
            ? ($this->express_includes ?: $this->includes ?: [])
            : ($this->includes ?: []);
    }

    public function hasSpeedChoice(): bool
    {
        return $this->key->hasSpeedChoice() && $this->express_price !== null;
    }
}
