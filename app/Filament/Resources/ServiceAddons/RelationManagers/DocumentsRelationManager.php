<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceAddons\RelationManagers;

use App\Domain\Catalog\Models\LegalDocument;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * En qué trámites se ofrece este adicional y por cuánto.
 *
 * El precio de aquí manda sobre el precio por defecto: acompañar una tutela
 * no cuesta lo mismo que acompañar un contrato de arriendo.
 */
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Trámites donde se ofrece';

    protected static ?string $modelLabel = 'trámite';

    public function form(Schema $schema): Schema
    {
        return $schema->components(self::pivotFields());
    }

    /** @return array<int, \Filament\Schemas\Components\Component> */
    public static function pivotFields(): array
    {
        return [
            TextInput::make('price')
                ->label('Precio en este trámite')
                ->numeric()
                ->prefix('$')
                ->suffix('COP')
                ->helperText('Vacío = usa el precio por defecto del adicional. 0 = incluido sin costo.'),

            Toggle::make('is_available')
                ->label('Disponible')
                ->default(true)
                ->inline(false),

            Toggle::make('is_recommended')
                ->label('Recomendado')
                ->inline(false)
                ->helperText('Se destaca y se usa como «siguiente mejor oferta».'),

            Toggle::make('is_preselected')
                ->label('Premarcado')
                ->inline(false)
                ->helperText('Llega marcado. Solo es legítimo si se ve, se explica y se puede quitar de un clic.'),

            TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Trámite')
                    ->searchable()
                    ->description(fn (LegalDocument $record) => $record->practice_area->getLabel()),

                TextColumn::make('pivot.price')
                    ->label('Precio aquí')
                    ->money('COP')
                    ->placeholder('Por defecto'),

                TextColumn::make('base_price')
                    ->label('Precio del trámite')
                    ->money('COP')
                    ->placeholder('Gratis')
                    ->color('gray'),

                ToggleColumn::make('pivot.is_available')->label('Disponible'),
                ToggleColumn::make('pivot.is_recommended')->label('Recomendado'),
                ToggleColumn::make('pivot.is_preselected')->label('Premarcado'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Añadir a un trámite')
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        ...self::pivotFields(),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->emptyStateHeading('No se ofrece en ningún trámite')
            ->emptyStateDescription('Hasta que lo añadas a un trámite, este adicional no aparece en el checkout.');
    }
}
