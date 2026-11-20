<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Documents\Services\InstructionsPdfBuilder;
use App\Domain\Documents\Services\LegalDocumentPdfBuilder;
use App\Domain\Orders\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Cuenta las páginas de los PDF de un pedido.
 *
 * dompdf produce páginas en blanco con facilidad (una caja un pixel más alta
 * que la página basta), y eso no se ve en ningún test de integración: hay que
 * contar las páginas del archivo.
 */
class PdfPageCount extends Command
{
    protected $signature = 'hl:pdf-pages {reference? : Referencia del pedido}';

    protected $description = 'Genera los PDF de un pedido y cuenta sus páginas';

    public function handle(
        InstructionsPdfBuilder $instructions,
        LegalDocumentPdfBuilder $documents,
    ): int {
        $order = $this->argument('reference')
            ? Order::query()->where('reference', $this->argument('reference'))->first()
            : Order::query()->with('document')->first();

        if (! $order) {
            $this->error('No hay pedidos.');

            return self::FAILURE;
        }

        $order->loadMissing(['document', 'addons']);

        foreach (['instructivo' => $instructions->build($order), 'documento' => $documents->build($order)] as $label => $path) {
            $this->line(sprintf('%-12s %d páginas  (%s)', $label, $this->pages($path), $path));
        }

        return self::SUCCESS;
    }

    private function pages(string $path): int
    {
        $raw = Storage::disk('local')->get($path);
        preg_match_all('#/Type\s*/Page[^s]#', (string) $raw, $matches);

        return count($matches[0]);
    }
}
