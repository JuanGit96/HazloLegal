<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Console\Command;

/** Cuánto de cada producto está realmente construido. */
class ProductosResumen extends Command
{
    protected $signature = 'hl:productos';

    protected $description = 'Resumen de lo que tiene cada trámite';

    public function handle(): int
    {
        $this->line(sprintf(
            '%-42s %-8s %-9s %-9s %-7s %-8s %s',
            'TRÁMITE', 'CAMPOS', 'CASILLAS', 'PLANTILLA', 'PASOS', 'AVISOS', 'GLOSARIO'
        ));

        foreach (LegalDocument::query()->withCount(['clauses', 'riskRules'])->orderBy('sort_order')->get() as $d) {
            $this->line(sprintf(
                '%-42s %-8s %-9s %-9s %-7s %-8s %s',
                $d->slug,
                count($d->template_fields ?? []),
                $d->clauses_count,
                $d->template_body ? 'sí' : '—',
                count($d->filing_steps ?? []),
                count($d->instructions_warnings ?? []),
                count($d->glossary ?? []),
            ));
        }

        return self::SUCCESS;
    }
}
