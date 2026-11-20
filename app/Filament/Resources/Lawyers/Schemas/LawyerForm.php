<?php

declare(strict_types=1);

namespace App\Filament\Resources\Lawyers\Schemas;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Enums\LawyerKind;
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
                        Select::make('kind')
                            ->label('Tipo')
                            ->options(LawyerKind::class)
                            ->default(LawyerKind::Individual->value)
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText('Una firma se presenta con su nombre comercial y su NIT; un abogado, con el suyo.'),

                        TextInput::make('name')
                            ->label(fn ($get) => $get('kind') === LawyerKind::Firm->value
                                ? 'Nombre de la firma'
                                : 'Nombre completo')
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

                Section::make('Credenciales (uso interno)')
                    ->description('La tarjeta profesional NO se publica en el sitio ni en los correos: se guarda solo para el control interno del equipo. Lo que sí es público es el nombre, la especialidad y la biografía.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('professional_card')
                            ->label('Tarjeta profesional (T.P.)')
                            ->helperText('Solo para verificación interna. No aparece en ninguna página pública. En una firma, la del representante.')
                            ->required(),

                        TextInput::make('nit')
                            ->label('NIT de la firma')
                            ->visible(fn ($get) => $get('kind') === LawyerKind::Firm->value)
                            ->helperText('Para verificar que la firma existe. Tampoco se publica.'),

                        TextInput::make('contact_name')
                            ->label('Con quién hablamos')
                            ->visible(fn ($get) => $get('kind') === LawyerKind::Firm->value),

                        TextInput::make('team_size')
                            ->label('Abogados en la firma')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('kind') === LawyerKind::Firm->value),

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

                Section::make('Perfil público del directorio')
                    ->description('Lo que ve quien llega buscando un abogado. La tarjeta profesional nunca sale aquí: publicarla comprometería a este abogado con una revisión individual que no hace.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Perfil publicado')
                            ->inline(false)
                            ->helperText('Requiere un plan con perfil público. Sin esto, no aparece en /abogados.'),

                        Toggle::make('is_featured')
                            ->label('Destacado en el directorio')
                            ->inline(false),

                        TextInput::make('firm_name')
                            ->label('Firma o despacho')
                            ->placeholder('Opcional'),

                        FileUpload::make('logo_path')
                            ->label('Logo de la firma')
                            ->image()
                            ->directory('lawyers/logos')
                            ->visibility('public'),

                        Textarea::make('long_bio')
                            ->label('Biografía larga del perfil')
                            ->rows(6)
                            ->helperText('Es el contenido que posiciona su perfil en Google. Cuanto más concreto, mejor.')
                            ->columnSpanFull(),

                        TagsInput::make('services')
                            ->label('Servicios que ofrece')
                            ->placeholder('Añade y pulsa Enter')
                            ->columnSpanFull(),

                        TagsInput::make('languages')
                            ->label('Idiomas')
                            ->columnSpanFull(),

                        Toggle::make('offers_virtual')
                            ->label('Atiende virtualmente')
                            ->inline(false),

                        Toggle::make('offers_free_first_call')
                            ->label('Primera llamada sin costo')
                            ->inline(false),

                        TextInput::make('website')
                            ->label('Sitio web')
                            ->url()
                            ->prefix('https://'),

                        TextInput::make('linkedin')
                            ->label('LinkedIn')
                            ->url(),

                        TextInput::make('seo_title')
                            ->label('Título SEO del perfil')
                            ->maxLength(70)
                            ->columnSpanFull(),

                        Textarea::make('seo_description')
                            ->label('Meta descripción del perfil')
                            ->rows(2)
                            ->maxLength(320)
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('Usuario del panel asociado')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin acceso al panel')
                            ->helperText('Para que el abogado entre a ver sus propios casos y contactos.'),
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
