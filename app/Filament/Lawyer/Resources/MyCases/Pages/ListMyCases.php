<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyCases\Pages;

use App\Filament\Lawyer\Resources\MyCases\MyCaseResource;
use Filament\Resources\Pages\ListRecords;

class ListMyCases extends ListRecords
{
    protected static string $resource = MyCaseResource::class;

    public function getTitle(): string
    {
        return 'Mis casos';
    }
}
