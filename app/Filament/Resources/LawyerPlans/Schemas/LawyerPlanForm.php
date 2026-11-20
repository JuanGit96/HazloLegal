<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerPlans\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LawyerPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El plan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $get) => blank($get('key'))
                                ? $set('key', Str::slug((string) $state, '_'))
                                : null),

                        TextInput::make('key')
                            ->label('Clave interna')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('tagline')
                            ->label('Para quién es, en una línea')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),

                        TagsInput::make('features')
                            ->label('Lo que incluye, como se lo contamos')
                            ->helperText('Esto es lo que se lee en la landing de captación de abogados.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Precio')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_monthly')
                            ->label('Mensual')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP')
                            ->required(),

                        TextInput::make('price_annual')
                            ->label('Anual')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP')
                            ->helperText('Déjalo vacío si no se vende por año.'),

                        TextInput::make('free_trial_months')
                            ->label('Meses de prueba gratis')
                            ->numeric()
                            ->default(0)
                            ->helperText('Al abogado le cuesta creer que llegan clientes. La prueba es lo que lo demuestra.'),
                    ]),

                Section::make('Qué le damos de verdad')
                    ->description('Cada interruptor cambia lo que el sistema hace, no solo lo que la landing promete.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('includes_profile_page')
                            ->label('Perfil público en el directorio')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('includes_pdf_placement')
                            ->label('Aparece en los instructivos PDF')
                            ->inline(false)
                            ->helperText('El PDF que el cliente imprime y lleva a la ventanilla.'),

                        Toggle::make('includes_case_alerts')
                            ->label('Aviso cuando alguien pide acompañamiento')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('includes_lead_data')
                            ->label('Acceso a contactos que autorizaron ser contactados')
                            ->inline(false)
                            ->helperText('Solo los que marcaron esa casilla. Nunca los demás.'),

                        Toggle::make('includes_featured')
                            ->label('Destacado en el directorio')
                            ->inline(false),

                        Toggle::make('includes_blog_placement')
                            ->label('Aparece en los artículos de su especialidad')
                            ->inline(false),

                        TextInput::make('directory_priority')
                            ->label('Prioridad en el directorio')
                            ->numeric()
                            ->default(0)
                            ->helperText('Mayor número = aparece antes.'),
                    ]),

                Section::make('Presentación')
                    ->columns(3)
                    ->schema([
                        TextInput::make('badge')
                            ->label('Etiqueta')
                            ->placeholder('MÁS ELEGIDO'),

                        Toggle::make('is_highlighted')
                            ->label('Destacar la tarjeta')
                            ->inline(false),

                        Toggle::make('is_active')
                            ->label('A la venta')
                            ->default(true)
                            ->inline(false),

                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
