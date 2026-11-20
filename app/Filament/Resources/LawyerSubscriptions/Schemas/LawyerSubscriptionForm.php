<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerSubscriptions\Schemas;

use App\Domain\Subscriptions\Enums\BillingPeriod;
use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LawyerSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quién y qué plan')
                    ->columns(2)
                    ->schema([
                        Select::make('lawyer_id')
                            ->label('Abogado')
                            ->relationship('lawyer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('lawyer_plan_id')
                            ->label('Plan')
                            ->relationship('plan', 'name')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $plan = \App\Domain\Subscriptions\Models\LawyerPlan::find($state);
                                $period = BillingPeriod::tryFrom((string) $get('billing_period')) ?? BillingPeriod::Monthly;

                                if ($plan) {
                                    $set('price', $plan->priceFor($period));
                                }
                            }),

                        Select::make('billing_period')
                            ->label('Periodo')
                            ->options(BillingPeriod::class)
                            ->default(BillingPeriod::Monthly->value)
                            ->required()
                            ->native(false),

                        TextInput::make('price')
                            ->label('Lo que paga realmente')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('COP')
                            ->required()
                            ->helperText('Puede diferir del precio de lista: aquí se registran los acuerdos puntuales.'),
                    ]),

                Section::make('Estado y fechas')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(SubscriptionStatus::class)
                            ->default(SubscriptionStatus::Trialing->value)
                            ->required()
                            ->native(false),

                        DateTimePicker::make('trial_ends_at')
                            ->label('Termina la prueba')
                            ->seconds(false),

                        DateTimePicker::make('started_at')
                            ->label('Empezó')
                            ->seconds(false),

                        DateTimePicker::make('renews_at')
                            ->label('Renueva')
                            ->seconds(false),

                        DateTimePicker::make('featured_until')
                            ->label('Destacado hasta')
                            ->seconds(false)
                            ->helperText('Mientras esté vigente, aparece arriba en el directorio.'),

                        DateTimePicker::make('cancelled_at')
                            ->label('Cancelada')
                            ->seconds(false),
                    ]),
            ]);
    }
}
