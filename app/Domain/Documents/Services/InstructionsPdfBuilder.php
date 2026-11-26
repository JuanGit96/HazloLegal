<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Catalog\Services\ClauseComposer;
use App\Domain\Orders\Models\Order;
use App\Domain\Settings\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

/**
 * Genera el instructivo que acompaña a cada documento.
 *
 * Cumple dos funciones a la vez y por eso recibe tanto cuidado:
 *
 * 1. Es lo que el cliente paga en el adicional «instructivo»: dónde radicar,
 *    qué anexar, qué plazo tiene la entidad y qué hacer si no responde.
 * 2. Es publicidad que circula. El PDF se reenvía, se imprime y se entrega en
 *    ventanilla. Lleva marca, y lleva al abogado suscrito del área.
 *
 * Se guarda en disco para poder reenviarlo sin recalcular y para que el
 * enlace sea temporal y firmado, no una URL pública adivinable.
 */
final class InstructionsPdfBuilder
{
    public function __construct(
        private readonly LawyerPlacementService $placement,
        private readonly SettingsService $settings,
    ) {}

    public function build(Order $order): string
    {
        $order->loadMissing(['document', 'addons', 'featuredLawyer']);

        $lawyers = $this->placement->forArea($order->document->practice_area, 2);

        $pdf = Pdf::loadView('pdf.instructions', [
            'order' => $order,
            'document' => $order->document,
            'lawyers' => $lawyers,
            'brand' => [
                'name' => $this->settings->get('brand_name', config('hazlolegal.brand.name')),
                'tagline' => $this->settings->get('brand_tagline', config('hazlolegal.brand.tagline')),
                'site' => config('app.url'),
                'whatsapp' => config('hazlolegal.contact.whatsapp_display'),
                'email' => config('hazlolegal.contact.email'),
            ],
            'steps' => $this->stepsFor($order),
            'attachments' => $order->document->instructions_attachments ?? [],
            'warnings' => $order->document->instructions_warnings ?? [],
            'glossary' => $order->document->glossary ?? [],
            'deadlineDays' => $this->deadlineDays($order),
            'deadlineDate' => $this->deadlineDate($order),
        ])->setPaper('letter');

        $path = "instructions/{$order->reference}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $order->update(['instructions_path' => $path]);

        if ($lawyers->isNotEmpty()) {
            $order->update(['featured_lawyer_id' => $lawyers->first()->id]);
            $lawyers->first()->forceFill(['last_placement_at' => now()])->saveQuietly();

            // Ese PDF se imprime y se lleva a una ventanilla: es la
            // colocación más difícil de explicar y la más fácil de olvidar
            // si no se cuenta.
            app(MetricRecorder::class)->record($lawyers->first(), MetricKind::PdfPlacement);
        }

        return $path;
    }

    /**
     * El plazo que de verdad aplica a este pedido.
     *
     * Puede venir de las casillas marcadas —pedir la historia clínica corre
     * a diez días y no a quince— así que se consulta ahí antes que al
     * trámite.
     */
    private function deadlineDays(Order $order): ?int
    {
        $seleccion = (array) ($order->answers['casillas'] ?? []);

        if ($seleccion !== []) {
            $compuesto = app(ClauseComposer::class)
                ->compose($order->document, $seleccion);

            return $compuesto['plazo_dias'] ?? null;
        }

        return $order->document->response_deadline_days
            ? (int) $order->document->response_deadline_days
            : null;
    }

    /**
     * La fecha de vencimiento si se radica hoy.
     *
     * Se cuenta en días hábiles desde el día siguiente, que es como la
     * cuentan las entidades. Decir «quince días» a secas hace que la gente
     * cuente de corrido y reclame antes de tiempo.
     */
    private function deadlineDate(Order $order): ?CarbonInterface
    {
        $dias = $this->deadlineDays($order);

        return $dias ? now()->addWeekdays($dias) : null;
    }

    /**
     * Los pasos de radicación.
     *
     * Se toman de lo que el equipo escribió en la ficha del trámite; el
     * respaldo genérico existe para que nunca salga un instructivo vacío.
     */
    private function stepsFor(Order $order): array
    {
        $configured = $order->document->filing_steps ?? null;

        if (filled($configured)) {
            return $configured;
        }

        return [
            [
                'title' => 'Revisa tus datos',
                'body' => 'Antes de firmar, comprueba que tu nombre, documento y los datos de la entidad estén exactos. Un dígito mal en una cédula es la causa más común de rechazo.',
            ],
            [
                'title' => 'Firma el documento',
                'body' => 'Imprime y firma, o usa la firma electrónica si contrataste ese adicional. La firma debe coincidir con la de tu documento de identidad.',
            ],
            [
                'title' => 'Radica y pide constancia',
                'body' => 'Entrega el documento en la ventanilla de la entidad o por su canal digital. Exige siempre el número de radicado: sin él no puedes probar que lo presentaste.',
            ],
            [
                'title' => 'Vigila el plazo',
                'body' => 'La entidad tiene un plazo legal para responder. Si se vence sin respuesta, puedes acudir a una acción de tutela por violación del derecho de petición.',
            ],
        ];
    }

    /** Ruta absoluta para adjuntar al correo. */
    public function absolutePath(Order $order): ?string
    {
        return $order->instructions_path
            ? Storage::disk('local')->path($order->instructions_path)
            : null;
    }
}
