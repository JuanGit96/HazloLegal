<?php

declare(strict_types=1);

namespace App\Filament\Resources\Testimonials;

use App\Domain\Content\Models\Testimonial;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::CONTENT;

    protected static ?string $navigationLabel = 'Testimonios';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'testimonio';

    protected static ?string $pluralModelLabel = 'testimonios';

    protected static ?string $recordTitleAttribute = 'author_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('⚠️ Antes de publicar')
                ->description('Publicar testimonios inventados es publicidad engañosa y la SIC puede sancionarlo (Ley 1480 de 2011, art. 30). Usa solo testimonios reales y con autorización escrita del cliente.')
                ->schema([]),

            Section::make('El testimonio')
                ->columns(2)
                ->schema([
                    TextInput::make('author_name')->label('Nombre del cliente')->required(),
                    TextInput::make('author_city')->label('Ciudad'),
                    TextInput::make('author_role')->label('Ocupación o rol'),

                    Select::make('rating')
                        ->label('Estrellas')
                        ->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★'])
                        ->default(5)
                        ->native(false),

                    Textarea::make('quote')
                        ->label('Lo que dijo')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('outcome')
                        ->label('Resultado concreto')
                        ->placeholder('Cirugía autorizada en 6 días')
                        ->helperText('Un resultado medible convierte mucho más que un adjetivo.')
                        ->columnSpanFull(),
                ]),

            Section::make('Contexto y publicación')
                ->columns(2)
                ->schema([
                    Select::make('legal_document_id')
                        ->label('Trámite relacionado')
                        ->relationship('document', 'name')
                        ->searchable()
                        ->preload(),

                    Select::make('source')
                        ->label('Origen')
                        ->options(['whatsapp' => 'WhatsApp', 'google' => 'Google', 'email' => 'Correo'])
                        ->default('whatsapp')
                        ->native(false),

                    FileUpload::make('screenshot_path')
                        ->label('Captura de pantalla')
                        ->image()
                        ->directory('testimonials')
                        ->helperText('Si subes una captura de WhatsApp, tapa los datos personales.'),

                    TextInput::make('sort_order')->label('Orden')->numeric()->default(0),

                    Toggle::make('is_published')->label('Publicado')->default(true)->inline(false),
                    Toggle::make('show_on_home')->label('Mostrar en la home')->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('author_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Testimonial $r) => collect([$r->author_role, $r->author_city])->filter()->implode(' · ')),

                TextColumn::make('outcome')->label('Resultado')->badge()->color('success')->placeholder('—')->wrap(),
                TextColumn::make('quote')->label('Testimonio')->limit(80)->wrap()->toggleable(),
                TextColumn::make('document.short_name')->label('Trámite')->placeholder('—'),
                TextColumn::make('rating')->label('★')->formatStateUsing(fn (int $state) => str_repeat('★', $state))->alignCenter(),
                IconColumn::make('show_on_home')->label('En home')->boolean(),
                IconColumn::make('is_published')->label('Publicado')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Publicado'),
                TernaryFilter::make('show_on_home')->label('En la home'),
            ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTestimonials::route('/')];
    }
}
