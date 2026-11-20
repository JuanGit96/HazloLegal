<?php

declare(strict_types=1);

namespace App\Filament\Resources\Calculators\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CalculatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Calculadora')
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
                                    ->prefix(url('/gratis/calculadoras').'/'),

                                TextInput::make('key')
                                    ->label('Clave de la fórmula')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Determina qué cálculo corre: liquidacion, indemnizacion, horas_extras, intereses_mora, prima. Una clave sin fórmula no calcula nada.'),

                                TextInput::make('emoji')
                                    ->label('Emoji')
                                    ->maxLength(8),

                                Textarea::make('intro')
                                    ->label('Entradilla')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Select::make('related_document_id')
                                    ->label('Trámite que se ofrece después del resultado')
                                    ->relationship('relatedDocument', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('El puente entre el cálculo gratis y lo que cobramos.'),

                                Toggle::make('captures_lead')
                                    ->label('Pedir el correo para enviar el resultado')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Con autorización de tratamiento de datos. Sin la casilla no se guarda nada.'),

                                Toggle::make('is_active')
                                    ->label('Publicada')
                                    ->default(true)
                                    ->inline(false),

                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Tab::make('Parámetros del año')
                            ->schema([
                                KeyValue::make('config')
                                    ->label('Valores que usa la fórmula')
                                    ->keyLabel('Parámetro')
                                    ->valueLabel('Valor')
                                    ->helperText('minimum_wage, transport_allowance, year… Hay que actualizarlos cada enero: una calculadora con el salario mínimo del año pasado da resultados falsos.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Contenido SEO')
                            ->schema([
                                RichEditor::make('body')
                                    ->label('Contenido largo bajo la calculadora')
                                    ->helperText('Es lo que posiciona la página: explica la fórmula y cita la norma.')
                                    ->columnSpanFull(),

                                Repeater::make('faqs')
                                    ->label('Preguntas frecuentes')
                                    ->schema([
                                        TextInput::make('question')->label('Pregunta')->required(),
                                        Textarea::make('answer')->label('Respuesta')->rows(3)->required(),
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
                                    ->columnSpanFull(),

                                Textarea::make('seo_description')
                                    ->label('Meta descripción')
                                    ->rows(3)
                                    ->maxLength(320)
                                    ->columnSpanFull(),

                                TextInput::make('focus_keyword')
                                    ->label('Palabra clave principal'),
                            ]),
                    ]),
            ]);
    }
}
