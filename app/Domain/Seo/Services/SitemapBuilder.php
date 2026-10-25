<?php

declare(strict_types=1);

namespace App\Domain\Seo\Services;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostCategory;
use Illuminate\Support\Collection;

/**
 * Sitemap generado en caliente. Con un catálogo de decenas de URLs no vale
 * la pena un archivo en disco; sí vale que nunca quede desactualizado.
 */
final class SitemapBuilder
{
    public function urls(): Collection
    {
        return collect()
            ->push(...$this->staticUrls())
            ->push(...$this->documentUrls())
            ->push(...$this->blogUrls());
    }

    private function staticUrls(): array
    {
        return [
            $this->url(url('/'), '1.0', 'daily'),
            $this->url(route('documents.index'), '0.9', 'weekly'),
            $this->url(route('pages.guarantee'), '0.8', 'monthly'),
            $this->url(route('pages.how-it-works'), '0.7', 'monthly'),
            $this->url(route('pages.about'), '0.6', 'monthly'),
            $this->url(route('cases.create'), '0.8', 'monthly'),
            $this->url(route('blog.index'), '0.8', 'daily'),
            $this->url(route('pages.terms'), '0.3', 'yearly'),
            $this->url(route('pages.privacy'), '0.3', 'yearly'),
            $this->url(route('pages.signature'), '0.5', 'yearly'),
        ];
    }

    private function documentUrls(): array
    {
        return LegalDocument::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (LegalDocument $doc) => $this->url(
                $doc->url(),
                $doc->is_featured ? '0.9' : '0.8',
                'weekly',
                $doc->updated_at?->toAtomString(),
            ))
            ->all();
    }

    private function blogUrls(): array
    {
        $categories = PostCategory::query()->get()
            ->map(fn (PostCategory $cat) => $this->url($cat->url(), '0.6', 'weekly'))
            ->all();

        $posts = Post::query()->published()->recent()->get()
            ->map(fn (Post $post) => $this->url(
                $post->url(),
                $post->is_featured ? '0.8' : '0.7',
                'monthly',
                ($post->reviewed_at ?? $post->updated_at)?->toAtomString(),
            ))
            ->all();

        return [...$categories, ...$posts];
    }

    private function url(string $loc, string $priority, string $changefreq, ?string $lastmod = null): array
    {
        return [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $lastmod ?? now()->toAtomString(),
        ];
    }
}
