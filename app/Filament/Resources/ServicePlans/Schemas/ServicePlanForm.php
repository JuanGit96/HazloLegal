<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServicePlans\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServicePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identidad del plan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label('Clave interna')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('La usa el código y las URLs. No se cambia.'),

                        TextInput::make('name')->label('Nombre')->required(),

                        TextInput::make('tagline')
                            ->label('Promesa en una línea')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Precio estándar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP')
                            ->helperText('Vacío = «Cotizar».'),

                        TextInput::make('delivery_hours')
                            ->label('Horas de entrega')
                            ->numeric(),

                        TagsInput::make('includes')
                            ->label('Qué incluye')
                            ->helperText('Admite **negrita** con doble asterisco.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Opción Express')
                    ->description('Si tiene precio Express, el plan muestra las dos velocidades al elegir el trámite. Si lo dejas vacío, no se ofrece.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('express_price')
                            ->label('Precio Express')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP'),

                        TextInput::make('express_delivery_hours')
                            ->label('Horas de entrega Express')
                            ->numeric(),

                        TagsInput::make('express_includes')
                            ->label('Qué incluye el Express')
                            ->columnSpanFull(),
                    ]),

                Section::make('Presentación')
                    ->columns(3)
                    ->schema([
                        TextInput::make('badge')
                            ->label('Etiqueta')
                            ->placeholder('MÁS PEDIDO'),

                        TextInput::make('cta_label')
                            ->label('Texto del botón')
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_highlighted')
                            ->label('Destacado')
                            ->inline(false),

                        Toggle::make('is_quote_only')
                            ->label('Solo cotización')
                            ->inline(false)
                            ->helperText('Lleva al formulario de caso complejo.'),

                        Toggle::make('is_active')
                            ->label('Visible')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
