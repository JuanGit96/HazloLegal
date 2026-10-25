<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Orders\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopDocumentsWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Qué trámites generan los ingresos';

    public function getDescription(): ?string
    {
        return 'Suma de pedidos pagados, por trámite.';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $rows = Order::query()
            ->revenue()
            ->select('legal_document_id', DB::raw('SUM(amount) as total'))
            ->groupBy('legal_document_id')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $names = LegalDocument::query()
            ->whereKey($rows->pluck('legal_document_id'))
            ->pluck('short_name', 'id');

        return [
            'datasets' => [[
                'label' => 'Ingresos',
                'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
                'backgroundColor' => ['#2563EB', '#EA580C', '#059669', '#8B5CF6', '#F59E0B', '#64748B'],
                'borderWidth' => 0,
            ]],
            'labels' => $rows->map(fn ($r) => $names[$r->legal_document_id] ?? 'Sin nombre')->all(),
        ];
    }
}
