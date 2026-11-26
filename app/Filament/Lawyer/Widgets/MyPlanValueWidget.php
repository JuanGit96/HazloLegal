<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Widgets;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Lawyers\Services\CurrentLawyer;
use Filament\Widgets\Widget;

/**
 * Qué le ha devuelto la suscripción, en sus propios términos.
 *
 * La mayoría de los meses un abogado no gana un caso aquí, y si el panel
 * solo contara casos ganados parecería que no pasa nada. Pasa: lo vieron
 * cuatrocientas veces, su nombre circuló en ochenta PDF, dos personas le
 * escribieron. Eso es lo que compró, y es lo que hay que enseñarle.
 *
 * Nada de esto es una promesa de resultados. Son hechos ocurridos, con sus
 * cifras, y cuando una cifra es cero se dice que es cero y qué hacer para
 * moverla. Inventar un «valor equivalente en publicidad» sería justo el
 * tipo de cifra que la SIC sanciona.
 */
class MyPlanValueWidget extends Widget
{
    protected static ?int $sort = 3;

    // Son dos consultas agregadas: no hay nada que diferir.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.lawyer.widgets.my-plan-value';

    public function lawyer(): ?Lawyer
    {
        return app(CurrentLawyer::class)->get();
    }

    /**
     * Lo acumulado desde que entró, no los últimos treinta días.
     *
     * Aquí sí manda el total: la pregunta que responde este bloque es «qué
     * me ha dado esto», y la respuesta incluye todo lo que dio.
     */
    public function lines(): array
    {
        $lawyer = $this->lawyer();

        if (! $lawyer) {
            return [];
        }

        $metrics = app(MetricRecorder::class);
        $desde = $lawyer->subscription?->started_at;

        $kinds = [
            MetricKind::DirectoryImpression,
            MetricKind::ProfileView,
            MetricKind::ContactClick,
            MetricKind::ArticleView,
            MetricKind::PdfPlacement,
            MetricKind::CaseInvitation,
            MetricKind::CaseWon,
        ];

        $totales = $metrics->totals($lawyer, $kinds, $desde);

        return collect($kinds)
            ->map(fn (MetricKind $kind) => [
                'kind' => $kind,
                'value' => $totales[$kind->value],
                'hint' => $totales[$kind->value] === 0 ? $this->nudge($kind) : null,
            ])
            ->all();
    }

    /** Qué hacer cuando una cifra está en cero. Sin esto, el cero solo frustra. */
    private function nudge(MetricKind $kind): string
    {
        return match ($kind) {
            MetricKind::DirectoryImpression => 'Publica tu perfil desde «Mi perfil» para empezar a aparecer.',
            MetricKind::ProfileView => 'Un titular concreto —«Pensiones y reliquidaciones», no «abogado»— multiplica las aperturas.',
            MetricKind::ContactClick => 'Añade tu WhatsApp en «Mi perfil»: es el botón que más se pulsa.',
            MetricKind::ArticleView => 'Escríbenos para firmar una guía de tu especialidad. Es lo que más visitas trae a un perfil.',
            MetricKind::PdfPlacement => 'Tu plan actual no incluye aparecer en los instructivos.',
            MetricKind::CaseInvitation => 'Amplía tus áreas de práctica para recibir más casos.',
            MetricKind::CaseWon => 'Cuando te postules, escribe una nota corta explicando cómo lo resolverías: es lo que el cliente compara.',
        };
    }

    public function since(): ?string
    {
        return $this->lawyer()?->subscription?->started_at?->translatedFormat('F \d\e Y');
    }
}
