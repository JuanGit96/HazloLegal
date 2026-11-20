<?php

declare(strict_types=1);

namespace App\Domain\Orders\Models;

use App\Domain\Catalog\Models\ServiceAddon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un adicional tal como se vendió.
 *
 * Guarda nombre y precio congelados: si mañana sube el express, el pedido
 * de ayer tiene que seguir diciendo lo que el cliente pagó.
 */
class OrderAddon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(ServiceAddon::class, 'service_addon_id');
    }

    public function formattedPrice(): string
    {
        return $this->price === 0 ? 'Incluido' : '$'.number_format($this->price, 0, ',', '.');
    }
}
