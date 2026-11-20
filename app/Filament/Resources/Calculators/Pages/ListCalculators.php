<?php

declare(strict_types=1);

namespace App\Filament\Resources\Calculators\Pages;

use App\Filament\Resources\Calculators\CalculatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCalculators extends ListRecords
{
    protected static string $resource = CalculatorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
