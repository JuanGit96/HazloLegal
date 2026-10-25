<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServicePlans\Pages;

use App\Filament\Resources\ServicePlans\ServicePlanResource;
use Filament\Resources\Pages\EditRecord;

class EditServicePlan extends EditRecord
{
    protected static string $resource = ServicePlanResource::class;
}
