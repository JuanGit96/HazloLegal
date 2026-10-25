<?php

declare(strict_types=1);

namespace App\Filament\Resources\GuaranteeClaims\Pages;

use App\Filament\Resources\GuaranteeClaims\GuaranteeClaimResource;
use Filament\Resources\Pages\ManageRecords;

class ManageGuaranteeClaims extends ManageRecords
{
    protected static string $resource = GuaranteeClaimResource::class;
}
