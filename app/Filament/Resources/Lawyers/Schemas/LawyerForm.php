<?php

declare(strict_types=1);

namespace App\Filament\Resources\Lawyers\Schemas;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Enums\LawyerStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LawyerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identidad')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(120),

                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('A este correo llegan las invitaciones de casos.'),

                        TextInput::make('phone')->label('Teléfono')->tel(),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->helperText('Formato internacional sin «+», p. ej. 573001234567.'),

                        TextInput::make('city')->label('Ciudad')->default('Bogotá'),

                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->imageEditor()
                            ->avatar()
                            ->directory('lawyers')
                            ->visibility('public'),
                    ]),

                Section::make('Credenciales')
                    ->description('Son la base de la confianza del cliente y de la señal E-E-A-T que exige Google en contenido legal. Deben ser verificables.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('professional_card')
                            ->label('Tarjeta profesional (T.P.)')
                            ->helperText('Verificable en el registro del Consejo Superior de la Judicatura.')
                            ->required(),

                        TextInput::make('university')->label('Universidad'),

                        TextInput::make('headline')
                            ->label('Especialidad (una línea)')
                            ->placeholder('Especialista en derecho a la salud y seguridad social')
                            ->columnSpanFull(),

                        Textarea::make('bio')
                            ->label('Biografía')
                            ->rows(4)
                            ->maxLength(600)
                            ->helperText('Se muestra en el sitio público y en el correo que recibe el cliente.')
                            ->columnSpanFull(),

                        TextInput::make('years_experience')
                            ->label('Años de experiencia')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('cases_resolved')
                            ->label('Casos resueltos')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),

                Section::make('Áreas y reparto de casos')
                    ->description('Determina a qué casos se le invita. Sin áreas marcadas, solo recibirá casos como relleno.')
                    ->columns(2)
                    ->schema([
                        Select::make('practice_areas')
                            ->label('Áreas de práctica')
                            ->multiple()
                            ->options(PracticeArea::class)
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        TagsInput::make('specialties')
                            ->label('Especialidades concretas')
                            ->placeholder('Añade y pulsa Enter')
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Estado')
                            ->options(LawyerStatus::class)
                            ->default(LawyerStatus::Active->value)
                            ->required()
                            ->native(false),

                        TextInput::make('max_active_cases')
                            ->label('Máximo de casos simultáneos')
                            ->numeric()
                            ->minValue(1)
                            ->default(5)
                            ->helperText('Al alcanzarlo deja de recibir invitaciones.'),

                        Toggle::make('accepts_cases')
                            ->label('Recibe invitaciones de casos')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('is_author')
                            ->label('Puede firmar o revisar artículos del blog')
                            ->inline(false),
                    ]),

                Section::make('Notificaciones')
                    ->columns(2)
                    ->schema([
                        Toggle::make('notify_email')
                            ->label('Avisar por correo')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('notify_whatsapp')
                            ->label('Avisar por WhatsApp')
                            ->inline(false)
                            ->disabled()
                            ->helperText('Pendiente del módulo de WhatsApp Business.'),

                        TextInput::make('rating')
                            ->label('Calificación interna')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(5)
                            ->default(5)
                            ->helperText('Define el orden en que se invita a los abogados del área.'),

                        TextInput::make('sort_order')
                            ->label('Orden en la página pública')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
