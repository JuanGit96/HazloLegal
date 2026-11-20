<?php

declare(strict_types=1);

namespace App\Filament\Resources\Calculators\Tables;

use App\Domain\Tools\Models\Calculator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CalculatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Calculadora')
                    ->searchable()
                    ->description(fn (Calculator $record) => $record->intro),

                TextColumn::make('key')
                    ->label('Fórmula')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('uses_count')
                    ->label('Usos')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('relatedDocument.name')
                    ->label('Ofrece después')
                    ->placeholder('Nada')
                    ->toggleable(),

                IconColumn::make('captures_lead')
                    ->label('Pide correo')
                    ->boolean(),

                ToggleColumn::make('is_active')
                    ->label('Publicada'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
