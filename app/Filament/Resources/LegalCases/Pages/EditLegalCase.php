<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalCases\Pages;

use App\Domain\LegalCases\Services\CaseDispatchService;
use App\Filament\Resources\LegalCases\LegalCaseResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditLegalCase extends EditRecord
{
    protected static string $resource = LegalCaseResource::class;

    public function getTitle(): string
    {
        return 'Caso '.$this->record->reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend')
                ->label('Reenviar a abogados')
                ->icon(Heroicon::OutlinedMegaphone)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => ! $this->record->isTaken())
                ->action(function (CaseDispatchService $dispatcher) {
                    $sent = $dispatcher->resend($this->record);

                    Notification::make()
                        ->title($sent > 0 ? "Reenviado a {$sent} abogados" : 'No había invitaciones pendientes')
                        ->status($sent > 0 ? 'success' : 'warning')
                        ->send();
                }),
        ];
    }
}
