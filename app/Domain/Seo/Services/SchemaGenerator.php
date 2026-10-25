<?php

declare(strict_types=1);

namespace App\Domain\Seo\Services;

use App\Domain\Catalog\Data\Quote;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Models\Post;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Construye los bloques JSON-LD del sitio.
 *
 * El contenido legal es YMYL ("Your Money or Your Life"): Google exige
 * autoría acreditada para posicionarlo. Cada artículo declara autor y revisor
 * con su especialidad y formación, y la empresa se declara como LegalService
 * con dirección física real.
 *
 * Lo que NO se publica es el número de tarjeta profesional: se guarda solo
 * para control interno, porque el sitio no promete revisión individual de
 * cada documento.
 */
final class SchemaGenerator
{
    public function organization(): array
    {
        $contact = config('hazlolegal.contact');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'LegalService',
            '@id' => url('/#organization'),
            'name' => config('hazlolegal.brand.name'),
            'legalName' => config('hazlolegal.brand.legal_name'),
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url('/img/logo-hazlolegal.svg'),
            ],
            'image' => url(config('hazlolegal.seo.og_image')),
            'description' => config('hazlolegal.seo.default_description'),
            'telephone' => $contact['phone'],
            'email' => $contact['email'],
            'priceRange' => '$$',
            'currenciesAccepted' => 'COP',
            'paymentAccepted' => 'Nequi, Daviplata, Transferencia bancaria, PSE, Tarjeta',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $contact['address'],
                'addressLocality' => $contact['city'],
                'addressRegion' => $contact['region'],
                'postalCode' => $contact['postal_code'],
                'addressCountry' => $contact['country'],
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $contact['latitude'],
                'longitude' => $contact['longitude'],
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    'opens' => '07:00',
                    'closes' => '21:00',
                ],
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Colombia',
            ],
            'knowsLanguage' => 'es-CO',
            'sameAs' => array_values(array_filter(config('hazlolegal.social'))),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => config('hazlolegal.trust.satisfaction'),
                'reviewCount' => config('hazlolegal.trust.documents_delivered'),
                'bestRating' => '5',
            ],
        ];
    }

    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'url' => url('/'),
            'name' => config('hazlolegal.brand.name'),
            'inLanguage' => 'es-CO',
            'publisher' => ['@id' => url('/#organization')],
            // Habilita la caja de búsqueda en los resultados de Google.
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/documentos?q={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function service(LegalDocument $document, ?Quote $quote = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'serviceType' => $document->name,
            'name' => $document->name,
            'description' => Str::limit(strip_tags((string) $document->summary), 300),
            'url' => $document->url(),
            'category' => $document->practice_area->getLabel(),
            'provider' => ['@id' => url('/#organization')],
            'areaServed' => ['@type' => 'Country', 'name' => 'Colombia'],
            'audience' => ['@type' => 'Audience', 'geographicArea' => ['@type' => 'Country', 'name' => 'Colombia']],
        ];

        if ($quote && $quote->amount !== null) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => (string) $quote->amount,
                'priceCurrency' => 'COP',
                'availability' => 'https://schema.org/InStock',
                'url' => $document->url(),
                'priceValidUntil' => now()->addMonths(6)->toDateString(),
                'deliveryLeadTime' => [
                    '@type' => 'QuantitativeValue',
                    'value' => $quote->deliveryHours,
                    'unitCode' => 'HUR',
                ],
            ];
        }

        return $schema;
    }

    /** @param array<int, array{question: string, answer: string}> $faqs */
    public function faqPage(array $faqs): ?array
    {
        if ($faqs === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($faq['answer']),
                ],
            ])->values()->all(),
        ];
    }

    /** @param array<int, array{name: string, url: string|null}> $crumbs */
    public function breadcrumbs(array $crumbs): ?array
    {
        if (count($crumbs) < 2) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'] ?? null,
            ]))->all(),
        ];
    }

    public function article(Post $post): array
    {
        $author = $post->author;

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => Str::limit($post->title, 110),
            'description' => $post->metaDescription(),
            'url' => $post->url(),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $post->url()],
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => ($post->reviewed_at ?? $post->updated_at)?->toIso8601String(),
            'inLanguage' => 'es-CO',
            'wordCount' => str_word_count(strip_tags((string) $post->body)),
            'articleSection' => $post->category?->name,
            'keywords' => implode(', ', $post->seo_keywords ?? []),
            'image' => $post->cover_path ? url($post->cover_path) : url(config('hazlolegal.seo.og_image')),
            'publisher' => ['@id' => url('/#organization')],
            'author' => $author ? $this->person($author) : ['@id' => url('/#organization')],
            // Autoría acreditada: sigue siendo la señal E-E-A-T más fuerte en
            // contenido legal, ahora sin publicar la tarjeta profesional.
            'reviewedBy' => $author ? $this->person($author) : null,
            'isAccessibleForFree' => true,
        ]);
    }

    public function person(Lawyer $lawyer): array
    {
        return array_filter([
            '@type' => 'Person',
            'name' => $lawyer->name,
            'jobTitle' => $lawyer->headline ?: 'Abogado',
            'description' => $lawyer->bio ? Str::limit(strip_tags($lawyer->bio), 250) : null,
            'image' => $lawyer->photo_path ? url($lawyer->photo_path) : null,
            'worksFor' => ['@id' => url('/#organization')],
            'knowsAbout' => collect($lawyer->practiceAreaEnums())->map(fn ($a) => $a->getLabel())->all(),
            // Sin `hasCredential`: la tarjeta profesional no se publica.
            'alumniOf' => $lawyer->university ? ['@type' => 'CollegeOrUniversity', 'name' => $lawyer->university] : null,
        ]);
    }

    /** Lista de servicios del catálogo: ayuda a ganar enlaces de sitio. */
    public function itemList(Collection $documents): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Catálogo de trámites y documentos legales',
            'numberOfItems' => $documents->count(),
            'itemListElement' => $documents->values()->map(fn (LegalDocument $doc, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $doc->name,
                'url' => $doc->url(),
            ])->all(),
        ];
    }

    /** @param array<int, array{name: string, text: string}> $steps */
    public function howTo(string $name, string $description, array $steps, ?int $minutes = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $name,
            'description' => $description,
            'totalTime' => $minutes ? "PT{$minutes}M" : null,
            'step' => collect($steps)->map(fn (array $step, int $i) => [
                '@type' => 'HowToStep',
                'position' => $i + 1,
                'name' => $step['name'],
                'text' => $step['text'],
            ])->all(),
        ]);
    }
}
