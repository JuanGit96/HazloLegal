<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Domain\Content\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public')
                ->label('Ver en el blog')
                ->icon(Heroicon::OutlinedLink)
                ->color('gray')
                ->url(fn () => $this->record->url(), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->status === PostStatus::Published),

            DeleteAction::make(),
        ];
    }
}
