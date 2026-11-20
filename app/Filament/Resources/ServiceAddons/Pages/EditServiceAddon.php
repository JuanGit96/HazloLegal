<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceAddons\Pages;

use App\Filament\Resources\ServiceAddons\ServiceAddonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceAddon extends EditRecord
{
    protected static string $resource = ServiceAddonResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
