<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessages;

use App\Domain\Shared\Enums\ContactKind;
use App\Domain\Shared\Enums\ContactStatus;
use App\Domain\Shared\Models\ContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lo que escribe el público: consultas y solicitudes de eliminación de datos.
 *
 * Van juntas porque desde la operación son lo mismo —algo a lo que hay que
 * responder— pero la solicitud de eliminación tiene plazo legal y por eso se
 * marca en rojo cuando se le acaba el tiempo.
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OPERATION;

    protected static ?string $navigationLabel = 'Mensajes y solicitudes';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'mensaje';

    protected static ?string $pluralModelLabel = 'mensajes y solicitudes';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = ContactMessage::query()->open()->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return ContactMessage::query()->open()->get()->contains(fn (ContactMessage $m) => $m->isOverdue())
            ? 'danger'
            : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('La solicitud')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options(ContactStatus::class)
                        ->required()
                        ->native(false),

                    Textarea::make('message')
                        ->label('Lo que escribió')
                        ->rows(8)
                        ->disabled()
                        ->columnSpanFull(),

                    Textarea::make('internal_notes')
                        ->label('Notas internas')
                        ->helperText('Quien escribió no las ve. En una eliminación de datos, deja constancia de qué se borró y qué se conservó por obligación legal.')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Radicado')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('kind')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Quién')
                    ->searchable()
                    ->description(fn (ContactMessage $record) => $record->email),

                TextColumn::make('subject')
                    ->label('Asunto')
                    ->placeholder('—')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                // El plazo de una solicitud de datos es legal, no una promesa
                // comercial: vencerlo tiene consecuencias.
                TextColumn::make('due')
                    ->label('Responder antes de')
                    ->state(fn (ContactMessage $record) => $record->dueAt()?->translatedFormat('d M, g:i a'))
                    ->color(fn (ContactMessage $record) => $record->isOverdue() ? 'danger' : 'gray')
                    ->description(fn (ContactMessage $record) => $record->isOverdue() ? 'VENCIDO' : null),

                TextColumn::make('created_at')
                    ->label('Recibido')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('Tipo')
                    ->options(ContactKind::class),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ContactStatus::class)
                    ->multiple(),

                Filter::make('open')
                    ->label('Solo sin resolver')
                    ->query(fn (Builder $query) => $query->open())
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('resolve')
                    ->label('Marcar resuelta')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Deja constancia de que se atendió y cuándo.')
                    ->visible(fn (ContactMessage $record) => in_array($record->status, ContactStatus::openStates(), true))
                    ->action(function (ContactMessage $record) {
                        app(\App\Domain\Shared\Services\ContactService::class)
                            ->resolve($record, auth()->id());

                        Notification::make()
                            ->title("{$record->reference} resuelta")
                            ->success()
                            ->send();
                    }),

                Action::make('reply')
                    ->label('Responder')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->color('info')
                    ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode("Re: {$record->reference}")),

                EditAction::make()->label('Abrir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Sin mensajes')
            ->emptyStateDescription('Aquí llega lo que la gente escribe desde /contacto y las solicitudes de eliminación de datos.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
