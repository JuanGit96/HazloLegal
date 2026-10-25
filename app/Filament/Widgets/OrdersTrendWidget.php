<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Orders\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersTrendWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'Pedidos de los últimos 30 días';

    public function getDescription(): ?string
    {
        return 'Cuántos pedidos entran cada día y cuántos se pagan.';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn (int $d) => now()->subDays($d));

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos creados',
                    'data' => $days
                        ->map(fn ($day) => Order::query()->whereDate('created_at', $day->toDateString())->count())
                        ->all(),
                    'borderColor' => '#2563EB',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Pedidos pagados',
                    'data' => $days
                        ->map(fn ($day) => Order::query()->whereDate('paid_at', $day->toDateString())->count())
                        ->all(),
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $days->map(fn ($day) => $day->format('d M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
