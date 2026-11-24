<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriptionPromos\Schemas;

use App\Domain\Subscriptions\Models\SubscriptionPromo;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SubscriptionPromoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Qué es')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre interno')
                            ->required(),

                        TextInput::make('code')
                            ->label('Código del enlace')
                            ->helperText('Déjalo vacío para que sea la oferta por defecto: la que recibe quien llega sin invitación.')
                            ->unique(ignoreRecord: true)
                            ->maxLength(40)
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Str::upper(trim($state)) : null),

                        TextInput::make('tagline')
                            ->label('La frase que ve el abogado')
                            ->columnSpanFull(),

                        Textarea::make('terms')
                            ->label('La letra pequeña')
                            ->helperText('Qué pasa cuando se acaba. Si esto queda vacío, el abogado se enterará el día del cobro, y eso es una queja.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('La condición')
                    ->columns(3)
                    ->description('Los meses gratis van primero; cuando se acaban, empieza el descuento.')
                    ->schema([
                        TextInput::make('free_months')
                            ->label('Meses gratis')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(24)
                            ->helperText('La tarjeta se registra igual, pero no se cobra hasta que pasen.'),

                        TextInput::make('discount_percentage')
                            ->label('Descuento')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        TextInput::make('discount_months')
                            ->label('Periodos con descuento')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(36)
                            ->helperText('Sin esto, el descuento no se aplica.'),

                        Select::make('lawyer_plan_id')
                            ->label('Solo para un plan')
                            ->relationship('plan', 'name')
                            ->placeholder('Vale para todos los planes')
                            ->columnSpan(2),

                        Toggle::make('is_default')
                            ->label('Es la oferta por defecto')
                            ->helperText('Solo una, y sin código.'),
                    ]),

                Section::make('Vigencia y cupo')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Empieza')
                            ->placeholder('Ya está vigente')
                            ->seconds(false),

                        DateTimePicker::make('expires_at')
                            ->label('Vence')
                            ->placeholder('Sin fecha de vencimiento')
                            ->seconds(false),

                        TextInput::make('max_redemptions')
                            ->label('Cupo')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Sin límite'),

                        TextInput::make('redeemed_count')
                            ->label('Ya usada')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Cuántos abogados entraron con esta condición.'),

                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),

                        TextInput::make('enlace')
                            ->label('El enlace que se le pasa al abogado')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Guarda la promoción con un código para ver el enlace')
                            ->afterStateHydrated(fn ($component, $record) => $component->state(
                                $record instanceof SubscriptionPromo ? $record->url() : null
                            ))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
