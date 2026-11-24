<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Models\ServiceAddon;

/** Un adicional, ya resuelto para un trámite y listo para pintarse. */
final readonly class AddonOffer
{
    public function __construct(
        public ServiceAddon $addon,
        public int $price,
        public ?int $compareAtPrice,
        public bool $isRecommended,
        public bool $isPreselected,
        public bool $isSelected,
        public ?string $reason = null,
        /** Incompatible con algo que el pedido ya lleva: no se puede ofrecer. */
        public bool $isBlocked = false,
    ) {}

    public function key(): string
    {
        return $this->addon->key;
    }

    public function formattedPrice(): string
    {
        return $this->price === 0 ? 'Incluido' : Quote::cop($this->price);
    }

    public function formattedCompareAt(): ?string
    {
        return $this->compareAtPrice ? Quote::cop($this->compareAtPrice) : null;
    }

    public function savings(): int
    {
        return max(0, ($this->compareAtPrice ?? 0) - $this->price);
    }
}
