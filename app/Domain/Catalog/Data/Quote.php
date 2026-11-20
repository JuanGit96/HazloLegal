<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use App\Domain\Orders\Enums\OrderTier;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * El precio resuelto de una combinación concreta: un trámite más los
 * adicionales elegidos. Es inmutable y se recalcula entero en cada cambio,
 * nunca se va sumando sobre el anterior.
 */
final readonly class Quote
{
    /** @param Collection<int, ServiceAddon> $addons */
    public function __construct(
        public LegalDocument $document,
        public Collection $addons,
        public int $baseAmount,
        public int $addonsAmount,
        public int $discountAmount,
        public int $total,
        public ?int $compareAtTotal,
        public int $deliveryHours,
        public OrderTier $tier,
    ) {}

    public function isFree(): bool
    {
        return $this->total === 0;
    }

    public function hasAddon(string $key): bool
    {
        return $this->addons->contains('key', $key);
    }

    public function formattedTotal(): string
    {
        return $this->isFree() ? 'Gratis' : self::cop($this->total);
    }

    public function formattedBase(): string
    {
        return $this->baseAmount === 0 ? 'Gratis' : self::cop($this->baseAmount);
    }

    public function formattedCompareAt(): ?string
    {
        return $this->compareAtTotal ? self::cop($this->compareAtTotal) : null;
    }

    /** Cuánto se ahorra frente al precio tachado. Munición para el copy. */
    public function savings(): int
    {
        return max(0, ($this->compareAtTotal ?? 0) - $this->total);
    }

    public function savingsPercentage(): int
    {
        if (! $this->compareAtTotal || $this->compareAtTotal <= 0) {
            return 0;
        }

        return (int) round(($this->savings() / $this->compareAtTotal) * 100);
    }

    public function promisedAt(): CarbonImmutable
    {
        return CarbonImmutable::now()->addHours($this->deliveryHours);
    }

    public function deliveryLabel(): string
    {
        return match (true) {
            $this->deliveryHours <= 0 => 'Ahora mismo',
            $this->deliveryHours <= 1 => 'En menos de 1 hora',
            $this->deliveryHours <= 24 => '24 horas hábiles',
            default => "{$this->deliveryHours} horas hábiles",
        };
    }

    public static function cop(int $amount): string
    {
        return '$'.number_format($amount, 0, ',', '.');
    }
}
