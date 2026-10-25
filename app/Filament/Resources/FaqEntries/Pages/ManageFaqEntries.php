<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqEntries\Pages;

use App\Filament\Resources\FaqEntries\FaqEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFaqEntries extends ManageRecords
{
    protected static string $resource = FaqEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nueva pregunta')];
    }
}
