<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Documents\Services\InstructionsPdfBuilder;
use App\Domain\Documents\Services\LegalDocumentPdfBuilder;
use App\Domain\Orders\Models\Order;
use Illuminate\Console\Command;

/**
 * Genera el documento y el instructivo de muestra de un trámite.
 *
 * Sirve para revisar cómo quedan sin recorrer el checkout entero. Rellena
 * los campos y las casillas con valores de ejemplo tomados de la propia
 * definición del producto.
 */
class DemoProducto extends Command
{
    protected $signature = 'hl:demo {slug}';

    protected $description = 'Genera el PDF de muestra de un trámite';

    public function handle(LegalDocumentPdfBuilder $documentos, InstructionsPdfBuilder $instructivos): int
    {
        $document = LegalDocument::query()->where('slug', $this->argument('slug'))->firstOrFail();

        $order = Order::query()->updateOrCreate(
            ['reference' => 'HL-DEMO-'.strtoupper(substr(md5($document->slug), 0, 6))],
            [
                'access_token' => bin2hex(random_bytes(16)),
                'legal_document_id' => $document->id,
                'status' => 'paid',
                'tier' => 'free',
                'customer_name' => 'Gloria Herrera Peña',
                'customer_email' => 'gloria.herrera@example.com',
                'customer_phone' => '300 123 4567',
                'customer_document_type' => 'CC',
                'customer_document_number' => '52.481.093',
                'customer_city' => 'Bogotá',
                'case_summary' => 'Descripción de ejemplo de lo que pasó, para ver cómo queda el documento.',
                'amount' => 0,
                'paid_at' => now(),
                'answers' => $this->respuestas($document),
            ],
        );

        $doc = $documentos->build($order->refresh());
        $ins = $instructivos->build($order->refresh());

        $this->info($document->name);
        $this->line('  documento:  '.$doc);
        $this->line('  instructivo: '.$ins);

        return self::SUCCESS;
    }

    /** Valores de ejemplo para los campos y las casillas del trámite. */
    private function respuestas(LegalDocument $document): array
    {
        $campos = [];
        foreach ($document->template_fields ?? [] as $campo) {
            $campos[$campo['key']] = [
                'label' => $campo['label'],
                'type' => $campo['type'] ?? 'text',
                'value' => $this->ejemplo($campo),
            ];
        }

        $casillas = $document->clauses()->where('is_active', true)->where('is_free_text', false)
            ->limit(3)->get();

        $valores = [];
        foreach ($casillas as $casilla) {
            foreach ($casilla->variables ?? [] as $variable) {
                $valores[$casilla->key][$variable['key']] = $this->ejemplo($variable);
            }
        }

        return [
            'documento' => $document->name,
            'entidad' => $campos['entidad']['value'] ?? $campos['empresa']['value'] ?? $document->filing_entity,
            'campos' => $campos,
            'casillas' => $casillas->pluck('key')->all(),
            'valores' => $valores,
            'hechos' => [
                ['fecha' => '2026-01-14', 'hecho' => 'ocurrió el primer hecho relevante y quedó registrado con el número 00123.'],
                ['fecha' => '2026-02-03', 'hecho' => 'me informaron por teléfono que el caso seguía en estudio, sin darme una fecha.'],
                ['fecha' => '2026-02-20', 'hecho' => 'volví a insistir y no obtuve respuesta escrita.'],
            ],
        ];
    }

    private function ejemplo(array $campo): string
    {
        return match ($campo['type'] ?? 'text') {
            'date' => '14 de enero de 2026',
            'number' => '1.250.000',
            default => 'Ejemplo de '.mb_strtolower($campo['label']),
        };
    }
}
