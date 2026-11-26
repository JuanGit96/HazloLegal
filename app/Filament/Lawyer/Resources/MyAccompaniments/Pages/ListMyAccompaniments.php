<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyAccompaniments\Pages;

use App\Filament\Lawyer\Resources\MyAccompaniments\MyAccompanimentResource;
use Filament\Resources\Pages\ListRecords;

class ListMyAccompaniments extends ListRecords
{
    protected static string $resource = MyAccompanimentResource::class;
}
