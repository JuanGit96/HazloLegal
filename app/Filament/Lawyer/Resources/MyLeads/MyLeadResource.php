<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyLeads;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Shared\Models\Lead;
use App\Domain\Shared\Models\LeadAccess;
use App\Filament\Lawyer\Resources\MyLeads\Pages\ListMyLeads;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Los contactos que un abogado suscrito puede trabajar.
 *
 * Tres filtros, y los tres son obligatorios:
 *
 * 1. Solo personas que marcaron «quiero que un abogado me contacte» y no han
 *    revocado. Sin eso esto sería una venta de datos.
 * 2. Solo de las áreas que este abogado ejerce. Entregar más de lo necesario
 *    no se justifica ni ante el titular ni ante la SIC.
 * 3. Solo si su plan incluye esa característica.
 *
 * El listado no muestra correo ni teléfono: hay que abrir el contacto, y esa
 * apertura queda registrada. Si mañana un titular pregunta quién accedió a
 * sus datos, hay una respuesta.
 */
class MyLeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Mi trabajo';

    protected static ?string $navigationLabel = 'Contactos';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'contacto';

    protected static ?string $pluralModelLabel = 'contactos';

    public static function canCreate(): bool
    {
        return false;
    }

    /** Sin la característica en el plan, el menú ni siquiera aparece. */
    public static function shouldRegisterNavigation(): bool
    {
        return app(CurrentLawyer::class)->allows('includes_lead_data');
    }

    public static function canViewAny(): bool
    {
        return app(CurrentLawyer::class)->allows('includes_lead_data');
    }

    public static function getEloquentQuery(): Builder
    {
        $lawyer = app(CurrentLawyer::class)->get();

        if (! $lawyer || ! app(CurrentLawyer::class)->allows('includes_lead_data')) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $areas = collect($lawyer->practice_areas ?? [])
            ->map(fn ($area) => $area instanceof PracticeArea ? $area->value : (string) $area)
            ->all();

        return parent::getEloquentQuery()
            ->shareable()
            ->when($areas !== [], fn (Builder $query) => $query->whereIn('practice_area', $areas))
            ->when($areas === [], fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Nombre parcial: para decidir si el caso interesa no hace
                // falta el nombre completo, y menos el correo.
                TextColumn::make('name')
                    ->label('Persona')
                    ->formatStateUsing(fn (?string $state) => $state
                        ? Str::of($state)->explode(' ')->take(2)->implode(' ')
                        : 'Sin nombre')
                    ->description(fn (Lead $record) => $record->city),

                TextColumn::make('practice_area')
                    ->label('Área')
                    ->badge(),

                TextColumn::make('case_summary')
                    ->label('Qué necesita')
                    ->limit(90)
                    ->wrap()
                    ->placeholder('No lo describió'),

                TextColumn::make('created_at')
                    ->label('Llegó')
                    ->since()
                    ->sortable(),

                TextColumn::make('accesses_count')
                    ->label('Abogados que ya lo vieron')
                    ->counts('accesses')
                    ->badge()
                    ->color(fn (int $state) => $state > 2 ? 'warning' : 'gray')
                    ->tooltip('Un contacto muy visto ya recibió varias llamadas.'),
            ])
            ->filters([
                SelectFilter::make('practice_area')
                    ->label('Área')
                    ->options(PracticeArea::class),
            ])
            ->recordActions([
                Action::make('reveal')
                    ->label('Ver datos de contacto')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('primary')
                    ->modalHeading('Datos de contacto')
                    ->modalDescription('Esta persona autorizó que un abogado de la red la contacte. Quedará registrado que fuiste tú quien abrió sus datos.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->schema(fn (Lead $record) => [
                        Section::make()
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name')->label('Nombre')->state($record->name),
                                TextEntry::make('email')->label('Correo')->state($record->email)->copyable(),
                                TextEntry::make('phone')->label('Celular')->state($record->phone ?: '—')->copyable(),
                                TextEntry::make('city')->label('Ciudad')->state($record->city ?: '—'),
                                TextEntry::make('case_summary')
                                    ->label('Lo que contó')
                                    ->state($record->case_summary ?: 'No dejó descripción.')
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (Lead $record) {
                        static::registerAccess($record, 'viewed');
                    })
                    ->after(fn () => Notification::make()
                        ->title('Acceso registrado')
                        ->body('Trátalo con el cuidado que exige la Ley 1581 de 2012.')
                        ->info()
                        ->send()),
            ])
            ->emptyStateHeading('No hay contactos disponibles')
            ->emptyStateDescription('Aquí aparecen las personas de tus áreas que pidieron que un abogado las contacte.');
    }

    /** Deja constancia de quién abrió qué y cuándo. */
    private static function registerAccess(Lead $lead, string $action): void
    {
        $lawyerId = app(CurrentLawyer::class)->id();

        if ($lawyerId === null) {
            return;
        }

        LeadAccess::query()->create([
            'lead_id' => $lead->id,
            'lawyer_id' => $lawyerId,
            'action' => $action,
            'accessed_at' => now(),
            'ip_address' => request()->ip(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyLeads::route('/'),
        ];
    }
}
