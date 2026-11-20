<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceAddons;

use App\Domain\Catalog\Models\ServiceAddon;
use App\Filament\Resources\ServiceAddons\Pages\CreateServiceAddon;
use App\Filament\Resources\ServiceAddons\Pages\EditServiceAddon;
use App\Filament\Resources\ServiceAddons\Pages\ListServiceAddons;
use App\Filament\Resources\ServiceAddons\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ServiceAddons\Schemas\ServiceAddonForm;
use App\Filament\Resources\ServiceAddons\Tables\ServiceAddonsTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Los «toppings».
 *
 * Aquí vive el catálogo global de adicionales y su precio por defecto. El
 * precio real para un trámite concreto se ajusta en la pestaña «Trámites»
 * de cada adicional o, de un golpe, en la Matriz de precios.
 */
class ServiceAddonResource extends Resource
{
    protected static ?string $model = ServiceAddon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::CATALOG;

    protected static ?string $navigationLabel = 'Adicionales';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'adicional';

    protected static ?string $pluralModelLabel = 'adicionales';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ServiceAddonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceAddonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceAddons::route('/'),
            'create' => CreateServiceAddon::route('/create'),
            'edit' => EditServiceAddon::route('/{record}/edit'),
        ];
    }
}
