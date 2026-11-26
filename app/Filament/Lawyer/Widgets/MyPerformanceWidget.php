<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Widgets;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Lawyers\Services\CurrentLawyer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Lo que el abogado necesita ver para decidir si renueva.
 *
 * Son sus números, no los del negocio: cuánta gente lo vio, cuánta quiso
 * escribirle, cuántos casos le ofrecimos y cuántos ganó. Si estas cifras no
 * crecen, la suscripción no vale lo que cuesta, y es mejor que lo vea él
 * antes que descubrirlo cuando se va.
 *
 * Se muestran los últimos 30 días y no el acumulado de siempre: un total que
 * solo sube parece bueno aunque lleve meses plano, y la pregunta al renovar
 * es si esto sigue funcionando, no si alguna vez funcionó.
 */
class MyPerformanceWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Tus últimos 30 días';

    protected ?string $description = 'Comparado con los 30 anteriores.';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $lawyer = app(CurrentLawyer::class)->get();

        if (! $lawyer) {
            return [];
        }

        $metrics = app(MetricRecorder::class);
        $desde = now()->subDays(30);

        $totales = $metrics->totals($lawyer, [
            MetricKind::DirectoryImpression,
            MetricKind::ProfileView,
            MetricKind::ContactClick,
            MetricKind::ArticleView,
            MetricKind::CaseInvitation,
            MetricKind::CaseApplication,
            MetricKind::CaseWon,
        ], $desde);

        $vistas = $totales[MetricKind::ProfileView->value];
        $clics = $totales[MetricKind::ContactClick->value];
        $ofrecidos = $totales[MetricKind::CaseInvitation->value];
        $ganados = $totales[MetricKind::CaseWon->value];
        $postulado = $totales[MetricKind::CaseApplication->value];

        return [
            $this->stat(MetricKind::DirectoryImpression, $totales[MetricKind::DirectoryImpression->value], $metrics, $lawyer)
                ->description($lawyer->is_published
                    ? 'Veces que saliste en un listado'
                    : 'Tu perfil está sin publicar: no apareces'),

            $this->stat(MetricKind::ProfileView, $vistas, $metrics, $lawyer)
                ->description($vistas > 0
                    ? $this->porcentaje($clics, $vistas).' de quienes te vieron quisieron escribirte'
                    : 'Todavía nadie abrió tu ficha'),

            $this->stat(MetricKind::ArticleView, $totales[MetricKind::ArticleView->value], $metrics, $lawyer)
                ->description($totales[MetricKind::ArticleView->value] > 0
                    ? 'Lecturas de las guías que firmas'
                    : 'Escribir una guía es lo que más tráfico trae'),

            $this->stat(MetricKind::CaseWon, $ganados, $metrics, $lawyer)
                ->description(match (true) {
                    $ofrecidos === 0 => 'Aún no te hemos ofrecido casos',
                    $postulado === 0 => "Te ofrecimos {$ofrecidos} y no te postulaste a ninguno",
                    default => "De {$ofrecidos} ofrecidos, te postulaste a {$postulado}",
                }),
        ];
    }

    private function stat(MetricKind $kind, int $valor, MetricRecorder $metrics, $lawyer): Stat
    {
        $tendencia = $metrics->trend($lawyer, $kind);

        return Stat::make($kind->getLabel(), number_format($valor, 0, ',', '.'))
            ->descriptionIcon($kind->getIcon())
            ->descriptionColor($tendencia === null ? 'gray' : ($tendencia >= 0 ? 'success' : 'danger'))
            ->color($valor > 0 ? $kind->getColor() : 'gray')
            // Sin el periodo anterior no hay comparación honesta: un «+100 %»
            // contra cero no significa nada.
            ->chart($tendencia === null ? [] : array_values($metrics->series($lawyer, $kind, 14)));
    }

    private function porcentaje(int $parte, int $total): string
    {
        return $total > 0 ? round(($parte / $total) * 100).' %' : '0 %';
    }
}
