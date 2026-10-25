<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalCases\RelationManagers;

use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Models\CaseOffer;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** La carrera, tal como quedó registrada: quién fue invitado y qué respondió. */
class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $title = 'Invitaciones enviadas';

    protected static ?string $modelLabel = 'invitación';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('status')
            ->columns([
                TextColumn::make('lawyer.name')
                    ->label('Abogado')
                    ->searchable()
                    ->description(fn (CaseOffer $record) => $record->lawyer?->headline),

                TextColumn::make('status')
                    ->label('Respuesta')
                    ->badge(),

                TextColumn::make('sent_at')
                    ->label('Enviada')
                    ->dateTime('d M, g:i a'),

                TextColumn::make('responded_at')
                    ->label('Respondió')
                    ->dateTime('d M, g:i a')
                    ->placeholder('Sin responder')
                    ->description(fn (CaseOffer $record) => $record->responded_at && $record->sent_at
                        ? 'En '.$record->sent_at->diffForHumans($record->responded_at, short: true, syntax: true)
                        : null),

                TextColumn::make('decline_reason')
                    ->label('Motivo del rechazo')
                    ->wrap()
                    ->placeholder('—')
                    ->visible(fn () => true),
            ])
            ->emptyStateHeading('Todavía no se ha repartido')
            ->emptyStateDescription('Usa la acción «Repartir» para enviar el caso a los abogados del área.');
    }
}
