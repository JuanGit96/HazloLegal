<?php

declare(strict_types=1);

namespace App\Domain\Content\Services;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class BlogService
{
    public function paginate(int $perPage = 9, ?PostCategory $category = null): LengthAwarePaginator
    {
        return Post::query()
            ->published()
            ->with(['category', 'author', 'relatedDocument'])
            ->when($category, fn ($q) => $q->where('post_category_id', $category->id))
            ->recent()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 3): Collection
    {
        return Post::query()
            ->published()
            ->with(['category', 'author'])
            ->featured()
            ->recent()
            ->limit($limit)
            ->get();
    }

    public function latest(int $limit = 3): Collection
    {
        return Post::query()->published()->with(['category', 'author'])->recent()->limit($limit)->get();
    }

    public function findBySlug(string $slug): ?Post
    {
        return Post::query()
            ->published()
            ->with(['category', 'author', 'relatedDocument'])
            ->where('slug', $slug)
            ->first();
    }

    public function categories(): Collection
    {
        return PostCategory::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();
    }

    /** Artículos del mismo cluster temático: refuerza el enlazado interno. */
    public function related(Post $post, int $limit = 3): Collection
    {
        return Post::query()
            ->published()
            ->with('category')
            ->whereKeyNot($post->getKey())
            ->when($post->post_category_id, fn ($q) => $q->where('post_category_id', $post->post_category_id))
            ->recent()
            ->limit($limit)
            ->get();
    }

    public function registerView(Post $post): void
    {
        $post->incrementQuietly('views_count');
    }
}
