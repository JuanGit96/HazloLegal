<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditLegalDocument extends EditRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public')
                ->label('Ver en el sitio')
                ->icon(Heroicon::OutlinedLink)
                ->color('gray')
                ->url(fn () => $this->record->url(), shouldOpenInNewTab: true),
        ];
    }
}
