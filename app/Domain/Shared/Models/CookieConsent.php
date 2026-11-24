<?php

declare(strict_types=1);

namespace App\Domain\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Una decisión sobre cookies, guardada para poder probarla.
 *
 * Es un registro de solo escritura: cada cambio de opinión crea una fila
 * nueva en vez de editar la anterior, porque lo que hay que poder demostrar
 * es la secuencia —qué autorizó, cuándo, y cuándo lo revocó—, no el último
 * estado.
 */
class CookieConsent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'analytics' => 'boolean',
            'marketing' => 'boolean',
        ];
    }

    /** Las categorías opcionales que esta decisión autorizó. */
    public function granted(): array
    {
        return array_keys(array_filter([
            'analytics' => $this->analytics,
            'marketing' => $this->marketing,
        ]));
    }
}
