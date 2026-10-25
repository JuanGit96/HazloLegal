<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Models\CaseOffer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/** Mide la salud de la red: si nadie acepta, el servicio no funciona. */
class LawyerResponseWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Respuesta de los abogados';

    public function getDescription(): ?string
    {
        return 'Qué hacen los abogados con las invitaciones que reciben.';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = CaseOffer::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = CaseOfferStatus::cases();

        return [
            'datasets' => [[
                'label' => 'Invitaciones',
                'data' => collect($statuses)->map(fn ($s) => (int) ($counts[$s->value] ?? 0))->all(),
                'backgroundColor' => ['#F59E0B', '#059669', '#DC2626', '#94A3B8', '#64748B'],
                'borderWidth' => 0,
            ]],
            'labels' => collect($statuses)->map(fn ($s) => $s->getLabel())->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
