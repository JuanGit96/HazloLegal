<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalCases\Schemas;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\LegalCases\Enums\BudgetRange;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Enums\CaseUrgency;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalCaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asignación')
                    ->description('El reparto es automático, pero se puede intervenir a mano.')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(CaseStatus::class)
                            ->required()
                            ->native(false),

                        Select::make('assigned_lawyer_id')
                            ->label('Abogado asignado')
                            ->relationship('assignedLawyer', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin asignar')
                            ->helperText('Asignar aquí sobrescribe la carrera por el caso.'),

                        DateTimePicker::make('assigned_at')
                            ->label('Asignado el')
                            ->seconds(false),

                        DateTimePicker::make('dispatched_at')
                            ->label('Enviado a abogados el')
                            ->seconds(false),

                        DateTimePicker::make('expires_at')
                            ->label('La invitación vence el')
                            ->seconds(false),

                        TextInput::make('offers_sent')
                            ->label('Invitaciones enviadas')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('El caso')
                    ->columns(3)
                    ->schema([
                        TextInput::make('reference')
                            ->label('Referencia')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('practice_area')
                            ->label('Área')
                            ->options(PracticeArea::class)
                            ->required()
                            ->native(false),

                        Select::make('urgency')
                            ->label('Urgencia')
                            ->options(CaseUrgency::class)
                            ->required()
                            ->native(false),

                        TextInput::make('title')
                            ->label('Resumen')
                            ->required()
                            ->columnSpan(2),

                        Select::make('budget_range')
                            ->label('Presupuesto indicado')
                            ->options(BudgetRange::class)
                            ->native(false),

                        Textarea::make('description')
                            ->label('Lo que contó el cliente')
                            ->rows(10)
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('deadline_note')
                            ->label('Término o fecha límite')
                            ->columnSpan(2),

                        Toggle::make('has_documents')
                            ->label('Dice tener documentos')
                            ->inline(false),
                    ]),

                Section::make('Cliente')
                    ->columns(4)
                    ->schema([
                        TextInput::make('client_name')->label('Nombre')->required(),
                        TextInput::make('client_email')->label('Correo')->email()->required(),
                        TextInput::make('client_phone')->label('Celular')->tel()->required(),
                        TextInput::make('client_city')->label('Ciudad'),
                    ]),

                Section::make('Notas internas')
                    ->collapsed()
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Notas del equipo')
                            ->helperText('El cliente y los abogados no las ven.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
