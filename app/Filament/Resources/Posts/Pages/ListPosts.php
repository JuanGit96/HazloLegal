<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Domain\Content\Enums\PostStatus;
use App\Domain\Content\Models\Post;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo artículo')];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),

            'publicados' => Tab::make('Publicados')
                ->modifyQueryUsing(fn (Builder $query) => $query->published()),

            'borradores' => Tab::make('Borradores')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PostStatus::Draft))
                ->badge(Post::query()->where('status', PostStatus::Draft)->count()),

            'sin_seo' => Tab::make('Sin SEO completo')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where(fn (Builder $q) => $q->whereNull('seo_title')->orWhereNull('focus_keyword')))
                ->badgeColor('warning')
                ->badge(Post::query()
                    ->where(fn (Builder $q) => $q->whereNull('seo_title')->orWhereNull('focus_keyword'))
                    ->count()),
        ];
    }
}
