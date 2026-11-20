@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="doc-hero">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="doc-hero__grid">
            <div>
                <div class="hero__kicker">
                    @if ($lawyer->is_featured)<span class="pill pill--action">Destacado</span>@endif
                    <span class="pill pill--brand">📍 {{ $lawyer->city }}</span>
                    @if ($lawyer->offers_virtual)<span class="pill pill--success">Atiende virtual</span>@endif
                </div>

                <h1>{{ $lawyer->displayName() }}</h1>
                <p class="doc-hero__lead">{{ $lawyer->headline }}</p>

                @if ($lawyer->long_bio || $lawyer->bio)
                    <div class="prose" style="margin-top: var(--s6);">
                        {!! nl2br(e($lawyer->long_bio ?: $lawyer->bio)) !!}
                    </div>
                @endif

                @if ($lawyer->practiceAreaEnums())
                    <h2 style="font-size: var(--t-lg); margin-top: var(--s10);">Áreas de práctica</h2>
                    <div class="lawyer-tile__meta">
                        @foreach ($lawyer->practiceAreaEnums() as $area)
                            <a href="{{ route('lawyers.index') }}?area={{ $area->value }}" class="pill pill--outline" style="text-decoration: none;">
                                {{ $area->emoji() }} {{ $area->getLabel() }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($lawyer->specialties)
                    <h2 style="font-size: var(--t-lg); margin-top: var(--s8);">Servicios</h2>
                    <ul class="plan__features" style="margin-bottom: 0;">
                        @foreach ($lawyer->specialties as $specialty)<li>{{ $specialty }}</li>@endforeach
                    </ul>
                @endif
            </div>

            <aside class="doc-hero__aside">
                <div class="buybox">
                    <div class="lawyer-tile__avatar" style="width: 84px; height: 84px; margin-bottom: var(--s5);" aria-hidden="true">
                        {{ $lawyer->initials() }}
                    </div>

                    <ul class="buybox__rows">
                        @if ($lawyer->years_experience)
                            <li><span aria-hidden="true">📅</span> <span><b>{{ $lawyer->years_experience }} años</b> de ejercicio</span></li>
                        @endif
                        @if ($lawyer->cases_resolved)
                            <li><span aria-hidden="true">✅</span> <span><b>{{ number_format($lawyer->cases_resolved, 0, ',', '.') }}</b> casos resueltos</span></li>
                        @endif
                        @if ($lawyer->university)
                            <li><span aria-hidden="true">🎓</span> <span>{{ $lawyer->university }}</span></li>
                        @endif
                        @if ($lawyer->offers_free_first_call)
                            <li><span aria-hidden="true">🎁</span> <span><b>Primera llamada sin costo</b></span></li>
                        @endif
                    </ul>

                    @if ($lawyer->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $lawyer->whatsapp) }}"
                           class="btn btn--wa btn--block" target="_blank" rel="noopener">
                            Escribir por WhatsApp
                        </a>
                    @endif

                    @if ($lawyer->email)
                        <a href="mailto:{{ $lawyer->email }}" class="btn btn--outline btn--block" style="margin-top: var(--s3);">
                            Enviar correo
                        </a>
                    @endif

                    <div class="buybox__divider"></div>

                    <p class="text-xs text-muted">
                        {{ $lawyer->displayName() }} es un profesional independiente inscrito en nuestra red.
                        La relación profesional que establezcas es directa con él.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>

@if ($posts->isNotEmpty())
<section class="section section--sunken section--tight">
    <div class="container">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>Artículos de {{ \Illuminate\Support\Str::before($lawyer->name, ' ') }}</h2>
        </div>
        <div class="grid grid--3">
            @foreach ($posts as $post)
                <article class="post-card">
                    <div class="post-card__cover" aria-hidden="true">{{ $post->category?->emoji ?: '📘' }}</div>
                    <div class="post-card__body">
                        <h3><a href="{{ $post->url() }}">{{ $post->title }}</a></h3>
                        <div class="post-card__meta"><span>{{ $post->reading_minutes }} min</span></div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($related->isNotEmpty())
<section class="section section--white section--tight">
    <div class="container">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>Otros abogados en {{ $lawyer->city }}</h2>
        </div>
        <div class="grid grid--3">
            @foreach ($related as $other)
                <article class="lawyer-tile">
                    <div class="lawyer-tile__avatar" aria-hidden="true">{{ $other->initials() }}</div>
                    <div class="lawyer-tile__body">
                        <h3 class="lawyer-tile__name" style="font-size: var(--t-base);">
                            <a href="{{ $other->profileUrl() }}" style="color: inherit; text-decoration: none;">{{ $other->displayName() }}</a>
                        </h3>
                        <p class="lawyer-tile__role" style="font-size: var(--t-xs);">{{ $other->headline }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
