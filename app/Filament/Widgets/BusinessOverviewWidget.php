<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Models\GuaranteeClaim;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Los cuatro números que el equipo debe mirar cada mañana. */
class BusinessOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $open = Order::query()->open()->count();
        $overdue = Order::query()->overdue()->count();

        $revenueThisMonth = (int) Order::query()
            ->revenue()
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        $revenueLastMonth = (int) Order::query()
            ->revenue()
            ->whereBetween('paid_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum('amount');

        $growth = $revenueLastMonth > 0
            ? (int) round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100)
            : null;

        $unassignedCases = LegalCase::query()->open()->count();
        $openClaims = GuaranteeClaim::query()->open()->count();

        return [
            Stat::make('Pedidos por atender', (string) $open)
                ->description($overdue > 0 ? "{$overdue} vencidos" : 'Ninguno vencido')
                ->descriptionIcon($overdue > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckBadge)
                ->descriptionColor($overdue > 0 ? 'danger' : 'success')
                ->color($overdue > 0 ? 'danger' : 'primary')
                ->chart($this->ordersLast7Days()),

            Stat::make('Ingresos del mes', '$'.number_format($revenueThisMonth, 0, ',', '.'))
                ->description($growth === null
                    ? 'Sin dato del mes anterior'
                    : ($growth >= 0 ? "+{$growth} % vs. mes anterior" : "{$growth} % vs. mes anterior"))
                ->descriptionIcon($growth !== null && $growth >= 0
                    ? Heroicon::OutlinedArrowTrendingUp
                    : Heroicon::OutlinedArrowTrendingDown)
                ->descriptionColor($growth !== null && $growth >= 0 ? 'success' : 'danger')
                ->color('success'),

            Stat::make('Casos sin abogado', (string) $unassignedCases)
                ->description($unassignedCases > 0 ? 'Revisa si hay que reenviar' : 'Todos repartidos')
                ->descriptionIcon(Heroicon::OutlinedScale)
                ->color($unassignedCases > 0 ? 'warning' : 'gray'),

            Stat::make('Garantías abiertas', (string) $openClaims)
                ->description($openClaims > 0 ? 'Se prometió responder en 24 h' : 'Nada pendiente')
                ->descriptionIcon(Heroicon::OutlinedShieldCheck)
                ->color($openClaims > 0 ? 'danger' : 'success'),
        ];
    }

    /** Mini-gráfica de los últimos 7 días para la primera tarjeta. */
    private function ordersLast7Days(): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => Order::query()
                ->whereDate('created_at', now()->subDays($daysAgo)->toDateString())
                ->count())
            ->all();
    }
}
