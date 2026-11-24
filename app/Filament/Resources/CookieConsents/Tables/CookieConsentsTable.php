<?php

declare(strict_types=1);

namespace App\Filament\Resources\CookieConsents\Tables;

use App\Domain\Shared\Models\CookieConsent;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CookieConsentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Cuándo')
                    ->dateTime('j M Y, g:i a')
                    ->sortable(),

                TextColumn::make('decision')
                    ->label('Qué decidió')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'all' => 'Aceptó todas',
                        'necessary' => 'Solo las necesarias',
                        'custom' => 'Eligió una por una',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'all' => 'success',
                        'necessary' => 'gray',
                        default => 'info',
                    }),

                IconColumn::make('analytics')->label('Analítica')->boolean(),
                IconColumn::make('marketing')->label('Publicidad')->boolean(),

                TextColumn::make('policy_version')
                    ->label('Versión del texto')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('source')
                    ->label('Desde')
                    ->placeholder('—'),

                // El token es lo único que identifica al visitante anónimo:
                // con él se honra una revocación y se reconstruye su historia.
                TextColumn::make('token')
                    ->label('Visitante')
                    ->limit(8)
                    ->copyable()
                    ->copyMessage('Identificador copiado')
                    ->tooltip(fn (CookieConsent $record) => $record->token)
                    ->searchable(),

                TextColumn::make('ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('decision')
                    ->label('Decisión')
                    ->options([
                        'all' => 'Aceptó todas',
                        'necessary' => 'Solo las necesarias',
                        'custom' => 'Eligió una por una',
                    ]),
            ])
            ->emptyStateHeading('Todavía nadie ha decidido')
            ->emptyStateDescription('Aquí queda la prueba de cada autorización y de cada revocación, con su fecha.');
    }
}
