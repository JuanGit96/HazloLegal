<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerPlans\Tables;

use App\Domain\Subscriptions\Models\LawyerPlan;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LawyerPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Plan')
                    ->searchable()
                    ->description(fn (LawyerPlan $record) => $record->tagline)
                    ->badge(fn (LawyerPlan $record) => $record->badge ? true : false),

                TextColumn::make('price_monthly')
                    ->label('Mensual')
                    ->money('COP')
                    ->sortable(),

                TextColumn::make('price_annual')
                    ->label('Anual')
                    ->money('COP')
                    ->placeholder('No se vende'),

                TextColumn::make('free_trial_months')
                    ->label('Prueba')
                    ->suffix(' meses')
                    ->placeholder('Sin prueba'),

                TextColumn::make('subscriptions_count')
                    ->label('Suscritos')
                    ->counts('subscriptions')
                    ->badge()
                    ->color('success'),

                IconColumn::make('includes_pdf_placement')
                    ->label('En PDF')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('includes_lead_data')
                    ->label('Leads')
                    ->boolean()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('A la venta'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Sin planes todavía')
            ->emptyStateDescription('Hasta que haya un plan, la landing de captación de abogados no tiene qué ofrecer.');
    }
}
