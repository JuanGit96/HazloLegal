<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\RelationManagers;

use App\Domain\Catalog\Models\ServiceAddon;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * Qué se le puede sumar a este trámite y por cuánto.
 *
 * El orden en que aparecen en el checkout no se decide aquí: lo decide la
 * familia del adicional, para que siempre se ofrezca primero lo barato y
 * concreto y al final lo caro.
 */
class AddonsRelationManager extends RelationManager
{
    protected static string $relationship = 'addons';

    protected static ?string $title = 'Adicionales de este trámite';

    protected static ?string $modelLabel = 'adicional';

    public function form(Schema $schema): Schema
    {
        return $schema->components(self::pivotFields());
    }

    /** @return array<int, mixed> */
    public static function pivotFields(): array
    {
        return [
            TextInput::make('price')
                ->label('Precio en este trámite')
                ->numeric()
                ->prefix('$')
                ->suffix('COP')
                ->helperText('Vacío = precio por defecto del adicional. 0 = incluido sin costo.'),

            Toggle::make('is_available')
                ->label('Disponible')
                ->default(true)
                ->inline(false),

            Toggle::make('is_recommended')
                ->label('Recomendado')
                ->inline(false),

            Toggle::make('is_preselected')
                ->label('Premarcado')
                ->inline(false)
                ->helperText('Llega marcado. Debe verse, explicarse y poderse quitar de un clic.'),

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
                    ->label('Adicional')
                    ->description(fn (ServiceAddon $record) => $record->tagline),

                TextColumn::make('kind')
                    ->label('Familia')
                    ->badge(),

                TextColumn::make('pivot.price')
                    ->label('Precio aquí')
                    ->money('COP')
                    ->placeholder('Por defecto'),

                TextColumn::make('default_price')
                    ->label('Por defecto')
                    ->money('COP')
                    ->color('gray')
                    ->placeholder('Incluido'),

                ToggleColumn::make('pivot.is_available')->label('Disponible'),
                ToggleColumn::make('pivot.is_recommended')->label('Recomendado'),
                ToggleColumn::make('pivot.is_preselected')->label('Premarcado'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Añadir un adicional')
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        ...self::pivotFields(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Precio'),
                DetachAction::make(),
            ])
            ->emptyStateHeading('Sin adicionales')
            ->emptyStateDescription('Sin adicionales, este trámite se vende solo por su precio base.');
    }
}
