<?php

declare(strict_types=1);

namespace App\Filament\Resources\LawyerPlans\Pages;

use App\Filament\Resources\LawyerPlans\LawyerPlanResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLawyerPlans extends ManageRecords
{
    protected static string $resource = LawyerPlanResource::class;
}
