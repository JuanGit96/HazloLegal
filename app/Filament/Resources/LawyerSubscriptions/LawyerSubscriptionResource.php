<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerSubscriptions;

use App\Domain\Subscriptions\Enums\SubscriptionStatus;
use App\Domain\Subscriptions\Models\LawyerSubscription;
use App\Filament\Resources\LawyerSubscriptions\Pages\ManageLawyerSubscriptions;
use App\Filament\Resources\LawyerSubscriptions\Schemas\LawyerSubscriptionForm;
use App\Filament\Resources\LawyerSubscriptions\Tables\LawyerSubscriptionsTable;
use App\Filament\Support\NavGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LawyerSubscriptionResource extends Resource
{
    protected static ?string $model = LawyerSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::NETWORK;

    protected static ?string $navigationLabel = 'Suscripciones';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'suscripción';

    protected static ?string $pluralModelLabel = 'suscripciones';

    public static function form(Schema $schema): Schema
    {
        return LawyerSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LawyerSubscriptionsTable::configure($table);
    }

    /** Los pagos pendientes son lo que hay que perseguir, así que van en el badge. */
    public static function getNavigationBadge(): ?string
    {
        $pending = LawyerSubscription::query()
            ->where('status', SubscriptionStatus::PastDue)
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLawyerSubscriptions::route('/'),
        ];
    }
}
