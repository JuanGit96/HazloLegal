<?php

declare(strict_types=1);

namespace App\Filament\Resources\Lawyers;

use App\Domain\Lawyers\Models\Lawyer;
use App\Filament\Resources\Lawyers\Pages\CreateLawyer;
use App\Filament\Resources\Lawyers\Pages\EditLawyer;
use App\Filament\Resources\Lawyers\Pages\ListLawyers;
use App\Filament\Resources\Lawyers\Schemas\LawyerForm;
use App\Filament\Resources\Lawyers\Tables\LawyersTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LawyerResource extends Resource
{
    protected static ?string $model = Lawyer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::NETWORK;

    protected static ?string $navigationLabel = 'Abogados';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'abogado';

    protected static ?string $pluralModelLabel = 'abogados';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LawyerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LawyersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLawyers::route('/'),
            'create' => CreateLawyer::route('/create'),
            'edit' => EditLawyer::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'professional_card'];
    }
}
