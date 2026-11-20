<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Orders\Enums\OrderTier;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderAddon;
use App\Domain\Shared\Models\Lead;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Los números del modelo de negocio, no los de la operación.
 *
 * Regalar documentos solo tiene sentido si una parte de quien llega gratis
 * acaba pagando algo, y si los que no pagan dejan un contacto que un abogado
 * suscrito valora. Estas cuatro cifras son las que dicen si eso ocurre; sin
 * ellas, «documentos gratis» es solo un costo.
 */
class FunnelOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'El embudo del modelo';

    protected ?string $description = 'Lo gratis atrae; los adicionales y las suscripciones son lo que cobra.';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $month = [now()->startOfMonth(), now()->endOfMonth()];

        $free = Order::query()
            ->where('tier', OrderTier::Free)
            ->whereBetween('created_at', $month)
            ->count();

        $paid = Order::query()
            ->whereIn('tier', [OrderTier::SelfService, OrderTier::Accompanied])
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', $month)
            ->count();

        $total = $free + $paid;
        $paidShare = $total > 0 ? (int) round(($paid / $total) * 100) : 0;

        // Lo que entra por adicionales: la métrica que dice si el checkout
        // está persuadiendo o solo cobrando.
        $addonRevenue = (int) OrderAddon::query()
            ->whereHas('order', fn ($q) => $q->revenue()->whereBetween('paid_at', $month))
            ->sum('price');

        $mrr = (int) LawyerSubscription::query()
            ->where('status', SubscriptionStatus::Active)
            ->get()
            ->sum(fn (LawyerSubscription $s) => $s->billing_period?->value === 'annual'
                ? (int) round($s->price / 12)
                : (int) $s->price);

        $trialing = LawyerSubscription::query()
            ->where('status', SubscriptionStatus::Trialing)
            ->count();

        // Solo cuentan los que lo autorizaron: los demás no son inventario.
        $shareable = Lead::query()->shareable()->count();

        return [
            Stat::make('Documentos gratis del mes', (string) $free)
                ->description($total > 0 ? "{$paidShare} % de los pedidos acabó pagando" : 'Sin pedidos este mes')
                ->descriptionIcon(Heroicon::OutlinedGift)
                ->descriptionColor($paidShare >= 15 ? 'success' : 'warning')
                ->color('info')
                ->chart($this->freeLast7Days()),

            Stat::make('Ingresos por adicionales', '$'.number_format($addonRevenue, 0, ',', '.'))
                ->description('Lo que se sumó sobre el precio base')
                ->descriptionIcon(Heroicon::OutlinedPlusCircle)
                ->color('success'),

            Stat::make('Suscripciones de abogados', '$'.number_format($mrr, 0, ',', '.').' /mes')
                ->description($trialing > 0 ? "{$trialing} en periodo de prueba" : 'Ninguno en prueba')
                ->descriptionIcon(Heroicon::OutlinedCreditCard)
                ->descriptionColor($trialing > 0 ? 'info' : 'gray')
                ->color('primary'),

            Stat::make('Contactos compartibles', (string) $shareable)
                ->description('Solo los que autorizaron ser contactados')
                ->descriptionIcon(Heroicon::OutlinedShieldCheck)
                ->color('warning'),
        ];
    }

    private function freeLast7Days(): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => Order::query()
                ->where('tier', OrderTier::Free)
                ->whereDate('created_at', now()->subDays($daysAgo)->toDateString())
                ->count())
            ->all();
    }
}
