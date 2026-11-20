<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\OrderTier;
use App\Domain\Orders\Enums\PaymentMethod;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Estado del trámite')
                    ->description('Lo que el equipo actualiza mientras trabaja el pedido.')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(OrderStatus::class)
                            ->required()
                            ->native(false),

                        Select::make('assigned_lawyer_id')
                            ->label('Abogado responsable')
                            ->relationship('assignedLawyer', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin asignar'),

                        DateTimePicker::make('promised_at')
                            ->label('Fecha comprometida')
                            ->seconds(false)
                            ->helperText('Lo que le prometimos al cliente.'),

                        DateTimePicker::make('delivered_at')
                            ->label('Entregado el')
                            ->seconds(false),

                        TextInput::make('document_path')
                            ->label('Documento generado')
                            ->disabled()
                            ->placeholder('Todavía no se ha generado'),

                        TextInput::make('instructions_path')
                            ->label('Instructivo generado')
                            ->disabled()
                            ->placeholder('No lleva instructivo'),

                        Select::make('featured_lawyer_id')
                            ->label('Abogado patrocinado en el instructivo')
                            ->relationship('featuredLawyer', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Ninguno'),
                    ]),

                Section::make('Pedido')
                    ->columns(3)
                    ->schema([
                        TextInput::make('reference')
                            ->label('Referencia')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('legal_document_id')
                            ->label('Trámite')
                            ->relationship('document', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('tier')
                            ->label('Nivel')
                            ->options(OrderTier::class)
                            ->required()
                            ->native(false)
                            ->helperText('Se calcula a partir de los adicionales; cámbialo solo para corregir un dato.'),

                        TextInput::make('base_amount')
                            ->label('Precio base')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP'),

                        TextInput::make('addons_amount')
                            ->label('Suma de adicionales')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP'),

                        TextInput::make('amount')
                            ->label('Total cobrado')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP')
                            ->required(),

                        Toggle::make('wants_signature')
                            ->label('Pidió firma electrónica')
                            ->inline(false),

                        Toggle::make('accepts_marketing')
                            ->label('Autorizó recibir correos')
                            ->inline(false)
                            ->disabled()
                            ->helperText('Lo marcó el cliente. No se cambia desde aquí.'),

                        Toggle::make('shareable_with_lawyers')
                            ->label('Autorizó compartir su caso con abogados')
                            ->inline(false)
                            ->disabled(),
                    ]),

                Section::make('Adicionales contratados')
                    ->description('El precio queda congelado tal como se cobró. Si mañana sube el adicional, este pedido sigue diciendo lo que el cliente pagó.')
                    ->schema([
                        Repeater::make('addons')
                            ->label('')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')->label('Adicional')->disabled(),
                                TextInput::make('price')
                                    ->label('Precio cobrado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pago')
                    ->columns(3)
                    ->schema([
                        Select::make('payment_method')
                            ->label('Medio de pago')
                            ->options(PaymentMethod::class)
                            ->native(false),

                        TextInput::make('payment_reference')
                            ->label('Referencia del pago'),

                        DateTimePicker::make('paid_at')
                            ->label('Pagado el')
                            ->seconds(false),
                    ]),

                Section::make('Cliente')
                    ->columns(3)
                    ->schema([
                        TextInput::make('customer_name')->label('Nombre')->required(),
                        TextInput::make('customer_email')->label('Correo')->email()->required(),
                        TextInput::make('customer_phone')->label('Celular')->tel()->required(),
                        Select::make('customer_document_type')
                            ->label('Tipo de documento')
                            ->options(['CC' => 'CC', 'CE' => 'CE', 'NIT' => 'NIT', 'PA' => 'Pasaporte', 'PEP' => 'PEP / PPT'])
                            ->native(false),
                        TextInput::make('customer_document_number')->label('Número de documento'),
                        TextInput::make('customer_city')->label('Ciudad'),
                    ]),

                Section::make('El caso')
                    ->schema([
                        Textarea::make('case_summary')
                            ->label('Lo que contó el cliente')
                            ->rows(8)
                            ->columnSpanFull(),

                        Textarea::make('internal_notes')
                            ->label('Notas internas')
                            ->helperText('El cliente no las ve.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Origen de la venta')
                    ->description('De dónde llegó este cliente. Sirve para medir qué campaña funciona.')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextInput::make('utm_source')->label('Fuente'),
                        TextInput::make('utm_medium')->label('Medio'),
                        TextInput::make('utm_campaign')->label('Campaña'),
                    ]),
            ]);
    }
}
