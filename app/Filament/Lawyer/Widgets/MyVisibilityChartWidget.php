<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Widgets;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Lawyers\Services\CurrentLawyer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * La curva de visibilidad, que es lo que se compra con la suscripción.
 *
 * Tres líneas y no una: salir en un listado, que abran tu ficha y que
 * pulsen para escribirte son tres pasos del mismo embudo, y verlos juntos
 * dice dónde se está perdiendo la gente. Si hay muchas impresiones y pocas
 * aperturas, el problema es el titular; si hay aperturas y nadie escribe,
 * el problema es la ficha. Eso es accionable; un número suelto no.
 */
class MyVisibilityChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Tu visibilidad, día a día';

    public ?string $filter = '30';

    public function getDescription(): ?string
    {
        return 'De aparecer en una búsqueda a que te escriban. Donde se cae la línea está lo que hay que arreglar.';
    }

    protected function getFilters(): ?array
    {
        return [
            '14' => 'Últimas 2 semanas',
            '30' => 'Últimos 30 días',
            '90' => 'Últimos 3 meses',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $lawyer = app(CurrentLawyer::class)->get();
        $dias = (int) ($this->filter ?: 30);

        if (! $lawyer) {
            return ['datasets' => [], 'labels' => []];
        }

        $metrics = app(MetricRecorder::class);

        $lineas = [
            [MetricKind::DirectoryImpression, '#94A3B8', 'rgba(148, 163, 184, 0.10)'],
            [MetricKind::ProfileView, '#2563EB', 'rgba(37, 99, 235, 0.12)'],
            [MetricKind::ContactClick, '#F97316', 'rgba(249, 115, 22, 0.16)'],
        ];

        $labels = null;

        $datasets = array_map(function (array $linea) use ($lawyer, $metrics, $dias, &$labels) {
            [$kind, $borde, $fondo] = $linea;
            $serie = $metrics->series($lawyer, $kind, $dias);

            $labels ??= array_map(
                fn (string $fecha) => Carbon::parse($fecha)->translatedFormat('j M'),
                array_keys($serie),
            );

            return [
                'label' => $kind->getLabel(),
                'data' => array_values($serie),
                'borderColor' => $borde,
                'backgroundColor' => $fondo,
                'fill' => true,
                'tension' => 0.35,
                'pointRadius' => 0,
                'pointHoverRadius' => 4,
            ];
        }, $lineas);

        return ['datasets' => $datasets, 'labels' => $labels ?? []];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                // Sin decimales: media visita no existe y confunde.
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'interaction' => ['mode' => 'index', 'intersect' => false],
        ];
    }
}
