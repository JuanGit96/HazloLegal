<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalCases\Pages;

use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Models\LegalCase;
use App\Filament\Resources\LegalCases\LegalCaseResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLegalCases extends ListRecords
{
    protected static string $resource = LegalCaseResource::class;

    public function getTabs(): array
    {
        return [
            'esperando' => Tab::make('Esperando abogado')
                ->modifyQueryUsing(fn (Builder $query) => $query->open())
                ->badge(LegalCase::query()->open()->count())
                ->badgeColor('warning'),

            'asignados' => Tab::make('Con abogado')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CaseStatus::Assigned)),

            'sin_respuesta' => Tab::make('Sin respuesta')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNull('assigned_lawyer_id')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now()))
                ->badgeColor('danger'),

            'todos' => Tab::make('Todos'),
        ];
    }
}
