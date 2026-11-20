<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Pages;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Lawyers\Services\CurrentLawyer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * El perfil que ve quien busca abogado.
 *
 * El abogado edita lo suyo, pero no todo lo suyo: la tarjeta profesional, la
 * calificación interna y la prioridad en el directorio se quedan fuera. La
 * primera porque no se publica nunca, y las otras dos porque son criterios
 * del negocio, no del abogado.
 */
class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Mi presencia';

    protected static ?string $navigationLabel = 'Mi perfil';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Mi perfil público';

    protected string $view = 'filament.lawyer.pages.my-profile';

    /** @var array<string, mixed> */
    public array $data = [];

    public ?Lawyer $lawyer = null;

    public function mount(): void
    {
        $this->lawyer = app(CurrentLawyer::class)->get();

        abort_unless($this->lawyer !== null, 403);

        $this->form->fill($this->lawyer->only([
            'headline', 'bio', 'long_bio', 'city', 'phone', 'whatsapp',
            'firm_name', 'logo_path', 'photo_path', 'practice_areas', 'specialties',
            'services', 'languages', 'offers_virtual', 'offers_free_first_call',
            'website', 'linkedin', 'years_experience', 'seo_title', 'seo_description',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Cómo te presentas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('headline')
                            ->label('Tu especialidad en una línea')
                            ->placeholder('Especialista en derecho laboral y seguridad social')
                            ->maxLength(140)
                            ->columnSpanFull(),

                        TextInput::make('firm_name')->label('Firma o despacho'),

                        TextInput::make('city')->label('Ciudad')->required(),

                        Textarea::make('bio')
                            ->label('Presentación corta')
                            ->rows(3)
                            ->maxLength(600)
                            ->helperText('Es lo que se lee en la tarjeta del directorio.')
                            ->columnSpanFull(),

                        Textarea::make('long_bio')
                            ->label('Tu perfil completo')
                            ->rows(8)
                            ->helperText('Es lo que posiciona tu perfil en Google. Cuenta casos y resultados concretos, sin prometer resultados judiciales.')
                            ->columnSpanFull(),

                        FileUpload::make('photo_path')
                            ->label('Tu foto')
                            ->image()
                            ->imageEditor()
                            ->avatar()
                            ->disk('public')
                            ->directory('lawyers')
                            ->visibility('public'),

                        FileUpload::make('logo_path')
                            ->label('Logo de tu firma')
                            ->image()
                            ->disk('public')
                            ->directory('lawyers/logos')
                            ->visibility('public'),
                    ]),

                Section::make('Qué haces')
                    ->columns(2)
                    ->schema([
                        Select::make('practice_areas')
                            ->label('Áreas de práctica')
                            ->multiple()
                            ->options(PracticeArea::class)
                            ->required()
                            ->native(false)
                            ->helperText('Determina qué casos y qué contactos te llegan.')
                            ->columnSpanFull(),

                        TagsInput::make('specialties')
                            ->label('Especialidades concretas')
                            ->columnSpanFull(),

                        TagsInput::make('services')
                            ->label('Servicios que ofreces')
                            ->columnSpanFull(),

                        TagsInput::make('languages')->label('Idiomas'),

                        TextInput::make('years_experience')
                            ->label('Años de ejercicio')
                            ->numeric()
                            ->minValue(0),

                        Toggle::make('offers_virtual')
                            ->label('Atiendo virtualmente')
                            ->inline(false),

                        Toggle::make('offers_free_first_call')
                            ->label('Primera llamada sin costo')
                            ->inline(false)
                            ->helperText('Es lo que más clics de contacto genera en el directorio.'),
                    ]),

                Section::make('Cómo te contactan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')->label('Teléfono')->tel(),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->helperText('Formato internacional sin «+», p. ej. 573001234567.'),
                        TextInput::make('website')->label('Sitio web')->url(),
                        TextInput::make('linkedin')->label('LinkedIn')->url(),
                    ]),

                Section::make('Cómo te encuentran en Google')
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Título de tu perfil en Google')
                            ->maxLength(70)
                            ->columnSpanFull(),

                        Textarea::make('seo_description')
                            ->label('Descripción en los resultados')
                            ->rows(2)
                            ->maxLength(320)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $lawyer = app(CurrentLawyer::class)->get();

        abort_unless($lawyer !== null, 403);

        // Solo los campos del formulario: nada de tarjeta profesional,
        // calificación interna ni prioridad en el directorio.
        $lawyer->update($this->form->getState());

        Notification::make()
            ->title('Perfil actualizado')
            ->body($lawyer->is_published
                ? 'Los cambios ya se ven en el directorio.'
                : 'Se guardó. Tu perfil se publica cuando tu plan lo incluye.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $lawyer = app(CurrentLawyer::class)->get();

        return array_values(array_filter([
            Action::make('save')
                ->label('Guardar cambios')
                ->icon(Heroicon::OutlinedCheck)
                ->action('save'),

            $lawyer?->is_published
                ? Action::make('view')
                    ->label('Ver mi perfil público')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(route('lawyers.show', $lawyer->slug))
                    ->openUrlInNewTab()
                : null,
        ]));
    }
}
