<?php

declare(strict_types=1);

namespace App\Filament\Resources\Calculators;

use App\Domain\Tools\Models\Calculator;
use App\Filament\Resources\Calculators\Pages\CreateCalculator;
use App\Filament\Resources\Calculators\Pages\EditCalculator;
use App\Filament\Resources\Calculators\Pages\ListCalculators;
use App\Filament\Resources\Calculators\Schemas\CalculatorForm;
use App\Filament\Resources\Calculators\Tables\CalculatorsTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Las calculadoras legales.
 *
 * Quien calcula su liquidación tiene un problema laboral ahora mismo y un
 * número en la cabeza: es el lead más caliente del embudo. Los parámetros
 * del año (salario mínimo, auxilio de transporte) se editan aquí para no
 * depender de un despliegue cada enero.
 */
class CalculatorResource extends Resource
{
    protected static ?string $model = Calculator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::MARKETING;

    protected static ?string $navigationLabel = 'Calculadoras';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'calculadora';

    protected static ?string $pluralModelLabel = 'calculadoras';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CalculatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CalculatorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCalculators::route('/'),
            'create' => CreateCalculator::route('/create'),
            'edit' => EditCalculator::route('/{record}/edit'),
        ];
    }
}
