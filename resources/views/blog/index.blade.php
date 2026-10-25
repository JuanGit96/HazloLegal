@extends('layouts.site')

@section('content')
<section class="section section--white section--tight">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Guías gratuitas</span>
            <h1>{{ $category ? $category->name : 'Entiende tu trámite antes de pagar por él' }}</h1>
            <p>
                {{ $category?->description
                    ?: 'Explicamos cada trámite paso a paso, con las normas vigentes y los plazos reales. Escrito y revisado por abogados colombianos titulados.' }}
            </p>
        </div>

        <div class="area-chips">
            <a href="{{ route('blog.index') }}" class="area-chip" @if (! $category) aria-current="page" @endif>Todas</a>
            @foreach ($categories as $cat)
                <a href="{{ $cat->url() }}" class="area-chip" @if ($category?->id === $cat->id) aria-current="page" @endif>
                    <span aria-hidden="true">{{ $cat->emoji }}</span> {{ $cat->name }}
                    <span class="text-xs text-muted">({{ $cat->posts_count }})</span>
                </a>
            @endforeach
        </div>

        @if ($featured->isNotEmpty())
            <div class="grid grid--2" style="margin-bottom: var(--s12);">
                @foreach ($featured as $post)
                    <article class="card card--hover card--pad-lg" style="border-color: var(--brand);">
                        <span class="pill pill--action" style="align-self: flex-start;">⭐ Destacada</span>
                        <h2 style="font-size: var(--t-xl); margin-top: var(--s4);">
                            <a href="{{ $post->url() }}" style="color: inherit; text-decoration: none;">{{ $post->title }}</a>
                        </h2>
                        <p class="text-muted">{{ $post->excerpt }}</p>
                        <div class="post-card__meta mt-auto">
                            @if ($post->category)<span class="pill pill--brand">{{ $post->category->name }}</span>@endif
                            <span>{{ $post->reading_minutes }} min</span>
                            @if ($post->author)
                                <span aria-hidden="true">·</span>
                                <span>Revisado por {{ $post->author->name }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($posts->isEmpty())
            <div class="alert alert--info" style="max-width: 620px; margin: 0 auto;">
                <span class="alert__icon" aria-hidden="true">📝</span>
                <div>Todavía no hay artículos publicados en esta categoría.</div>
            </div>
        @else
            <div class="grid grid--3">
                @foreach ($posts as $post)
                    <article class="post-card">
                        <div class="post-card__cover" aria-hidden="true">{{ $post->category?->emoji ?: '📘' }}</div>
                        <div class="post-card__body">
                            @if ($post->category)
                                <a href="{{ $post->category->url() }}" class="pill pill--brand"
                                   style="align-self: flex-start; margin-bottom: var(--s3); text-decoration: none;">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                            <h2 style="font-size: var(--t-base); margin-bottom: var(--s2);">
                                <a href="{{ $post->url() }}" style="color: inherit; text-decoration: none;">{{ $post->title }}</a>
                            </h2>
                            <p>{{ \Illuminate\Support\Str::limit($post->excerpt, 120) }}</p>
                            <div class="post-card__meta">
                                <span>{{ $post->reading_minutes }} min de lectura</span>
                                @if ($post->published_at)
                                    <span aria-hidden="true">·</span>
                                    <time datetime="{{ $post->published_at->toDateString() }}">
                                        {{ $post->published_at->translatedFormat('j M Y') }}
                                    </time>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{ $posts->links() }}
        @endif
    </div>
</section>

<x-cta-band
    title="Si después de leer prefieres que lo haga un abogado"
    text="Tenemos el trámite con precio fijo, revisión profesional y garantía de corrección. Y si tu caso es más complicado, un abogado lo evalúa sin costo."
    primary-label="Ver trámites y precios"
    :primary-href="route('documents.index')"
    secondary-label="Tengo un caso complejo"
    :secondary-href="route('cases.create')" />
@endsection
