<?php

declare(strict_types=1);

namespace App\Filament\Resources\Calculators\Pages;

use App\Filament\Resources\Calculators\CalculatorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCalculator extends CreateRecord
{
    protected static string $resource = CalculatorResource::class;
}
