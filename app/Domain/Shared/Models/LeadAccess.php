<?php

declare(strict_types=1);

namespace App\Domain\Shared\Models;

use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de qué abogado vio qué lead y cuándo.
 *
 * No es telemetría: es la prueba de diligencia que hay que poder enseñar si
 * un titular ejerce su derecho a saber quién accedió a sus datos.
 */
class LeadAccess extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['accessed_at' => 'datetime'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }
}
