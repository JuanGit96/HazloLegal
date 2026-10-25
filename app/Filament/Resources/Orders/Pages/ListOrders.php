<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Models\Order;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /** Pestañas que reflejan el flujo real de trabajo del equipo. */
    public function getTabs(): array
    {
        return [
            'por_hacer' => Tab::make('Por hacer')
                ->modifyQueryUsing(fn (Builder $query) => $query->open())
                ->badge(Order::query()->open()->count())
                ->badgeColor('warning'),

            'vencidos' => Tab::make('Vencidos')
                ->modifyQueryUsing(fn (Builder $query) => $query->overdue())
                ->badge(Order::query()->overdue()->count())
                ->badgeColor('danger'),

            'esperando_pago' => Tab::make('Esperando pago')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::AwaitingPayment))
                ->badge(Order::query()->where('status', OrderStatus::AwaitingPayment)->count()),

            'entregados' => Tab::make('Entregados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::Delivered)),

            'todos' => Tab::make('Todos'),
        ];
    }
}
