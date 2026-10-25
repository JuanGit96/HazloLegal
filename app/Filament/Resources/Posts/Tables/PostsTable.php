<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use App\Domain\Content\Enums\PostStatus;
use App\Domain\Content\Models\Post;
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

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Artículo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(70)
                    ->description(fn (Post $record) => $record->focus_keyword
                        ? "🔑 {$record->focus_keyword}"
                        : '⚠️ Sin palabra clave definida'),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->sortable(),

                TextColumn::make('author.name')
                    ->label('Firma')
                    ->placeholder('⚠️ Sin abogado')
                    ->description(fn (Post $record) => $record->author?->headline),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('reading_minutes')
                    ->label('Lectura')
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('views_count')
                    ->label('Visitas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('relatedDocument.short_name')
                    ->label('Convierte a')
                    ->placeholder('Sin trámite')
                    ->toggleable(),

                IconColumn::make('lead_magnet_title')
                    ->label('Imán')
                    ->boolean()
                    ->state(fn (Post $record) => $record->hasLeadMagnet())
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(PostStatus::class),
                SelectFilter::make('post_category_id')->label('Categoría')->relationship('category', 'name'),
                SelectFilter::make('author_lawyer_id')->label('Firma')->relationship('author', 'name'),
                TernaryFilter::make('is_featured')->label('Destacado'),
            ])
            ->recordActions([
                Action::make('view_public')
                    ->label('Ver')
                    ->icon(Heroicon::OutlinedLink)
                    ->color('gray')
                    ->url(fn (Post $record) => $record->url(), shouldOpenInNewTab: true)
                    ->visible(fn (Post $record) => $record->status === PostStatus::Published),

                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
