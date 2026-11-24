<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyCases;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Models\LegalCase;
use App\Filament\Lawyer\Resources\MyCases\Pages\ListMyCases;
use App\Filament\Lawyer\Resources\MyCases\Pages\ViewMyCase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Los casos que este abogado se quedó.
 *
 * El filtro no es una conveniencia de la interfaz: es la regla del producto.
 * Un caso se le asigna al abogado que el cliente elige, y los datos de
 * contacto del cliente solo existen para él. Por eso la consulta se acota
 * aquí, en el origen, y no en un menú ni en una vista.
 */
class MyCaseResource extends Resource
{
    protected static ?string $model = LegalCase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'Mi trabajo';

    protected static ?string $navigationLabel = 'Mis casos';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'caso';

    protected static ?string $pluralModelLabel = 'casos';

    public static function getEloquentQuery(): Builder
    {
        $lawyerId = app(CurrentLawyer::class)->id();

        return parent::getEloquentQuery()
            ->where('assigned_lawyer_id', $lawyerId)
            // Sin abogado identificado no hay caso que enseñar. Un whereNull
            // aquí devolvería los casos sin asignar, que es justo lo que no
            // puede pasar.
            ->when($lawyerId === null, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('assigned_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('Caso')
                    ->searchable()
                    ->wrap()
                    ->description(fn (LegalCase $record) => $record->practice_area->getLabel()),

                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (LegalCase $record) => $record->client_city),

                TextColumn::make('urgency')
                    ->label('Urgencia')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('assigned_at')
                    ->label('Lo tomaste')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(CaseStatus::class)
                    ->multiple(),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()->label('Abrir'),
            ])
            ->emptyStateHeading('Todavía no tienes casos')
            ->emptyStateDescription('Cuando llegue un caso de tu área te avisamos por correo. Te postulas y el cliente elige entre los interesados.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyCases::route('/'),
            'view' => ViewMyCase::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->whereIn('status', [CaseStatus::Assigned])
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}
