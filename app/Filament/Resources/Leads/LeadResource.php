<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads;

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

    protected static ?string $navigationLabel = 'Leads y suscriptores';

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
                TextColumn::make('name')->label('Nombre')->searchable()->placeholder('—'),
                TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'lead_magnet' => 'Plantilla gratis',
                        'newsletter' => 'Boletín',
                        'exit_intent' => 'Salida del sitio',
                        default => $state,
                    }),
                TextColumn::make('post.title')->label('Artículo')->limit(50)->placeholder('—')->wrap(),
                TextColumn::make('document.short_name')->label('Interés')->placeholder('—'),
                IconColumn::make('is_subscribed')->label('Suscrito')->boolean(),
                TextColumn::make('consented_at')->label('Autorizó el')->dateTime('d M Y, g:i a')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Capturado')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Origen')
                    ->options([
                        'lead_magnet' => 'Plantilla gratis',
                        'newsletter' => 'Boletín',
                        'exit_intent' => 'Salida del sitio',
                    ]),
                TernaryFilter::make('is_subscribed')->label('Suscrito'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Sin leads todavía')
            ->emptyStateDescription('Los correos capturados con las plantillas gratuitas del blog aparecen aquí.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageLeads::route('/')];
    }
}
