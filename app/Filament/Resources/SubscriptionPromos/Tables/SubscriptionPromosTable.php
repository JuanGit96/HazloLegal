<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriptionPromos\Tables;

use App\Domain\Subscriptions\Models\SubscriptionPromo;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SubscriptionPromosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('is_default', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Promoción')
                    ->searchable()
                    ->description(fn (SubscriptionPromo $record) => $record->tagline),

                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->copyable()
                    ->copyMessage('Código copiado')
                    ->placeholder('Por defecto')
                    ->searchable(),

                TextColumn::make('condicion')
                    ->label('Qué ofrece')
                    ->state(fn (SubscriptionPromo $record) => match (true) {
                        $record->hasFreeMonths() && $record->hasDiscount() => "{$record->free_months} meses gratis y luego {$record->discount_percentage} % menos",
                        $record->hasFreeMonths() => "{$record->free_months} meses gratis",
                        $record->hasDiscount() => "{$record->discount_percentage} % menos por {$record->discount_months} periodos",
                        default => 'Nada: precio de lista',
                    }),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->placeholder('Todos'),

                TextColumn::make('redeemed_count')
                    ->label('Usada')
                    ->state(fn (SubscriptionPromo $record) => $record->max_redemptions
                        ? "{$record->redeemed_count} / {$record->max_redemptions}"
                        : (string) $record->redeemed_count)
                    ->badge()
                    ->color(fn (SubscriptionPromo $record) => $record->isRedeemable() ? 'success' : 'danger'),

                TextColumn::make('expires_at')
                    ->label('Vence')
                    ->dateTime('j M Y')
                    ->placeholder('Sin fecha')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Activa'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->emptyStateHeading('Sin promociones')
            ->emptyStateDescription('Sin ninguna, cada abogado paga el precio de lista desde el primer día.');
    }
}
