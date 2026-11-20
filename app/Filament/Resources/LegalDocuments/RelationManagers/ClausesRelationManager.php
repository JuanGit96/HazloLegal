<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\RelationManagers;

use App\Domain\Catalog\Models\DocumentClause;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * Las casillas de petición: lo que el usuario marca y el texto que eso
 * escribe en el documento.
 *
 * El texto es exacto. Cambiarlo aquí cambia lo que la entidad lee, así que
 * cada casilla lleva una marca de si un abogado ya aprobó su redacción.
 */
class ClausesRelationManager extends RelationManager
{
    protected static string $relationship = 'clauses';

    protected static ?string $title = 'Casillas de petición';

    protected static ?string $modelLabel = 'casilla';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->label('Clave interna')
                ->required()
                ->helperText('No la cambies una vez publicada: los pedidos viejos la guardan.'),

            TextInput::make('label')
                ->label('Lo que lee el usuario en la casilla')
                ->required()
                ->columnSpanFull(),

            Textarea::make('body')
                ->label('Texto exacto que entra al documento')
                ->rows(4)
                ->helperText('Las variables van entre llaves: {procedimiento}, {fecha de la orden}.')
                ->columnSpanFull(),

            Textarea::make('legal_basis')
                ->label('Fundamento que añade esta casilla')
                ->rows(3)
                ->helperText('Se concatena con el fundamento común sin repetir normas.')
                ->columnSpanFull(),

            Repeater::make('variables')
                ->label('Las variables del texto')
                ->schema([
                    TextInput::make('key')->label('Llave')->required()
                        ->helperText('Tal cual aparece entre {} en el texto.'),
                    TextInput::make('label')->label('Pregunta al usuario')->required(),
                    Select::make('type')
                        ->label('Tipo')
                        ->options(['text' => 'Texto', 'textarea' => 'Texto largo', 'date' => 'Fecha', 'number' => 'Número'])
                        ->default('text')
                        ->native(false),
                ])
                ->columns(3)
                ->collapsible()
                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                ->columnSpanFull(),

            TextInput::make('response_deadline_days')
                ->label('Plazo propio de esta casilla')
                ->numeric()
                ->suffix('días hábiles')
                ->helperText('Déjalo vacío si rige el del trámite. Pedir documentos suele correr a 10 días y no a 15.'),

            TextInput::make('sort_order')->label('Orden')->numeric()->default(0),

            Toggle::make('is_free_text')
                ->label('Es la casilla de «lo escribo yo»')
                ->inline(false),

            Toggle::make('needs_lawyer_review')
                ->label('Pendiente de que un abogado apruebe la redacción')
                ->inline(false),

            Toggle::make('is_active')->label('Activa')->default(true)->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label('Casilla')
                    ->wrap()
                    ->description(fn (DocumentClause $record) => $record->key),

                TextColumn::make('body')
                    ->label('Texto')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('variables')
                    ->label('Variables')
                    ->state(fn (DocumentClause $record) => count($record->variables ?? []))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('response_deadline_days')
                    ->label('Plazo')
                    ->suffix(' días')
                    ->placeholder('El del trámite'),

                IconColumn::make('needs_lawyer_review')
                    ->label('Sin aprobar')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip('Un abogado tiene que aprobar la redacción antes de publicarla.'),

                ToggleColumn::make('is_active')->label('Activa'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('Sin casillas')
            ->emptyStateDescription('Este trámite arma el documento con su plantilla, no con casillas.');
    }
}
