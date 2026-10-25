@php
    /** @var \App\Domain\Seo\Data\SeoMeta|null $meta */
    $seoConfig = config('hazlolegal.seo');
    $title = $meta->title ?? $seoConfig['default_title'];
    $description = $meta->description ?? $seoConfig['default_description'];
    $canonical = $meta->canonical ?? url()->current();
    $image = $meta->image ?? url($seoConfig['og_image']);
    $type = $meta->type ?? 'website';
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

@if ($meta?->noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
@endif

{{-- El sitio es monolingüe para Colombia: se declara explícito para evitar
     que Google lo sirva a otros mercados hispanohablantes. --}}
<link rel="alternate" hreflang="es-co" href="{{ $canonical }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
<meta name="geo.region" content="CO">
<meta name="geo.placename" content="{{ config('hazlolegal.contact.city') }}">

<meta property="og:site_name" content="{{ config('hazlolegal.brand.name') }}">
<meta property="og:locale" content="{{ $seoConfig['locale'] }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

@if ($type === 'article')
    @if ($meta?->publishedTime)<meta property="article:published_time" content="{{ $meta->publishedTime }}">@endif
    @if ($meta?->modifiedTime)<meta property="article:modified_time" content="{{ $meta->modifiedTime }}">@endif
    @if ($meta?->author)<meta property="article:author" content="{{ $meta->author }}">@endif
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="{{ $seoConfig['twitter_handle'] }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

@if ($seoConfig['google_site_verification'])
    <meta name="google-site-verification" content="{{ $seoConfig['google_site_verification'] }}">
@endif

@foreach ($meta?->schemas ?? [] as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
