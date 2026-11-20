<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerSubscriptions\Tables;

use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LawyerSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('lawyer.name')
                    ->label('Abogado')
                    ->searchable()
                    ->description(fn (LawyerSubscription $record) => $record->lawyer?->city),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Paga')
                    ->money('COP')
                    ->description(fn (LawyerSubscription $record) => $record->billing_period?->getLabel()),

                TextColumn::make('trial_ends_at')
                    ->label('Prueba hasta')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->color(fn (LawyerSubscription $record) => $record->trial_ends_at?->isPast() ? 'danger' : 'info'),

                TextColumn::make('renews_at')
                    ->label('Renueva')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(SubscriptionStatus::class),

                SelectFilter::make('billing_period')
                    ->label('Periodo')
                    ->options(BillingPeriod::class),

                SelectFilter::make('lawyer_plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->emptyStateHeading('Ninguna suscripción todavía')
            ->emptyStateDescription('Los abogados que se registren desde la landing aparecen aquí para que les asignes un plan.');
    }
}
