<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\Tables;

use App\Domain\Catalog\Enums\AutomationPhase;
use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Catalog\Models\LegalDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LegalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('emoji')->label('')->size('lg'),

                TextColumn::make('name')
                    ->label('Trámite')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (LegalDocument $record) => $record->tagline)
                    ->wrap(),

                TextColumn::make('practice_area')
                    ->label('Área')
                    ->badge()
                    ->sortable(),

                TextColumn::make('automation_phase')
                    ->label('Fase')
                    ->badge()
                    ->sortable(),

                TextColumn::make('price_modifier')
                    ->label('Ajuste')
                    ->money('COP')
                    ->placeholder('Sin ajuste')
                    ->toggleable(),

                TextColumn::make('orders_count')
                    ->label('Pedidos')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('views_count')
                    ->label('Visitas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('supports_signature')
                    ->label('Firma')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Publicado')
                    ->boolean()
                    ->sortable(),

                // Un trámite sin contenido largo no posiciona.
                IconColumn::make('body')
                    ->label('SEO')
                    ->boolean()
                    ->state(fn (LegalDocument $record) => filled($record->body) && filled($record->seo_title))
                    ->trueIcon(Heroicon::OutlinedCheckBadge)
                    ->falseIcon(Heroicon::OutlinedExclamationTriangle)
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip('¿Tiene contenido largo y título SEO?'),
            ])
            ->filters([
                SelectFilter::make('practice_area')->label('Área')->options(PracticeArea::class)->multiple(),
                SelectFilter::make('automation_phase')->label('Fase')->options(AutomationPhase::class),
                TernaryFilter::make('is_active')->label('Publicado'),
                TernaryFilter::make('is_featured')->label('Destacado'),
            ])
            ->recordActions([
                Action::make('view_public')
                    ->label('Ver en el sitio')
                    ->icon(Heroicon::OutlinedLink)
                    ->color('gray')
                    ->url(fn (LegalDocument $record) => $record->url(), shouldOpenInNewTab: true),

                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
