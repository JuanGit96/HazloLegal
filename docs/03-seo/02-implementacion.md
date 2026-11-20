# SEO: dónde está cada pieza en el código

> **Regla:** nunca escribir `<title>` ni JSON-LD a mano en una plantilla.
> Cada página pública pasa una `SeoMeta` a la vista, construida por
> `SeoManager`.

## El flujo

```
Controlador
   └─► SeoManager::documento()/home()/post()/page()
          ├─► SchemaGenerator  (arma los JSON-LD)
          └─► SeoMeta          (title, description, canonical, og, migas, esquemas)
                 └─► resources/views/partials/seo.blade.php
```

## `SeoManager` — un método por tipo de página

| Método | Para qué | Qué esquemas añade |
|---|---|---|
| `home(Collection $documents)` | La home | `LegalService`, `WebSite`+`SearchAction`, `ItemList` |
| `catalog(Collection $documents)` | `/documentos` | `BreadcrumbList`, `ItemList` |
| `document(LegalDocument $d, Quote $q)` | Ficha del trámite | `Service`+`Offer`, `BreadcrumbList`, `FAQPage` |
| `blogIndex(?PostCategory $c)` | `/blog` | `BreadcrumbList` |
| `post(Post $p)` | Un artículo | `BlogPosting` con autor y `reviewedBy` |
| `page(...)` | Páginas sueltas | `BreadcrumbList`, con `noindex` opcional |
| `withFaqSchema(SeoMeta, array)` | Añade FAQ a cualquiera | `FAQPage` |

## `SchemaGenerator` — los JSON-LD

| Método | Tipo schema.org | Dónde sale |
|---|---|---|
| `organization()` | `LegalService` | Home; el resto lo referencia por `@id` |
| `website()` | `WebSite` + `SearchAction` | Home. Habilita la caja de búsqueda en Google |
| `service()` | `Service` + `Offer` | Ficha del trámite. Precio, moneda COP y plazo de entrega |
| `faqPage()` | `FAQPage` | Ficha y home. Puede ganar espacio extra en el resultado |
| `breadcrumbs()` | `BreadcrumbList` | Todas las interiores |
| `article()` | `BlogPosting` | Artículos, con `author` y `reviewedBy` |
| `person()` | `Person` | Abogados. La señal E-E-A-T |
| `itemList()` | `ItemList` | Listados de trámites |
| `howTo()` | `HowTo` | Guías de pasos |

El `Offer` incluye `priceValidUntil` (6 meses) y `deliveryLeadTime` en horas.
Un precio de `0` es válido y se declara: es lo que le dice a Google que el
trámite es gratuito.

## `SitemapBuilder`

Genera `/sitemap.xml` con prioridades y frecuencias pensadas:

| Página | Prioridad | Frecuencia |
|---|---|---|
| Home | 1.0 | diaria |
| `/documentos` | 0.9 | semanal |
| Cada trámite | según destaque | semanal |
| `/blog` | 0.8 | diaria |
| Artículos | — | con `lastmod` |
| Garantía, caso complejo | 0.8 | mensual |
| Legales | 0.3 | anual |

> **Si añades un tipo de página pública, añádela también a `SitemapBuilder`.**
> Es la regla de `CLAUDE.md` que más fácil se olvida.
>
> Hoy faltan en el sitemap: `/gratis`, las calculadoras, `/abogados` y los
> perfiles de abogado. Está anotado en [Pendientes](../05-operacion/03-pendientes.md).

## `partials/seo.blade.php`

Pinta en el `<head>`:

- `<title>` y `<meta name="description">`
- `<link rel="canonical">`
- Open Graph y Twitter Card, con imagen
- `robots` con `max-image-preview:large` (habilita miniaturas grandes)
- `noindex, nofollow` en las páginas del embudo
- Todos los `<script type="application/ld+json">`

## Campos SEO editables

| Modelo | Campos |
|---|---|
| `LegalDocument` | `seo_title`, `seo_description`, `focus_keyword`, `seo_keywords`, `og_image_path`, `body` |
| `Post` | `seo_title`, `seo_description`, `author_lawyer_id` |
| `Calculator` | `seo_title`, `seo_description`, `focus_keyword`, `body`, `faqs` |
| `Lawyer` | `seo_title`, `seo_description`, `long_bio` |

En el panel, la tabla de trámites tiene una columna «SEO» que se pone en ámbar
si al trámite le falta contenido largo o título SEO. Un trámite sin `body` no
posiciona.

## Qué cubren los tests

`tests/Feature/PublicSeoTest.php`:

- La home trae `LegalService`, `WebSite`, `FAQPage`, `ItemList`, canónica, og
  y `max-image-preview:large`.
- La ficha trae `Service`, `Offer`, `priceCurrency: COP`, `BreadcrumbList` y
  `FAQPage`.
- Un artículo declara `BlogPosting`, `reviewedBy` y `Person` con nombre.
- El sitemap lista catálogo y blog; `robots.txt` bloquea el embudo.
- Las páginas del embudo llevan `noindex`.
