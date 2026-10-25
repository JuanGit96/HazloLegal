<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServicePlans\Tables;

use App\Domain\Catalog\Models\ServicePlan;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Plan')
                    ->weight('bold')
                    ->description(fn (ServicePlan $record) => $record->tagline)
                    ->wrap(),

                TextColumn::make('price')
                    ->label('Estándar')
                    ->money('COP')
                    ->placeholder('Cotizar')
                    ->description(fn (ServicePlan $record) => $record->delivery_hours
                        ? "{$record->delivery_hours} h"
                        : null),

                TextColumn::make('express_price')
                    ->label('Express')
                    ->money('COP')
                    ->placeholder('No aplica')
                    ->description(fn (ServicePlan $record) => $record->express_delivery_hours
                        ? "{$record->express_delivery_hours} h"
                        : null),

                TextColumn::make('orders_count')
                    ->label('Pedidos')
                    ->counts('orders')
                    ->alignCenter(),

                TextColumn::make('badge')->label('Etiqueta')->placeholder('—'),

                IconColumn::make('is_highlighted')->label('Destacado')->boolean(),
                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ]);
    }
}
