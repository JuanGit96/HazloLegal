<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Console\Command;

/** Qué adicionales se ofrecen en cada trámite. */
class AdicionalesResumen extends Command
{
    protected $signature = 'hl:adicionales';

    protected $description = 'Muestra los adicionales que ofrece cada trámite';

    public function handle(): int
    {
        foreach (LegalDocument::query()->with('availableAddons')->orderBy('slug')->get() as $d) {
            $this->line(sprintf(
                '%-44s %-5s %s',
                $d->slug,
                $d->availableAddons->count(),
                $d->availableAddons->pluck('key')->implode(', ') ?: '— ninguno —',
            ));
        }

        return self::SUCCESS;
    }
}
