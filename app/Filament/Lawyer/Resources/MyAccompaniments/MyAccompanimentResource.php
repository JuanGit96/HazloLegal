<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyAccompaniments;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\Orders\Models\Order;
use App\Filament\Lawyer\Resources\MyAccompaniments\Pages\ListMyAccompaniments;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Los pedidos en los que el cliente pagó para que un abogado los escriba.
 *
 * Es trabajo pagado, no una oportunidad: cuando un pedido lleva el adicional
 * de acompañamiento, alguien tiene que redactarlo de verdad. El abogado
 * asignado no lo veía en ninguna parte y se enteraba por correo, que es la
 * forma más fácil de que algo se pierda.
 *
 * La consulta se acota al abogado asignado y no se filtra en la vista: los
 * datos del cliente de un pedido ajeno no se le enseñan a nadie, y eso tiene
 * que ser imposible por construcción, no por disciplina.
 */
class MyAccompanimentResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Mi trabajo';

    protected static ?string $navigationLabel = 'Acompañamientos';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'acompañamiento';

    protected static ?string $pluralModelLabel = 'acompañamientos';

    public static function getEloquentQuery(): Builder
    {
        $lawyerId = app(CurrentLawyer::class)->id();

        return parent::getEloquentQuery()
            ->with(['document', 'addons'])
            ->where('assigned_lawyer_id', $lawyerId)
            ->when($lawyerId === null, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('document.name')
                    ->label('Trámite')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn (Order $record) => $record->customer_city),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),

                TextColumn::make('promised_at')
                    ->label('Comprometido')
                    ->dateTime('j M, g:i a')
                    ->placeholder('—')
                    // Un plazo vencido en rojo: el cliente pagó por una
                    // fecha y la garantía la cubre la empresa.
                    ->color(fn (Order $record) => $record->promised_at?->isPast() ? 'danger' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Entró')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('Escribirle')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (Order $record) => filled($record->customer_phone))
                    ->url(fn (Order $record) => 'https://wa.me/'.preg_replace('/\D+/', '', '57'.ltrim((string) $record->customer_phone, '+57')))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Sin acompañamientos asignados')
            ->emptyStateDescription('Cuando un cliente pague por que un abogado redacte su documento y te lo asignemos, aparece aquí con sus datos y su plazo.')
            ->emptyStateIcon('heroicon-o-pencil-square');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyAccompaniments::route('/'),
        ];
    }
}
