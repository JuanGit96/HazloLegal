<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\Schemas;

use App\Domain\Catalog\Enums\AutomationPhase;
use App\Domain\Catalog\Enums\PracticeArea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Trámite')
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
                                    ->prefix(url('/documentos').'/')
                                    ->helperText('Cambiarlo rompe los enlaces ya indexados por Google.'),

                                TextInput::make('short_name')
                                    ->label('Nombre corto')
                                    ->helperText('Para tarjetas y botones.'),

                                TextInput::make('emoji')
                                    ->label('Emoji')
                                    ->maxLength(8),

                                Select::make('practice_area')
                                    ->label('Área de práctica')
                                    ->options(PracticeArea::class)
                                    ->required()
                                    ->native(false),

                                Select::make('automation_phase')
                                    ->label('Fase')
                                    ->options(AutomationPhase::class)
                                    ->required()
                                    ->native(false)
                                    ->helperText('Fase 1: plantilla maestra + auditoría. Fase 2: redacción humana.'),

                                TextInput::make('tagline')
                                    ->label('Promesa en una línea')
                                    ->columnSpanFull(),

                                Textarea::make('summary')
                                    ->label('Resumen')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                TextInput::make('legal_basis')
                                    ->label('Fundamento legal')
                                    ->placeholder('Ley 1266 de 2008')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Precio y niveles')
                            ->columns(2)
                            ->schema([
                                TextInput::make('base_price')
                                    ->label('Precio base')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('$')
                                    ->suffix('COP')
                                    ->required()
                                    ->helperText('0 = el trámite es gratis. Lo que se cobre encima sale de los adicionales.'),

                                TextInput::make('compare_at_price')
                                    ->label('Precio tachado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->suffix('COP')
                                    ->helperText('Solo si ese precio fue real alguna vez. Inventarlo es publicidad engañosa (Ley 1480 de 2011).'),

                                TextInput::make('discount_percentage')
                                    ->label('O un descuento en %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->maxValue(90)
                                    ->helperText('Si pones un porcentaje, el precio tachado se calcula solo.'),

                                TextInput::make('sort_order')
                                    ->label('Orden en el catálogo')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('delivery_hours')
                                    ->label('Horas de entrega')
                                    ->numeric()
                                    ->default(24)
                                    ->suffix('horas')
                                    ->required()
                                    ->helperText('Sin el adicional de entrega inmediata, esto es lo que se promete.'),

                                TextInput::make('express_delivery_hours')
                                    ->label('Horas con entrega inmediata')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('horas')
                                    ->helperText('0 = se entrega al instante.'),

                                Toggle::make('self_service_enabled')
                                    ->label('Se puede pedir sin abogado')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Apágalo en lo que no se puede automatizar sin hacer daño, como una tutela: entonces solo se vende con acompañamiento.'),

                                Toggle::make('is_free')
                                    ->label('Aparece entre los gratuitos')
                                    ->inline(false)
                                    ->helperText('Lo muestra en /gratis y en la franja de la home. Debe ir de la mano de un precio base de 0.'),

                                Toggle::make('supports_signature')
                                    ->label('Admite firma electrónica')
                                    ->inline(false),

                                Toggle::make('is_active')
                                    ->label('Publicado')
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('is_featured')
                                    ->label('Destacado en la home')
                                    ->inline(false),

                                TextInput::make('hook')
                                    ->label('Gancho')
                                    ->helperText('La frase corta de la tarjeta en /gratis. «Te responden en 15 días o pueden tutelar».')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Plantilla del documento')
                            ->schema([
                                TextInput::make('template_heading')
                                    ->label('Encabezado del PDF')
                                    ->placeholder('SEÑORES')
                                    ->helperText('La primera línea del documento que recibe el cliente.'),

                                Textarea::make('template_body')
                                    ->label('Cuerpo del documento')
                                    ->rows(18)
                                    ->helperText('Marcadores disponibles: {{ cliente.nombre }}, {{ cliente.documento }}, {{ cliente.correo }}, {{ cliente.telefono }}, {{ cliente.ciudad }}, {{ caso.resumen }}, {{ fecha.larga }}, {{ tramite.nombre }}, {{ pedido.referencia }}. Separa párrafos con una línea en blanco.')
                                    ->columnSpanFull(),

                                Repeater::make('template_fields')
                                    ->label('Campos extra del formulario')
                                    ->helperText('Lo que se le pregunta al cliente además del resumen del caso.')
                                    ->schema([
                                        TextInput::make('key')
                                            ->label('Clave')
                                            ->required()
                                            ->helperText('Se usa como marcador: {{ campo.clave }}'),
                                        TextInput::make('label')->label('Pregunta')->required(),
                                        Select::make('type')
                                            ->label('Tipo')
                                            ->options([
                                                'text' => 'Texto corto',
                                                'textarea' => 'Texto largo',
                                                'date' => 'Fecha',
                                                'number' => 'Número',
                                            ])
                                            ->default('text')
                                            ->native(false),
                                        Toggle::make('required')->label('Obligatorio')->inline(false),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['label'] ?? null)
                                    ->columnSpanFull(),

                                Repeater::make('filing_steps')
                                    ->label('Pasos de radicación (van en el instructivo PDF)')
                                    ->schema([
                                        TextInput::make('title')->label('Paso')->required(),
                                        Textarea::make('body')->label('Explicación')->rows(3)->required(),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                    ->columnSpanFull(),

                                TextInput::make('filing_entity')
                                    ->label('Ante quién se radica')
                                    ->placeholder('La EPS, o cualquier juez de la República'),

                                TextInput::make('response_deadline_days')
                                    ->label('Plazo legal de respuesta')
                                    ->numeric()
                                    ->suffix('días')
                                    ->helperText('Se imprime en el instructivo para que el cliente sepa cuándo reclamar.'),
                            ]),

                        Tab::make('Instructivo')
                            ->schema([
                                Textarea::make('instructions_intro')
                                    ->label('De qué se trata, en una frase')
                                    ->rows(3)
                                    ->helperText('Lo primero que lee. Tiene que entenderlo alguien que nunca ha radicado nada.')
                                    ->columnSpanFull(),

                                TagsInput::make('instructions_attachments')
                                    ->label('Qué tiene que llevar o adjuntar')
                                    ->helperText('Se imprime como una lista con casillas para marcar con lápiz.')
                                    ->columnSpanFull(),

                                Repeater::make('instructions_warnings')
                                    ->label('Cuándo este documento no alcanza')
                                    ->helperText('Las señales del semáforo, explicadas. Van en el instructivo y no en el checkout: avisar cuando ya tiene el documento en la mano sirve más que una alerta que cerró de paso.')
                                    ->schema([
                                        TextInput::make('title')->label('Situación')->required(),
                                        Textarea::make('body')->label('Qué debería hacer entonces')->rows(3)->required(),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                    ->columnSpanFull(),

                                Repeater::make('glossary')
                                    ->label('Palabras que va a encontrar')
                                    ->helperText('«Radicado», «días hábiles», «silencio positivo». Si el cliente no las entiende, no sigue los pasos.')
                                    ->schema([
                                        TextInput::make('term')->label('Palabra')->required(),
                                        TextInput::make('definition')->label('Qué significa, sin tecnicismos')->required(),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['term'] ?? null)
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Repeater::make('filing_channels')
                                    ->label('Dónde se radica')
                                    ->helperText('Los canales oficiales, con su enlace y la fecha en que se verificó. Un enlace muerto en el instructivo es el reclamo más fácil de evitar.')
                                    ->schema([
                                        TextInput::make('name')->label('Canal')->required(),
                                        TextInput::make('url')->label('Enlace')->url(),
                                        \Filament\Forms\Components\DatePicker::make('verified_at')->label('Verificado el'),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                    ->columns(3)
                                    ->columnSpanFull(),

                                TextInput::make('instructions_footer')
                                    ->label('Frase fija del pie')
                                    ->placeholder('HazloLegal no radica por usted.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Qué recibe el cliente')
                            ->schema([
                                TagsInput::make('what_you_get')
                                    ->label('Qué recibe')
                                    ->placeholder('Añade y pulsa Enter')
                                    ->columnSpanFull(),

                                TagsInput::make('required_info')
                                    ->label('Qué necesitamos de él')
                                    ->columnSpanFull(),

                                TagsInput::make('use_cases')
                                    ->label('Casos de uso')
                                    ->columnSpanFull(),

                                TagsInput::make('not_included')
                                    ->label('Qué NO incluye')
                                    ->helperText('Decirlo antes de cobrar evita reclamaciones después.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Contenido SEO')
                            ->schema([
                                RichEditor::make('body')
                                    ->label('Contenido largo de la landing')
                                    ->helperText('Es lo que posiciona la página. Mínimo recomendado: 600 palabras.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Preguntas frecuentes')
                            ->schema([
                                Repeater::make('faqs')
                                    ->label('FAQ del trámite')
                                    ->helperText('Se publican con marcado FAQPage, que puede ganar espacio extra en Google.')
                                    ->schema([
                                        TextInput::make('question')->label('Pregunta')->required(),
                                        Textarea::make('answer')->label('Respuesta')->rows(4)->required(),
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
                                    ->helperText('Máximo 60-65 caracteres para que no se corte en Google.')
                                    ->columnSpanFull(),

                                Textarea::make('seo_description')
                                    ->label('Meta descripción')
                                    ->rows(3)
                                    ->maxLength(320)
                                    ->helperText('Idealmente 150-155 caracteres.')
                                    ->columnSpanFull(),

                                TextInput::make('focus_keyword')
                                    ->label('Palabra clave principal')
                                    ->helperText('La búsqueda exacta que quieres ganar.'),

                                TagsInput::make('seo_keywords')
                                    ->label('Palabras clave secundarias'),
                            ]),
                    ]),
            ]);
    }
}
