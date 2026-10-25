<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\Schemas;

use App\Domain\Catalog\Enums\AutomationPhase;
use App\Domain\Catalog\Enums\PracticeArea;
use Filament\Forms\Components\Repeater;
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

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Trámite')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Básico')
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
                                    ->label('Slug (URL)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->prefix(url('/documentos').'/')
                                    ->helperText('Cambiarlo rompe los enlaces ya indexados por Google.'),

                                TextInput::make('short_name')
                                    ->label('Nombre corto')
                                    ->helperText('Para tarjetas y botones.'),

                                TextInput::make('emoji')
                                    ->label('Emoji')
                                    ->maxLength(8),

                                Select::make('practice_area')
                                    ->label('Área de práctica')
                                    ->options(PracticeArea::class)
                                    ->required()
                                    ->native(false),

                                Select::make('automation_phase')
                                    ->label('Fase')
                                    ->options(AutomationPhase::class)
                                    ->required()
                                    ->native(false)
                                    ->helperText('Fase 1: plantilla maestra + auditoría. Fase 2: redacción humana.'),

                                TextInput::make('tagline')
                                    ->label('Promesa en una línea')
                                    ->columnSpanFull(),

                                Textarea::make('summary')
                                    ->label('Resumen')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                TextInput::make('legal_basis')
                                    ->label('Fundamento legal')
                                    ->placeholder('Ley 1266 de 2008')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Precio y entrega')
                            ->columns(2)
                            ->schema([
                                TextInput::make('price_modifier')
                                    ->label('Ajuste de precio')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('$')
                                    ->suffix('COP')
                                    ->helperText('Se suma al precio del plan. 0 = cuesta lo mismo que cualquier otro trámite.'),

                                TextInput::make('sort_order')
                                    ->label('Orden en el catálogo')
                                    ->numeric()
                                    ->default(0),

                                Toggle::make('available_express')
                                    ->label('Admite entrega Express')
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('supports_signature')
                                    ->label('Admite firma electrónica')
                                    ->inline(false),

                                Toggle::make('requires_lawyer_review')
                                    ->label('Exige revisión de abogado')
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('is_active')
                                    ->label('Publicado')
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('is_featured')
                                    ->label('Destacado en la home')
                                    ->inline(false),
                            ]),

                        Tab::make('Qué recibe el cliente')
                            ->schema([
                                TagsInput::make('what_you_get')
                                    ->label('Qué recibe')
                                    ->placeholder('Añade y pulsa Enter')
                                    ->columnSpanFull(),

                                TagsInput::make('required_info')
                                    ->label('Qué necesitamos de él')
                                    ->columnSpanFull(),

                                TagsInput::make('use_cases')
                                    ->label('Casos de uso')
                                    ->columnSpanFull(),

                                TagsInput::make('not_included')
                                    ->label('Qué NO incluye')
                                    ->helperText('Decirlo antes de cobrar evita reclamaciones después.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Contenido SEO')
                            ->schema([
                                RichEditor::make('body')
                                    ->label('Contenido largo de la landing')
                                    ->helperText('Es lo que posiciona la página. Mínimo recomendado: 600 palabras.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Preguntas frecuentes')
                            ->schema([
                                Repeater::make('faqs')
                                    ->label('FAQ del trámite')
                                    ->helperText('Se publican con marcado FAQPage, que puede ganar espacio extra en Google.')
                                    ->schema([
                                        TextInput::make('question')->label('Pregunta')->required(),
                                        Textarea::make('answer')->label('Respuesta')->rows(4)->required(),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['question'] ?? null)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Metadatos')
                            ->columns(2)
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('Título SEO')
                                    ->maxLength(70)
                                    ->helperText('Máximo 60-65 caracteres para que no se corte en Google.')
                                    ->columnSpanFull(),

                                Textarea::make('seo_description')
                                    ->label('Meta descripción')
                                    ->rows(3)
                                    ->maxLength(320)
                                    ->helperText('Idealmente 150-155 caracteres.')
                                    ->columnSpanFull(),

                                TextInput::make('focus_keyword')
                                    ->label('Palabra clave principal')
                                    ->helperText('La búsqueda exacta que quieres ganar.'),

                                TagsInput::make('seo_keywords')
                                    ->label('Palabras clave secundarias'),
                            ]),
                    ]),
            ]);
    }
}
