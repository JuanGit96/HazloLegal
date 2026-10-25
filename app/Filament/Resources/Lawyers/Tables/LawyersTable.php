<?php

declare(strict_types=1);

namespace App\Filament\Resources\Lawyers\Tables;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Enums\LawyerStatus;
use App\Domain\Lawyers\Models\Lawyer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LawyersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (Lawyer $record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=DBEAFE&color=1E3A8A'),

                TextColumn::make('name')
                    ->label('Abogado')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Lawyer $record) => $record->headline),

                TextColumn::make('professional_card')
                    ->label('T.P.')
                    ->searchable()
                    ->copyable()
                    ->placeholder('⚠️ Sin tarjeta'),

                TextColumn::make('practice_areas')
                    ->label('Áreas')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PracticeArea::tryFrom($state)?->getLabel() ?? $state)
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                IconColumn::make('accepts_cases')
                    ->label('Recibe casos')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('assigned_cases_count')
                    ->label('Casos activos')
                    ->counts('assignedCases')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Lawyer $record, $state) => $state >= $record->max_active_cases ? 'danger' : 'success')
                    ->description(fn (Lawyer $record) => "Tope: {$record->max_active_cases}"),

                TextColumn::make('cases_resolved')
                    ->label('Resueltos')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('rating')
                    ->label('Calificación')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_author')
                    ->label('Autor')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->label('Ciudad')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(LawyerStatus::class),

                SelectFilter::make('practice_areas')
                    ->label('Área')
                    ->options(PracticeArea::class)
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereJsonContains('practice_areas', $data['value'])
                        : $query),

                TernaryFilter::make('accepts_cases')->label('Recibe casos'),
                TernaryFilter::make('is_author')->label('Es autor del blog'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin abogados en la red')
            ->emptyStateDescription('Sin abogados activos, los casos complejos no se pueden repartir.');
    }
}
