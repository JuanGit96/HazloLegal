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
            ->push(...$this->areaUrls())
            ->push(...$this->toolUrls())
            ->push(...$this->lawyerUrls())
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
            // Las herramientas gratuitas son la puerta de entrada del embudo:
            // dejarlas fuera del sitemap sería dejar fuera el imán.
            $this->url(route('tools.index'), '0.9', 'weekly'),
            $this->url(route('lawyers.index'), '0.8', 'weekly'),
            $this->url(route('lawyers.join'), '0.6', 'monthly'),
            $this->url(route('pages.terms'), '0.3', 'yearly'),
            $this->url(route('pages.privacy'), '0.3', 'yearly'),
            $this->url(route('pages.signature'), '0.5', 'yearly'),
            $this->url(route('pages.contact'), '0.6', 'monthly'),
            $this->url(route('pages.data-deletion'), '0.3', 'yearly'),
        ];
    }

    /** Una landing por área, pero solo las que tienen trámites publicados. */
    private function areaUrls(): array
    {
        return LegalDocument::query()
            ->active()
            ->get()
            ->map(fn (LegalDocument $doc) => $doc->practice_area)
            ->unique()
            ->map(fn ($area) => $this->url(route('documents.area', $area->value), '0.7', 'weekly'))
            ->values()
            ->all();
    }

    private function toolUrls(): array
    {
        return \App\Domain\Tools\Models\Calculator::query()
            ->where('is_active', true)
            ->get()
            ->map(fn ($calculator) => $this->url($calculator->url(), '0.8', 'monthly'))
            ->all();
    }

    /** Solo los perfiles publicados: uno sin publicar daría 404. */
    private function lawyerUrls(): array
    {
        return \App\Domain\Lawyers\Models\Lawyer::query()
            ->where('is_published', true)
            ->get()
            ->map(fn ($lawyer) => $this->url(route('lawyers.show', $lawyer->slug), '0.6', 'monthly'))
            ->all();
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
