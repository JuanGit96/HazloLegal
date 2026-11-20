<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Models\DocumentClause;
use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Support\Collection;

/**
 * Arma el cuerpo del escrito a partir de lo que el usuario marcó.
 *
 * Tres reglas que vienen de la especificación y que conviene no perder:
 *
 * - Las peticiones van numeradas y en el orden del catálogo, no en el
 *   orden en que el usuario las fue marcando.
 * - El fundamento se concatena **sin repetir normas**: un escrito que cita
 *   tres veces la misma ley se lee como plantilla mal armada.
 * - El plazo lo manda la casilla más corta. Pedir la historia clínica son
 *   diez días hábiles, y si va junto a otras peticiones el usuario tiene
 *   derecho a que el contador respete el plazo que de verdad aplica.
 */
final class ClauseComposer
{
    /**
     * @param  array<int, string>  $keys  Casillas marcadas
     * @param  array<string, array<string, mixed>>  $valores  Variables por casilla
     */
    public function compose(LegalDocument $document, array $keys, array $valores = []): array
    {
        $clauses = $this->selected($document, $keys);

        return [
            'peticiones' => $this->peticiones($clauses, $valores),
            'fundamento' => $this->fundamento($document, $clauses),
            'cierre' => $document->clause_closing,
            'plazo_dias' => $this->plazo($document, $clauses),
            'faltantes' => $this->faltantes($clauses, $valores),
        ];
    }

    /** @return Collection<int, DocumentClause> */
    public function selected(LegalDocument $document, array $keys): Collection
    {
        $marcadas = collect($keys)->filter()->map(fn ($k) => (string) $k);

        return $document->clauses()
            ->where('is_active', true)
            ->get()
            ->filter(fn (DocumentClause $c) => $marcadas->contains($c->key))
            ->values();
    }

    /** @return array<int, string> */
    private function peticiones(Collection $clauses, array $valores): array
    {
        return $clauses
            ->map(fn (DocumentClause $c) => $c->render($valores[$c->key] ?? []))
            ->filter(fn (string $t) => trim($t) !== '')
            ->values()
            ->all();
    }

    private function fundamento(LegalDocument $document, Collection $clauses): string
    {
        $normas = collect([$document->clause_common_basis])
            ->concat($clauses->pluck('legal_basis'))
            ->filter()
            ->map(fn (string $n) => trim($n))
            ->unique()
            ->values();

        return $normas->implode(' ');
    }

    private function plazo(LegalDocument $document, Collection $clauses): int
    {
        $propios = $clauses->pluck('response_deadline_days')->filter()->all();

        $general = (int) ($document->response_deadline_days ?: 15);

        // Si todas las marcadas traen plazo propio, manda el más corto de
        // ellas; si hay alguna sin plazo propio, rige el general.
        if ($propios !== [] && count($propios) === $clauses->count()) {
            return min($propios);
        }

        return $general;
    }

    /** Qué variables quedaron sin contestar, para avisar antes de generar. */
    private function faltantes(Collection $clauses, array $valores): array
    {
        $faltan = [];

        foreach ($clauses as $clause) {
            foreach ($clause->placeholders() as $hueco) {
                if (blank($valores[$clause->key][$hueco] ?? null)) {
                    $faltan[] = ['clause' => $clause->key, 'variable' => $hueco];
                }
            }
        }

        return $faltan;
    }
}
