<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Domain\Content\Enums\PostStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Artículo')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Contenido')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set, $get) => blank($get('slug'))
                                        ? $set('slug', Str::slug((string) $state))
                                        : null)
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->prefix(url('/blog').'/')
                                    ->columnSpanFull(),

                                Textarea::make('excerpt')
                                    ->label('Entradilla')
                                    ->rows(3)
                                    ->helperText('Aparece en las tarjetas del blog y como respaldo de la meta descripción.')
                                    ->columnSpanFull(),

                                RichEditor::make('body')
                                    ->label('Cuerpo del artículo')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Autoría y enlaces')
                            ->columns(2)
                            ->schema([
                                Select::make('author_lawyer_id')
                                    ->label('Abogado que lo firma y revisa')
                                    ->relationship('author', 'name', fn ($query) => $query->where('is_author', true))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Google exige autoría verificable en contenido legal (E-E-A-T). Sin abogado firmante, el artículo posiciona peor.'),

                                Select::make('post_category_id')
                                    ->label('Categoría')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('related_document_id')
                                    ->label('Trámite relacionado')
                                    ->relationship('relatedDocument', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Define el bloque de conversión al final del artículo.'),

                                FileUpload::make('cover_path')
                                    ->label('Imagen de portada')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('posts')
                                    ->visibility('public'),

                                TextInput::make('cover_alt')
                                    ->label('Texto alternativo de la imagen')
                                    ->helperText('Describe la imagen. Cuenta para accesibilidad y para búsqueda de imágenes.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Publicación')
                            ->columns(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(PostStatus::class)
                                    ->default(PostStatus::Draft->value)
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('published_at')
                                    ->label('Fecha de publicación')
                                    ->seconds(false)
                                    ->helperText('En el futuro = programado.'),

                                DateTimePicker::make('reviewed_at')
                                    ->label('Última revisión jurídica')
                                    ->seconds(false)
                                    ->helperText('Se publica en el artículo y en los datos estructurados. Mantenerla fresca ayuda al posicionamiento.'),

                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->inline(false),
                            ]),

                        Tab::make('Imán de leads')
                            ->columns(2)
                            ->schema([
                                TextInput::make('lead_magnet_title')
                                    ->label('Título del recurso gratis')
                                    ->placeholder('Modelo de tutela por salud en PDF')
                                    ->helperText('Si lo llenas, el artículo muestra el formulario para capturar el correo.')
                                    ->columnSpanFull(),

                                FileUpload::make('lead_magnet_path')
                                    ->label('Archivo a entregar')
                                    ->directory('lead-magnets')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Metadatos')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('Título SEO')
                                    ->maxLength(70)
                                    ->columnSpanFull(),

                                Textarea::make('seo_description')
                                    ->label('Meta descripción')
                                    ->rows(3)
                                    ->maxLength(320)
                                    ->columnSpanFull(),

                                TextInput::make('focus_keyword')->label('Palabra clave principal'),

                                TagsInput::make('seo_keywords')->label('Palabras clave secundarias'),
                            ]),
                    ]),
            ]);
    }
}
