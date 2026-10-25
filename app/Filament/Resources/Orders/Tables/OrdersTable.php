<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (Order $record) => $record->customer_email)
                    ->wrap(),

                TextColumn::make('document.short_name')
                    ->label('Trámite')
                    ->searchable(['legal_documents.name'])
                    ->default(fn (Order $record) => $record->document?->name)
                    ->wrap(),

                TextColumn::make('speed')
                    ->label('Velocidad')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('COP')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')->money('COP'),
                    ]),

                // El semáforo del plazo: lo primero que mira el equipo cada mañana.
                TextColumn::make('promised_at')
                    ->label('Vence')
                    ->dateTime('d M, g:i a')
                    ->sortable()
                    ->color(fn (Order $record) => match (true) {
                        $record->isOverdue() => 'danger',
                        $record->hoursLeft() !== null && $record->hoursLeft() <= 4 => 'warning',
                        default => 'gray',
                    })
                    ->description(fn (Order $record) => $record->isOverdue()
                        ? 'VENCIDO'
                        : ($record->promised_at?->diffForHumans() ?? '—')),

                TextColumn::make('assignedLawyer.name')
                    ->label('Abogado')
                    ->placeholder('Sin asignar')
                    ->toggleable(),

                IconColumn::make('wants_signature')
                    ->label('Firma')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d M Y, g:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(OrderStatus::class)
                    ->multiple(),

                SelectFilter::make('speed')
                    ->label('Velocidad')
                    ->options(DeliverySpeed::class),

                SelectFilter::make('legal_document_id')
                    ->label('Trámite')
                    ->relationship('document', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('overdue')
                    ->label('Solo vencidos')
                    ->query(fn (Builder $query) => $query->overdue())
                    ->toggle(),

                Filter::make('unassigned')
                    ->label('Sin abogado asignado')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_lawyer_id'))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('markInReview')
                    ->label('En redacción')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('info')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::Paid)
                    ->action(function (Order $record, OrderService $orders) {
                        $orders->markInReview($record);

                        Notification::make()
                            ->title("{$record->reference} pasó a redacción")
                            ->success()
                            ->send();
                    }),

                Action::make('markDelivered')
                    ->label('Entregado')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Marcar el pedido como entregado')
                    ->modalDescription('Esto detiene el reloj del plazo comprometido.')
                    ->visible(fn (Order $record) => in_array($record->status, OrderStatus::openStates(), true))
                    ->action(function (Order $record, OrderService $orders) {
                        $orders->markDelivered($record);

                        Notification::make()
                            ->title("{$record->reference} entregado")
                            ->success()
                            ->send();
                    }),

                EditAction::make()->label('Abrir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->emptyStateHeading('Todavía no hay pedidos')
            ->emptyStateDescription('Cuando alguien complete el checkout, aparecerá aquí.');
    }
}
