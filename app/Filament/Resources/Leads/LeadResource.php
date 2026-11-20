<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Shared\Models\Lead;
use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::MARKETING;

    protected static ?string $navigationLabel = 'Leads y consentimientos';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'lead';

    protected static ?string $pluralModelLabel = 'leads';

    protected static ?string $recordTitleAttribute = 'email';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->label('Correo')->searchable()->copyable()->weight('bold'),

                TextColumn::make('name')->label('Nombre')->searchable()->placeholder('—')
                    ->description(fn (Lead $record) => $record->phone),

                TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'lead_magnet' => 'Plantilla del blog',
                        'free_document' => 'Documento gratis',
                        'paid_document' => 'Documento pagado',
                        'calculator' => 'Calculadora',
                        'newsletter' => 'Boletín',
                        'exit_intent' => 'Salida del sitio',
                        'lawyer_signup' => 'Registro de abogado',
                        default => $state,
                    }),

                TextColumn::make('practice_area')
                    ->label('Área')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('city')->label('Ciudad')->placeholder('—')->toggleable(),

                // Las dos casillas que deciden qué se puede hacer con este dato.
                IconColumn::make('consent_marketing')
                    ->label('Correos')
                    ->boolean()
                    ->tooltip('Autorizó recibir guías por correo'),

                IconColumn::make('consent_share_with_lawyers')
                    ->label('Abogados')
                    ->boolean()
                    ->tooltip('Autorizó que un abogado de la red lo contacte'),

                TextColumn::make('accesses_count')
                    ->label('Entregado a')
                    ->counts('accesses')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('consented_at')
                    ->label('Autorizó el')
                    ->dateTime('d M Y, g:i a')
                    ->placeholder('—')
                    ->description(fn (Lead $record) => $record->consent_policy_version
                        ? 'política '.$record->consent_policy_version
                        : null)
                    ->toggleable(),

                TextColumn::make('consent_revoked_at')
                    ->label('Revocó el')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->color('danger')
                    ->toggleable(),

                TextColumn::make('created_at')->label('Capturado')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Origen')
                    ->multiple()
                    ->options([
                        'free_document' => 'Documento gratis',
                        'paid_document' => 'Documento pagado',
                        'calculator' => 'Calculadora',
                        'lead_magnet' => 'Plantilla del blog',
                        'newsletter' => 'Boletín',
                        'lawyer_signup' => 'Registro de abogado',
                    ]),

                SelectFilter::make('practice_area')
                    ->label('Área')
                    ->options(PracticeArea::class),

                TernaryFilter::make('consent_share_with_lawyers')
                    ->label('Autorizó contacto de abogados'),

                TernaryFilter::make('consent_marketing')
                    ->label('Autorizó correos'),

                \Filament\Tables\Filters\Filter::make('revoked')
                    ->label('Solo los que revocaron')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereNotNull('consent_revoked_at'))
                    ->toggle(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('revoke')
                    ->label('Revocar consentimiento')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revocar el consentimiento de este titular')
                    ->modalDescription('Deja de entregarse a abogados y sale de los envíos. Es un derecho del titular (Ley 1581 de 2012) y hay que poder ejercerlo en un clic cuando lo pide.')
                    ->visible(fn (Lead $record) => $record->consent_revoked_at === null)
                    ->action(function (Lead $record) {
                        $record->update([
                            'consent_revoked_at' => now(),
                            'consent_marketing' => false,
                            'consent_share_with_lawyers' => false,
                            'is_subscribed' => false,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Consentimiento revocado')
                            ->body('Este contacto ya no se comparte ni recibe correos.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Sin leads todavía')
            ->emptyStateDescription('Aquí llega quien genera un documento gratis, usa una calculadora o descarga una plantilla del blog, con el registro de lo que autorizó.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageLeads::route('/')];
    }
}
