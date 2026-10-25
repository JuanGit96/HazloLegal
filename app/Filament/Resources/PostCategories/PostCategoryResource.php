<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCategories;

use App\Domain\Content\Models\PostCategory;
use App\Filament\Resources\PostCategories\Pages\ManagePostCategories;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::CONTENT;

    protected static ?string $navigationLabel = 'Categorías del blog';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'categoría';

    protected static ?string $pluralModelLabel = 'categorías';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get) => blank($get('slug'))
                            ? $set('slug', Str::slug((string) $state))
                            : null),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->prefix(url('/blog/categoria').'/'),

                    TextInput::make('emoji')->label('Emoji')->maxLength(8),

                    TextInput::make('sort_order')->label('Orden')->numeric()->default(0),

                    Textarea::make('description')
                        ->label('Descripción')
                        ->rows(2)
                        ->helperText('Se muestra como subtítulo y sirve de respaldo para la meta descripción.')
                        ->columnSpanFull(),

                    TextInput::make('seo_title')->label('Título SEO')->maxLength(70)->columnSpanFull(),

                    Textarea::make('seo_description')
                        ->label('Meta descripción')
                        ->rows(2)
                        ->maxLength(320)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('emoji')->label(''),
                TextColumn::make('name')->label('Categoría')->searchable()->sortable()->weight('bold'),
                TextColumn::make('slug')->label('URL')->color('gray')->toggleable(),
                TextColumn::make('posts_count')->label('Artículos')->counts('posts')->alignCenter()->badge(),
                TextColumn::make('description')->label('Descripción')->wrap()->limit(80)->toggleable(),
            ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePostCategories::route('/')];
    }
}
