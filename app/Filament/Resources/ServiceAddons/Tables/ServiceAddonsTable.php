<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceAddons\Tables;

use App\Domain\Catalog\Enums\AddonKind;
use App\Domain\Catalog\Models\ServiceAddon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceAddonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Adicional')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ServiceAddon $record) => $record->tagline)
                    ->icon(fn (ServiceAddon $record) => $record->kind->getIcon()),

                TextColumn::make('kind')
                    ->label('Familia')
                    ->badge()
                    ->sortable(),

                TextColumn::make('default_price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable()
                    ->placeholder('Incluido'),

                TextColumn::make('discount_percentage')
                    ->label('Dto.')
                    ->suffix(' %')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('documents_count')
                    ->label('Trámites')
                    ->counts('documents')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('badge')
                    ->label('Etiqueta')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('social_proof')
                    ->label('Prueba social')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('Familia')
                    ->options(AddonKind::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Todavía no hay adicionales')
            ->emptyStateDescription('Los adicionales son lo que el cliente le suma a un trámite: instructivo, entrega inmediata, firma, acompañamiento.');
    }
}
