<?php

declare(strict_types=1);

namespace App\Domain\Seo\Services;

use App\Domain\Catalog\Data\Quote;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostCategory;
use App\Domain\Seo\Data\SeoMeta;
use Illuminate\Support\Collection;

/** Arma los metadatos de cada tipo de página. Las vistas sólo los pintan. */
final class SeoManager
{
    public function __construct(private readonly SchemaGenerator $schema) {}

    public function home(Collection $documents): SeoMeta
    {
        $meta = new SeoMeta(
            title: config('hazlolegal.seo.default_title'),
            description: config('hazlolegal.seo.default_description'),
            canonical: url('/'),
        );

        return $meta
            ->withSchema($this->schema->organization())
            ->withSchema($this->schema->website())
            ->withSchema($this->schema->itemList($documents));
    }

    public function catalog(Collection $documents): SeoMeta
    {
        $meta = new SeoMeta(
            title: 'Catálogo de trámites legales en Colombia | Precios y tiempos | Hazlo Legal',
            description: 'Tutelas, derechos de petición, contratos de arrendamiento, NDA, promesas de compraventa y más. Precio fijo publicado y entrega desde 4 horas.',
            canonical: route('documents.index'),
            breadcrumbs: [
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Catálogo', 'url' => route('documents.index')],
            ],
        );

        return $this->attachBreadcrumbs($meta)
            ->withSchema($this->schema->itemList($documents));
    }

    public function document(LegalDocument $document, Quote $quote): SeoMeta
    {
        $meta = new SeoMeta(
            title: $document->metaTitle(),
            description: $document->metaDescription(),
            canonical: $document->url(),
            image: $document->og_image_path ? url($document->og_image_path) : null,
            breadcrumbs: [
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Catálogo', 'url' => route('documents.index')],
                ['name' => $document->short_name ?: $document->name, 'url' => $document->url()],
            ],
        );

        $meta = $this->attachBreadcrumbs($meta)
            ->withSchema($this->schema->service($document, $quote));

        if ($faq = $this->schema->faqPage($document->resolvedFaqs())) {
            $meta->withSchema($faq);
        }

        return $meta;
    }

    public function blogIndex(?PostCategory $category = null): SeoMeta
    {
        $title = $category
            ? ($category->seo_title ?: "{$category->name} | Guías legales | Hazlo Legal")
            : 'Guías legales gratis para Colombia | Blog de Hazlo Legal';

        $description = $category
            ? ($category->seo_description ?: "Guías y formatos sobre {$category->name} explicados por abogados colombianos.")
            : 'Explicaciones claras y formatos gratis sobre tutelas, EPS, Datacrédito, arriendos, liquidaciones y contratos. Escrito y revisado por abogados titulados en Colombia.';

        $crumbs = [
            ['name' => 'Inicio', 'url' => url('/')],
            ['name' => 'Blog', 'url' => route('blog.index')],
        ];

        if ($category) {
            $crumbs[] = ['name' => $category->name, 'url' => $category->url()];
        }

        return $this->attachBreadcrumbs(new SeoMeta(
            title: $title,
            description: $description,
            canonical: $category ? $category->url() : route('blog.index'),
            breadcrumbs: $crumbs,
        ));
    }

    public function post(Post $post): SeoMeta
    {
        $meta = new SeoMeta(
            title: $post->metaTitle(),
            description: $post->metaDescription(),
            canonical: $post->url(),
            image: $post->cover_path ? url($post->cover_path) : null,
            type: 'article',
            breadcrumbs: array_values(array_filter([
                ['name' => 'Inicio', 'url' => url('/')],
                ['name' => 'Blog', 'url' => route('blog.index')],
                $post->category ? ['name' => $post->category->name, 'url' => $post->category->url()] : null,
                ['name' => $post->title, 'url' => $post->url()],
            ])),
            publishedTime: $post->published_at?->toIso8601String(),
            modifiedTime: ($post->reviewed_at ?? $post->updated_at)?->toIso8601String(),
            author: $post->author?->name,
        );

        return $this->attachBreadcrumbs($meta)->withSchema($this->schema->article($post));
    }

    /** Páginas estáticas: garantía, nosotros, términos, etc. */
    public function page(string $title, string $description, string $canonical, array $crumbs = [], bool $noindex = false): SeoMeta
    {
        return $this->attachBreadcrumbs(new SeoMeta(
            title: $title,
            description: $description,
            canonical: $canonical,
            noindex: $noindex,
            breadcrumbs: $crumbs,
        ));
    }

    public function withFaqSchema(SeoMeta $meta, array $faqs): SeoMeta
    {
        if ($schema = $this->schema->faqPage($faqs)) {
            $meta->withSchema($schema);
        }

        return $meta;
    }

    private function attachBreadcrumbs(SeoMeta $meta): SeoMeta
    {
        if ($schema = $this->schema->breadcrumbs($meta->breadcrumbs)) {
            $meta->withSchema($schema);
        }

        return $meta;
    }
}
