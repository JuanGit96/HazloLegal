<?php

declare(strict_types=1);

namespace App\Domain\Seo\Data;

/** Metadatos de una página: los consume el layout y nada más. */
final class SeoMeta
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $canonical = null,
        public ?string $image = null,
        public string $type = 'website',
        public bool $noindex = false,
        /** @var array<int, array{name: string, url: string|null}> */
        public array $breadcrumbs = [],
        /** @var array<int, array<string, mixed>> Bloques JSON-LD ya construidos */
        public array $schemas = [],
        public ?string $publishedTime = null,
        public ?string $modifiedTime = null,
        public ?string $author = null,
    ) {}

    public function withSchema(array $schema): self
    {
        $this->schemas[] = $schema;

        return $this;
    }

    public function withBreadcrumbs(array $crumbs): self
    {
        $this->breadcrumbs = $crumbs;

        return $this;
    }
}
