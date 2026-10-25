<?php

declare(strict_types=1);

namespace App\Filament\Resources\GuaranteeClaims;

use App\Domain\Shared\Enums\ClaimReason;
use App\Domain\Shared\Enums\ClaimStatus;
use App\Domain\Shared\Models\GuaranteeClaim;
use App\Filament\Resources\GuaranteeClaims\Pages\ManageGuaranteeClaims;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GuaranteeClaimResource extends Resource
{
    protected static ?string $model = GuaranteeClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OPERATION;

    protected static ?string $navigationLabel = 'Reclamaciones de garantía';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'reclamación';

    protected static ?string $pluralModelLabel = 'reclamaciones';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = GuaranteeClaim::query()->open()->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Resolución')
                ->description('El compromiso público es responder en 24 horas hábiles.')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options(ClaimStatus::class)
                        ->required()
                        ->native(false),

                    DateTimePicker::make('resolved_at')->label('Resuelta el')->seconds(false),

                    Toggle::make('refund_issued')
                        ->label('Se hizo la devolución')
                        ->inline(false),

                    Textarea::make('resolution_note')
                        ->label('Nota de resolución')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('La reclamación')
                ->columns(2)
                ->schema([
                    TextInput::make('reference')->label('Referencia')->disabled()->dehydrated(false),
                    TextInput::make('order_reference')->label('Pedido reclamado')->disabled()->dehydrated(false),
                    TextInput::make('client_name')->label('Cliente')->disabled()->dehydrated(false),
                    TextInput::make('client_email')->label('Correo')->disabled()->dehydrated(false),

                    Select::make('reason')
                        ->label('Motivo')
                        ->options(ClaimReason::class)
                        ->disabled()
                        ->dehydrated(false),

                    Textarea::make('description')
                        ->label('Lo que reporta el cliente')
                        ->rows(6)
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->label('Referencia')->searchable()->weight('bold'),
                TextColumn::make('order_reference')->label('Pedido')->searchable()->copyable(),
                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (GuaranteeClaim $r) => $r->client_email),
                TextColumn::make('reason')->label('Motivo')->badge()->wrap(),
                TextColumn::make('status')->label('Estado')->badge()->sortable(),
                IconColumn::make('refund_issued')->label('Devuelto')->boolean(),
                TextColumn::make('created_at')
                    ->label('Recibida')
                    ->since()
                    ->sortable()
                    ->description(fn (GuaranteeClaim $r) => $r->created_at->diffInHours() > 24 && $r->status === ClaimStatus::Open
                        ? '⚠️ Fuera del plazo de 24 h'
                        : null),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(ClaimStatus::class)->multiple(),
                SelectFilter::make('reason')->label('Motivo')->options(ClaimReason::class),
            ])
            ->recordActions([EditAction::make()->label('Atender')])
            ->emptyStateHeading('Ninguna reclamación')
            ->emptyStateDescription('Buena señal: nadie ha activado la garantía.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGuaranteeClaims::route('/')];
    }
}
