<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceAddons\Schemas;

use App\Domain\Catalog\Enums\AddonKind;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceAddonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Qué es')
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
                            ->unique(ignoreRecord: true)
                            ->helperText('Se usa en el código y en las URL (?extra=express). Cambiarla rompe enlaces.'),

                        TextInput::make('short_name')
                            ->label('Nombre corto')
                            ->helperText('Para la caja de compra, donde no cabe el nombre completo.'),

                        TextInput::make('emoji')
                            ->label('Emoji')
                            ->maxLength(8),

                        Select::make('kind')
                            ->label('Familia')
                            ->options(AddonKind::class)
                            ->required()
                            ->native(false)
                            ->helperText('Define el orden en que se le ofrece al cliente: primero lo barato y concreto, al final el abogado.'),

                        TextInput::make('sort_order')
                            ->label('Orden dentro de su familia')
                            ->numeric()
                            ->default(0),

                        TextInput::make('tagline')
                            ->label('El argumento, en una línea')
                            ->helperText('Qué gana el cliente. Es lo que se lee bajo el nombre en el checkout.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripción larga')
                            ->rows(3)
                            ->columnSpanFull(),

                        TagsInput::make('bullets')
                            ->label('Qué incluye')
                            ->helperText('Máximo tres se muestran en el checkout. Solo cosas que realmente entregamos.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Precio')
                    ->columns(2)
                    ->schema([
                        TextInput::make('default_price')
                            ->label('Precio por defecto')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->suffix('COP')
                            ->required()
                            ->helperText('0 = se entrega incluido. Cada trámite puede sobrescribirlo.'),

                        TextInput::make('discount_percentage')
                            ->label('Descuento a mostrar')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->maxValue(90)
                            ->helperText('Pinta un precio tachado más alto. Úsalo solo si ese precio fue real: inventarlo es publicidad engañosa (Ley 1480 de 2011).'),
                    ]),

                Section::make('Persuasión')
                    ->description('Todo lo de aquí es opcional y debe ser cierto. Un cliente que se siente engañado no vuelve, y aquí el negocio depende de que vuelva.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('badge')
                            ->label('Etiqueta')
                            ->placeholder('MÁS ELEGIDO')
                            ->helperText('Se pinta sobre la tarjeta.'),

                        TextInput::make('social_proof')
                            ->label('Prueba social')
                            ->placeholder('7 de cada 10 lo agregan')
                            ->helperText('Debe reflejar un número real.'),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Si lo apagas desaparece del checkout, pero los pedidos viejos conservan lo que pagaron.'),
                    ]),
            ]);
    }
}
