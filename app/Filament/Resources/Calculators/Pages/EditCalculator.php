<?php

declare(strict_types=1);

namespace App\Filament\Resources\Calculators\Pages;

use App\Filament\Resources\Calculators\CalculatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCalculator extends EditRecord
{
    protected static string $resource = CalculatorResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
