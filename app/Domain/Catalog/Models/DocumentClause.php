<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una casilla de petición: lo que el usuario marca y lo que eso escribe.
 *
 * El texto es exacto a propósito. Un derecho de petición mal redactado no
 * se nota hasta que la entidad contesta otra cosa, así que la redacción la
 * aprueba un abogado una vez y se reutiliza, en vez de improvisarse en cada
 * pedido.
 */
class DocumentClause extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_free_text' => 'boolean',
            'needs_lawyer_review' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Las {llaves} que el texto necesita que alguien rellene. */
    public function placeholders(): array
    {
        preg_match_all('/\{([^}]+)\}/', (string) $this->body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /** El texto con los valores puestos. Lo que no se contestó queda marcado. */
    public function render(array $valores): string
    {
        return (string) preg_replace_callback(
            '/\{([^}]+)\}/',
            function (array $m) use ($valores) {
                $valor = $valores[$m[1]] ?? null;

                // Un hueco sin rellenar se deja visible: es mejor que el
                // usuario vea «[falta: fecha]» y lo corrija a que firme un
                // escrito con una frase coja.
                return filled($valor) ? e((string) $valor) : '[falta: '.$m[1].']';
            },
            (string) $this->body,
        );
    }
}
