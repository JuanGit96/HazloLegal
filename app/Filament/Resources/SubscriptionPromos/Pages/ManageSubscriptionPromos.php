<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriptionPromos\Pages;

use App\Filament\Resources\SubscriptionPromos\SubscriptionPromoResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSubscriptionPromos extends ManageRecords
{
    protected static string $resource = SubscriptionPromoResource::class;
}
