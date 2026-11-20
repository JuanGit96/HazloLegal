<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerPlans;

use App\Domain\Subscriptions\Models\LawyerPlan;
use App\Filament\Resources\LawyerPlans\Pages\ManageLawyerPlans;
use App\Filament\Resources\LawyerPlans\Schemas\LawyerPlanForm;
use App\Filament\Resources\LawyerPlans\Tables\LawyerPlansTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Lo que le vendemos a los abogados.
 *
 * Es la segunda línea de ingreso del negocio y la que escala sin costo
 * marginal: el abogado paga por aparecer donde ya llega el tráfico que
 * generan los documentos gratuitos.
 */
class LawyerPlanResource extends Resource
{
    protected static ?string $model = LawyerPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::NETWORK;

    protected static ?string $navigationLabel = 'Planes para abogados';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'plan';

    protected static ?string $pluralModelLabel = 'planes para abogados';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LawyerPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LawyerPlansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLawyerPlans::route('/'),
        ];
    }
}
