<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalCases;

use App\Domain\LegalCases\Models\LegalCase;
use App\Filament\Resources\LegalCases\Pages\EditLegalCase;
use App\Filament\Resources\LegalCases\Pages\ListLegalCases;
use App\Filament\Resources\LegalCases\RelationManagers\OffersRelationManager;
use App\Filament\Resources\LegalCases\Schemas\LegalCaseForm;
use App\Filament\Resources\LegalCases\Tables\LegalCasesTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LegalCaseResource extends Resource
{
    protected static ?string $model = LegalCase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OPERATION;

    protected static ?string $navigationLabel = 'Casos complejos';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'caso complejo';

    protected static ?string $pluralModelLabel = 'casos complejos';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return LegalCaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegalCasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [OffersRelationManager::class];
    }

    public static function getNavigationBadge(): ?string
    {
        $open = LegalCase::query()->open()->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Casos sin abogado asignado';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalCases::route('/'),
            'edit' => EditLegalCase::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'client_name', 'client_email', 'title'];
    }
}
