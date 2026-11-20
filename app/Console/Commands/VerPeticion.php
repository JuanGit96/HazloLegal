<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Documents\Services\LegalDocumentPdfBuilder;
use App\Domain\Orders\Models\Order;
use Illuminate\Console\Command;

class VerPeticion extends Command
{
    protected $signature = 'hl:ver-peticion {reference=HL-DEMO-0001}';

    protected $description = 'Muestra el escrito armado con las casillas marcadas';

    public function handle(LegalDocumentPdfBuilder $pdf): int
    {
        $order = Order::query()->with('document')->where('reference', $this->argument('reference'))->firstOrFail();
        $e = $pdf->escrito($order);

        if (! $e) {
            $this->warn('Este trámite no trabaja con casillas.');

            return self::SUCCESS;
        }

        $this->line('ENTIDAD: '.$e['entidad']);
        $this->newLine();
        $this->line('HECHOS');
        foreach ($e['hechos'] as $i => $h) {
            $this->line('  '.($i + 1).'. '.($h['fecha'] ?? '—').' · '.$h['hecho']);
        }
        $this->newLine();
        $this->line('PETICIONES');
        foreach ($e['peticiones'] as $i => $p) {
            $this->line('  '.($i + 1).'. '.html_entity_decode(strip_tags($p)));
        }
        $this->newLine();
        $this->line('PLAZO: '.$e['plazo_dias'].' días hábiles');
        $this->newLine();
        $this->line('FUNDAMENTO: '.$e['fundamento']);
        $this->newLine();
        $this->line('VARIABLES SIN CONTESTAR: '.count($e['faltantes']));

        return self::SUCCESS;
    }
}
