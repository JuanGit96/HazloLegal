<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalCases\Tables;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Enums\CaseUrgency;
use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\LegalCases\Services\CaseDispatchService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LegalCasesTable
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

                TextColumn::make('title')
                    ->label('Caso')
                    ->searchable()
                    ->wrap()
                    ->limit(90)
                    ->width('32%')
                    ->description(fn (LegalCase $record) => $record->client_name),

                TextColumn::make('practice_area')
                    ->label('Área')
                    ->badge()
                    ->sortable(),

                TextColumn::make('urgency')
                    ->label('Urgencia')
                    ->badge()
                    ->formatStateUsing(fn (CaseUrgency $state) => $state->shortLabel())
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('offers_sent')
                    ->label('Invitados')
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('assignedLawyer.name')
                    ->label('Abogado')
                    ->placeholder('Nadie aún')
                    ->description(fn (LegalCase $record) => $record->responseTime()
                        ? "Respondió en {$record->responseTime()}"
                        : null),

                TextColumn::make('expires_at')
                    ->label('Vence')
                    ->dateTime('d M, g:i a')
                    ->sortable()
                    ->color(fn (LegalCase $record) => $record->hasExpired() ? 'danger' : 'gray')
                    ->description(fn (LegalCase $record) => $record->hasExpired() ? 'VENCIDO' : null)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Recibido')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(CaseStatus::class)
                    ->multiple(),

                SelectFilter::make('practice_area')
                    ->label('Área')
                    ->options(PracticeArea::class)
                    ->multiple(),

                SelectFilter::make('urgency')
                    ->label('Urgencia')
                    ->options(CaseUrgency::class),

                Filter::make('unassigned')
                    ->label('Sin abogado')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_lawyer_id'))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Reenviar')
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar el caso a los abogados')
                    ->modalDescription('Vuelve a enviar el correo a los abogados que todavía no han respondido.')
                    ->visible(fn (LegalCase $record) => ! $record->isTaken())
                    ->action(function (LegalCase $record, CaseDispatchService $dispatcher) {
                        $sent = $dispatcher->resend($record);

                        Notification::make()
                            ->title($sent > 0 ? "Reenviado a {$sent} abogados" : 'No había invitaciones pendientes')
                            ->status($sent > 0 ? 'success' : 'warning')
                            ->send();
                    }),

                Action::make('dispatch')
                    ->label('Repartir')
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Repartir el caso')
                    ->modalDescription('Busca abogados del área con cupo libre y les envía la invitación.')
                    ->visible(fn (LegalCase $record) => $record->status === CaseStatus::New)
                    ->action(function (LegalCase $record, CaseDispatchService $dispatcher) {
                        $sent = $dispatcher->dispatchToLawyers($record);

                        Notification::make()
                            ->title($sent > 0 ? "Enviado a {$sent} abogados" : 'No hay abogados disponibles en el área')
                            ->status($sent > 0 ? 'success' : 'danger')
                            ->send();
                    }),

                EditAction::make()->label('Abrir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->emptyStateHeading('Sin casos complejos')
            ->emptyStateDescription('Los casos enviados desde el formulario público aparecen aquí.');
    }
}
