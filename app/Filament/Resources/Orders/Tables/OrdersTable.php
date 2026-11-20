<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Orders\Enums\OrderTier;
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
            // La columna de adicionales los lee uno a uno: sin esto es una
            // consulta por fila.
            ->modifyQueryUsing(fn (Builder $query) => $query->with('addons'))
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

                TextColumn::make('tier')
                    ->label('Nivel')
                    ->badge()
                    ->sortable(),

                TextColumn::make('addons_count')
                    ->label('Adicionales')
                    ->counts('addons')
                    ->badge()
                    ->color('gray')
                    ->description(fn (Order $record) => $record->isExpress() ? 'inmediato' : null),

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

                SelectFilter::make('tier')
                    ->label('Nivel')
                    ->options(OrderTier::class)
                    ->multiple(),

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

                Filter::make('shareable')
                    ->label('Autorizó que un abogado lo contacte')
                    ->query(fn (Builder $query) => $query->where('shareable_with_lawyers', true))
                    ->toggle(),

                Filter::make('missing_document')
                    ->label('Pagado y sin documento generado')
                    ->query(fn (Builder $query) => $query->whereNotNull('paid_at')->whereNull('document_path'))
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

                // Si el equipo corrige un dato del cliente, el PDF viejo deja
                // de servir: hay que poder volver a generarlo sin tocar código.
                Action::make('regenerate')
                    ->label('Regenerar PDF')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Volver a generar los archivos')
                    ->modalDescription('Se rehacen el documento y el instructivo con los datos actuales. El cliente verá la versión nueva en su enlace.')
                    ->visible(fn (Order $record) => $record->paid_at !== null)
                    ->action(function (Order $record, OrderService $orders) {
                        $orders->prepareDeliverables($record);

                        Notification::make()
                            ->title("Archivos de {$record->reference} regenerados")
                            ->success()
                            ->send();
                    }),

                Action::make('download')
                    ->label('Ver documento')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->color('info')
                    ->visible(fn (Order $record) => filled($record->document_path))
                    ->url(fn (Order $record) => $record->downloadUrl('documento'))
                    ->openUrlInNewTab(),

                EditAction::make()->label('Abrir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->emptyStateHeading('Todavía no hay pedidos')
            ->emptyStateDescription('Cuando alguien complete el checkout, aparecerá aquí.');
    }
}
