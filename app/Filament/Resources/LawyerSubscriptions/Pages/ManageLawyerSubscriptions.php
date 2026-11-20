<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerSubscriptions\Pages;

use App\Filament\Resources\LawyerSubscriptions\LawyerSubscriptionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLawyerSubscriptions extends ManageRecords
{
    protected static string $resource = LawyerSubscriptionResource::class;
}
