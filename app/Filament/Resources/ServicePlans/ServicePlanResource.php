<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServicePlans;

use App\Domain\Catalog\Models\ServicePlan;
use App\Filament\Resources\ServicePlans\Pages\EditServicePlan;
use App\Filament\Resources\ServicePlans\Pages\ListServicePlans;
use App\Filament\Resources\ServicePlans\Schemas\ServicePlanForm;
use App\Filament\Resources\ServicePlans\Tables\ServicePlansTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServicePlanResource extends Resource
{
    protected static ?string $model = ServicePlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::CATALOG;

    protected static ?string $navigationLabel = 'Planes y precios';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'plan';

    protected static ?string $pluralModelLabel = 'planes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ServicePlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicePlansTable::configure($table);
    }

    /** Los planes son tres y definen el modelo de negocio: no se crean ni se borran a la ligera. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicePlans::route('/'),
            'edit' => EditServicePlan::route('/{record}/edit'),
        ];
    }
}
