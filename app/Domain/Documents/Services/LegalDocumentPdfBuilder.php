<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

use App\Domain\Catalog\Services\ClauseComposer;
use App\Domain\Orders\Models\Order;
use App\Domain\Settings\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Arma el documento que el cliente va a radicar.
 *
 * El cuerpo sale de la plantilla del trámite, que el equipo edita desde el
 * panel. Los marcadores se reemplazan con los datos del pedido; nunca se
 * inventa contenido legal aquí, porque un párrafo de más en un derecho de
 * petición puede cambiar lo que la entidad entiende que se le pide.
 */
final class LegalDocumentPdfBuilder
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ClauseComposer $clauses,
    ) {}

    public function build(Order $order): string
    {
        $order->loadMissing('document');

        $signature = $order->signatureRequest()->where('status', 'signed')->latest('signed_at')->first();

        $pdf = Pdf::loadView('pdf.document', [
            'order' => $order,
            'document' => $order->document,
            'heading' => $order->document->template_heading ?: 'SEÑORES',
            'body' => $this->renderBody($order),
            // Si el trámite tiene casillas de petición, el escrito se arma
            // con ellas: las peticiones numeradas, el fundamento sin normas
            // repetidas y el cierre. Es lo que distingue este producto de
            // una plantilla con huecos.
            'escrito' => $this->escrito($order),
            'signature' => $signature,
            // dompdf sabe pintar SVG embebido gracias a php-svg-lib; va en
            // base64 porque un <svg> inline no lo renderiza.
            'signatureUri' => $signature?->signature_data
                ? 'data:image/svg+xml;base64,'.base64_encode($signature->signature_data)
                : null,
            'brand' => [
                'name' => $this->settings->get('brand_name', config('hazlolegal.brand.name')),
                'site' => config('app.url'),
            ],
        ])->setPaper('letter');

        $path = "orders/{$order->reference}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $order->update(['document_path' => $path]);

        return $path;
    }

    /**
     * El escrito armado a partir de las casillas marcadas.
     *
     * Devuelve null cuando el trámite no trabaja con casillas: entonces
     * manda la plantilla de texto libre.
     */
    public function escrito(Order $order): ?array
    {
        $document = $order->document;

        if ($document->clauses()->where('is_active', true)->doesntExist()) {
            return null;
        }

        $seleccion = (array) ($order->answers['casillas'] ?? []);
        $valores = (array) ($order->answers['valores'] ?? []);

        if ($seleccion === []) {
            return null;
        }

        $compuesto = $this->clauses->compose($document, $seleccion, $valores);

        return [
            ...$compuesto,
            'hechos' => $this->hechos($order),
            'entidad' => $order->answers['entidad'] ?? $document->filing_entity,
        ];
    }

    /**
     * Los hechos, numerados y ordenados por fecha.
     *
     * La especificación lo pide así porque una entidad lee mucho mejor una
     * cronología que un párrafo corrido, y porque fija qué pasó y cuándo.
     */
    private function hechos(Order $order): array
    {
        $hechos = collect((array) ($order->answers['hechos'] ?? []))
            ->filter(fn ($h) => filled($h['hecho'] ?? null))
            ->sortBy(fn ($h) => $h['fecha'] ?? '')
            ->values();

        if ($hechos->isEmpty() && filled($order->case_summary)) {
            return [['fecha' => null, 'hecho' => $order->case_summary]];
        }

        return $hechos->all();
    }

    /**
     * Sustituye los marcadores de la plantilla.
     *
     * Se escapa todo lo que viene del cliente: la plantilla la escribe el
     * equipo, pero el contenido lo escribe un desconocido.
     */
    public function renderBody(Order $order): string
    {
        $template = $order->document->template_body ?: $this->fallbackTemplate();

        $replacements = [
            'cliente.nombre' => $order->customer_name,
            'cliente.documento' => trim($order->customer_document_type.' '.$order->customer_document_number),
            'cliente.correo' => $order->customer_email,
            'cliente.telefono' => $order->customer_phone,
            'cliente.ciudad' => $order->customer_city ?: 'Colombia',
            'caso.resumen' => $order->case_summary,
            'pedido.referencia' => $order->reference,
            'fecha.larga' => now()->translatedFormat('j \d\e F \d\e Y'),
            'tramite.nombre' => $order->document->name,
        ];

        // Las respuestas a las preguntas propias del trámite, como
        // {{ campo.entidad }}. Se guardan con su etiqueta y su tipo, así que
        // una fecha se escribe «14 de enero de 2026» y no «2026-01-14».
        foreach (($order->answers['campos'] ?? []) as $key => $row) {
            $field = \App\Domain\Catalog\Data\DocumentField::fromArray([
                'key' => $key,
                'label' => $row['label'] ?? $key,
                'type' => $row['type'] ?? 'text',
            ]);

            $replacements['campo.'.$key] = $field?->format($row['value'] ?? null) ?? '';
        }

        $rendered = preg_replace_callback(
            '/\{\{\s*([a-z._]+)\s*\}\}/i',
            fn (array $m) => e($replacements[$m[1]] ?? ''),
            $template,
        );

        return (string) $rendered;
    }

    /** Respaldo mínimo para que un trámite sin plantilla aún entregue algo útil. */
    private function fallbackTemplate(): string
    {
        return implode("\n\n", [
            '{{ cliente.ciudad }}, {{ fecha.larga }}',
            'Respetados señores:',
            'Yo, {{ cliente.nombre }}, identificado con {{ cliente.documento }}, '
                .'en ejercicio de los derechos que me reconocen la Constitución y la ley, '
                .'me permito manifestar lo siguiente:',
            '{{ caso.resumen }}',
            'Por lo anterior, solicito que se dé respuesta de fondo a esta petición dentro '
                .'de los términos legales y que se me notifique al correo {{ cliente.correo }}.',
            'Atentamente,',
            "{{ cliente.nombre }}\n{{ cliente.documento }}\n{{ cliente.telefono }}",
        ]);
    }

    public function absolutePath(Order $order): ?string
    {
        return $order->document_path
            ? Storage::disk('local')->path($order->document_path)
            : null;
    }

    /** Nombre amable para la descarga. */
    public static function fileName(Order $order): string
    {
        return Str::slug($order->document->short_name ?: $order->document->name).'-'.$order->reference.'.pdf';
    }
}
