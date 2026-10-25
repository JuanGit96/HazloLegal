<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqEntries;

use App\Domain\Content\Models\FaqEntry;
use App\Filament\Resources\FaqEntries\Pages\ManageFaqEntries;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqEntryResource extends Resource
{
    protected static ?string $model = FaqEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::CONTENT;

    protected static ?string $navigationLabel = 'Preguntas frecuentes';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'pregunta';

    protected static ?string $pluralModelLabel = 'preguntas frecuentes';

    protected static ?string $recordTitleAttribute = 'question';

    private const GROUPS = [
        'home' => 'Home',
        'pricing' => 'Precios',
        'guarantee' => 'Garantía',
        'signature' => 'Firma electrónica',
        'cases' => 'Casos complejos',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    Select::make('group')
                        ->label('Dónde se muestra')
                        ->options(self::GROUPS)
                        ->default('home')
                        ->required()
                        ->native(false),

                    Select::make('legal_document_id')
                        ->label('Trámite específico')
                        ->relationship('document', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Ninguno (pregunta general)'),

                    TextInput::make('question')
                        ->label('Pregunta')
                        ->required()
                        ->columnSpanFull(),

                    Textarea::make('answer')
                        ->label('Respuesta')
                        ->rows(6)
                        ->required()
                        ->helperText('Admite Markdown: **negrita**, listas y [enlaces](/ruta). Se publica con marcado FAQPage para Google.')
                        ->columnSpanFull(),

                    TextInput::make('sort_order')->label('Orden')->numeric()->default(0),

                    Toggle::make('is_published')->label('Publicada')->default(true)->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('group')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('question')->label('Pregunta')->searchable()->wrap()->weight('bold'),
                TextColumn::make('group')
                    ->label('Sección')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::GROUPS[$state] ?? $state),
                TextColumn::make('document.short_name')->label('Trámite')->placeholder('General'),
                TextColumn::make('sort_order')->label('Orden')->alignCenter(),
                IconColumn::make('is_published')->label('Publicada')->boolean(),
            ])
            ->filters([
                SelectFilter::make('group')->label('Sección')->options(self::GROUPS),
            ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFaqEntries::route('/')];
    }
}
