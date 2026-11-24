<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriptionPromos;

use App\Domain\Subscriptions\Models\SubscriptionPromo;
use App\Filament\Resources\SubscriptionPromos\Pages\ManageSubscriptionPromos;
use App\Filament\Resources\SubscriptionPromos\Schemas\SubscriptionPromoForm;
use App\Filament\Resources\SubscriptionPromos\Tables\SubscriptionPromosTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Con qué condición entra cada abogado.
 *
 * La que no tiene código es la oferta de lanzamiento: la recibe quien llega
 * por su cuenta. Las que tienen código son enlaces de invitación, para los
 * abogados que se traen a mano y llenan el directorio al principio.
 *
 * Esto se edita aquí y no en el código porque es una decisión comercial que
 * cambia sola: cuando la red ya tenga abogados, el lanzamiento se apaga.
 */
class SubscriptionPromoResource extends Resource
{
    protected static ?string $model = SubscriptionPromo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::NETWORK;

    protected static ?string $navigationLabel = 'Promociones e invitaciones';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'promoción';

    protected static ?string $pluralModelLabel = 'promociones e invitaciones';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SubscriptionPromoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionPromosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubscriptionPromos::route('/'),
        ];
    }
}
