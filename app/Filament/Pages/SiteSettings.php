<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Settings\Models\SiteSetting;
use App\Domain\Settings\Services\SettingsService;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Los ajustes del sitio, agrupados como los piensa el equipo.
 *
 * El formulario se construye desde la tabla `site_settings`: añadir un
 * ajuste nuevo es insertar una fila con su grupo y su tipo, sin tocar esta
 * clase. Así el panel no se queda corto cada vez que hace falta un texto
 * configurable más.
 */
class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::SETTINGS;

    protected static ?string $navigationLabel = 'Ajustes del sitio';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Ajustes del sitio';

    protected string $view = 'filament.pages.site-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    private const GROUP_LABELS = [
        'marca' => 'Marca y logo',
        'ganchos' => 'Ganchos y textos',
        'promociones' => 'Promociones',
        'operacion' => 'Operación',
    ];

    private const GROUP_HELP = [
        'marca' => 'El logo y los colores se usan en el sitio, en el panel y en los PDF que circulan por fuera.',
        'ganchos' => 'El titular de la home es lo primero que hay que probar cuando la conversión no sube.',
        'promociones' => 'Un precio tachado solo es legítimo si ese precio fue real. Inventarlo es publicidad engañosa (Ley 1480 de 2011) y la SIC lo sanciona.',
        'operacion' => 'Interruptores que encienden o apagan partes del sitio.',
    ];

    public function mount(): void
    {
        $this->form->fill(
            SiteSetting::query()->get()
                ->mapWithKeys(fn (SiteSetting $s) => [$s->key => $s->typedValue()])
                ->all()
        );
    }

    public function form(Schema $schema): Schema
    {
        $groups = SiteSetting::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Ajustes')
                    ->columnSpanFull()
                    ->tabs(
                        $groups->map(fn ($settings, string $group) => Tab::make(
                            self::GROUP_LABELS[$group] ?? ucfirst($group)
                        )->schema([
                            Section::make()
                                ->description(self::GROUP_HELP[$group] ?? null)
                                ->columns(2)
                                ->schema($settings->map(fn (SiteSetting $setting) => self::field($setting))->all()),
                        ]))->values()->all()
                    ),
            ]);
    }

    /** Un campo por tipo declarado en la fila. */
    private static function field(SiteSetting $setting)
    {
        $label = $setting->label ?: $setting->key;

        return match ($setting->type) {
            'bool' => Toggle::make($setting->key)
                ->label($label)
                ->helperText($setting->help)
                ->inline(false),

            'int' => TextInput::make($setting->key)
                ->label($label)
                ->helperText($setting->help)
                ->numeric(),

            'text' => Textarea::make($setting->key)
                ->label($label)
                ->helperText($setting->help)
                ->rows(3)
                ->columnSpanFull(),

            'image' => FileUpload::make($setting->key)
                ->label($label)
                ->helperText($setting->help)
                ->image()
                ->disk('public')
                ->directory('brand')
                ->visibility('public'),

            default => TextInput::make($setting->key)
                ->label($label)
                ->helperText($setting->help),
        };
    }

    public function save(): void
    {
        $values = $this->form->getState();

        foreach ($values as $key => $value) {
            SiteSetting::query()->where('key', $key)->update([
                'value' => match (true) {
                    is_bool($value) => $value ? '1' : '0',
                    is_array($value) => json_encode($value),
                    default => (string) ($value ?? ''),
                },
            ]);
        }

        app(SettingsService::class)->flush();

        Notification::make()
            ->title('Ajustes guardados')
            ->body('Los cambios ya se ven en el sitio público.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar ajustes')
                ->icon(Heroicon::OutlinedCheck)
                ->action('save'),
        ];
    }
}
