@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $faqs = $document->resolvedFaqs();
@endphp

<section class="doc-hero">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="doc-hero__grid">
            <div>
                <div class="hero__kicker">
                    <span class="pill pill--brand">{{ $document->practice_area->emoji() }} {{ $document->practice_area->getLabel() }}</span>
                    @if ($document->isAutomated())
                        <span class="pill pill--success">Entrega automatizada</span>
                    @else
                        <span class="pill pill--warn">Redacción a la medida</span>
                    @endif
                    @if ($document->supports_signature)
                        <span class="pill pill--outline">✍️ Admite firma electrónica</span>
                    @endif
                </div>

                <h1>{{ $document->name }}</h1>
                <p class="doc-hero__lead">{{ $document->summary }}</p>

                @if ($document->legal_basis)
                    <p class="text-sm text-muted">
                        <strong>Fundamento legal:</strong> {{ $document->legal_basis }}
                    </p>
                @endif

                <div class="alert alert--info mt-6">
                    <span class="alert__icon" aria-hidden="true">{{ $document->isAutomated() ? '⚡' : '✍️' }}</span>
                    <div>
                        <strong>{{ $document->automation_phase->publicLabel() }}.</strong>
                        <p style="margin-top: var(--s1);">{{ $document->automation_phase->publicExplanation() }}</p>
                    </div>
                </div>
            </div>

            {{-- Caja de compra: precio, plazo y CTA siempre visibles --}}
            <aside>
                <div class="buybox">
                    <span class="eyebrow">Precio final</span>
                    <div class="buybox__price">
                        {{ $pricing->formatCop($standardQuote->amount) }}
                        <small>COP</small>
                    </div>
                    <p class="text-sm text-muted mb-0">Pago único. Sin cuotas de manejo ni honorarios adicionales.</p>

                    <ul class="buybox__rows">
                        <li><span aria-hidden="true">🕒</span> <span>Entrega en <b>{{ $standardQuote->deliveryLabel() }}</b></span></li>
                        @if ($expressQuote)
                            <li><span aria-hidden="true">⚡</span> <span>Express en <b>menos de 4 horas</b> por {{ $pricing->formatCop($expressQuote->amount) }}</span></li>
                        @endif
                        <li><span aria-hidden="true">⚖️</span> <span>Auditado por <b>abogado titulado</b></span></li>
                        <li><span aria-hidden="true">🛡️</span> <span>Cubierto por el <b>Certificado de Garantía</b></span></li>
                        <li><span aria-hidden="true">📱</span> <span>Llega a tu <b>correo y WhatsApp</b></span></li>
                    </ul>

                    <a href="{{ route('checkout.configure', [$documentPlan->key->value, $document->slug]) }}"
                       class="btn btn--primary btn--block btn--lg">
                        Pedir este documento
                    </a>

                    @if ($expressQuote)
                        <a href="{{ route('checkout.configure', [$documentPlan->key->value, $document->slug]) }}?velocidad=express"
                           class="btn btn--outline btn--block" style="margin-top: var(--s3);">
                            Lo necesito en 4 horas
                        </a>
                    @endif

                    <div class="buybox__divider"></div>

                    @if ($advisoryQuote)
                        <p class="text-xs text-muted">
                            ¿Prefieres hablar con el abogado antes?
                            <a href="{{ route('checkout.configure', [$advisoryPlan->key->value, $document->slug]) }}">
                                <strong>Documento + asesoría de 30 min por {{ $pricing->formatCop($advisoryQuote->amount) }}</strong>
                            </a>
                        </p>
                    @endif

                    <p class="text-xs text-muted" style="margin-top: var(--s3);">
                        ¿Dudas? <a href="{{ $wa->forDocument($document->name) }}" target="_blank" rel="noopener">Pregúntanos por WhatsApp</a>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- ============================================ QUÉ RECIBES / QUÉ NECESITAMOS --}}
<section class="section section--sunken section--tight">
    <div class="container">
        <div class="grid grid--3">
            @if ($document->what_you_get)
            <div class="card card--pad-lg">
                <h3>✅ Qué recibes</h3>
                <ul class="plan__features" style="margin-bottom: 0;">
                    @foreach ($document->what_you_get as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if ($document->required_info)
            <div class="card card--pad-lg">
                <h3>📋 Qué necesitamos de ti</h3>
                <ul class="plan__features" style="margin-bottom: 0;">
                    @foreach ($document->required_info as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if ($document->not_included)
            <div class="card card--pad-lg">
                <h3>⚠️ Qué no incluye</h3>
                <p class="text-sm text-muted">Lo decimos antes de que pagues, no después:</p>
                <ul style="font-size: var(--t-sm); color: var(--text-muted); margin-bottom: 0;">
                    @foreach ($document->not_included as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- ============================================ CASOS DE USO --}}
@if ($document->use_cases)
<section class="section section--white section--tight">
    <div class="container container--narrow">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>¿Es este el documento que necesitas?</h2>
            <p>Lo es si te identificas con alguna de estas situaciones.</p>
        </div>

        <div class="grid grid--2">
            @foreach ($document->use_cases as $case)
                <div class="card card--flat" style="flex-direction: row; gap: var(--s4); align-items: flex-start; background: var(--blue-50); border-color: var(--blue-200);">
                    <span style="font-size: 1.2rem; flex-shrink: 0;" aria-hidden="true">👉</span>
                    <p class="text-sm mb-0">{{ $case }}</p>
                </div>
            @endforeach
        </div>

        <p class="center mt-10 text-sm text-muted">
            ¿Tu caso es distinto o más complicado?
            <a href="{{ route('cases.create') }}"><strong>Cuéntanoslo y un abogado lo evalúa gratis</strong></a>.
        </p>
    </div>
</section>
@endif

{{-- ============================================ CONTENIDO LARGO (SEO) --}}
@if ($document->body)
<section class="section section--sunken">
    <div class="container container--prose">
        <article class="prose">
            {!! $document->body !!}
        </article>

        <div class="card card--pad-lg mt-10" style="border-color: var(--brand); background: var(--blue-50);">
            <h3>Pedir {{ $document->short_name ?: $document->name }}</h3>
            <p class="text-sm text-muted">
                {{ $pricing->formatCop($standardQuote->amount) }} · entrega en {{ $standardQuote->deliveryLabel() }} ·
                garantía de corrección o devolución.
            </p>
            <a href="{{ route('checkout.configure', [$documentPlan->key->value, $document->slug]) }}" class="btn btn--primary">
                Empezar ahora
            </a>
        </div>
    </div>
</section>
@endif

{{-- ============================================ TESTIMONIOS DEL DOCUMENTO --}}
@if ($testimonials->isNotEmpty())
<section class="section section--white section--tight">
    <div class="container container--narrow">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>Quién ya lo resolvió así</h2>
        </div>

        <div class="grid grid--3">
            @foreach ($testimonials as $testimonial)
                <figure class="quote" style="width: auto;">
                    <div class="quote__stars" aria-label="{{ $testimonial->rating }} de 5 estrellas">{{ str_repeat('★', $testimonial->rating) }}</div>
                    @if ($testimonial->outcome)
                        <span class="pill pill--success quote__outcome">✓ {{ $testimonial->outcome }}</span>
                    @endif
                    <blockquote class="quote__text">{{ $testimonial->quote }}</blockquote>
                    <figcaption class="quote__who">
                        <span class="quote__avatar" aria-hidden="true">{{ $testimonial->initials() }}</span>
                        <span>
                            <strong>{{ $testimonial->author_name }}</strong>
                            <span>{{ $testimonial->author_city }}</span>
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================ FAQ --}}
@if ($faqs !== [])
<section class="section section--sunken">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Preguntas frecuentes</span>
            <h2>Sobre {{ \Illuminate\Support\Str::lower($document->short_name ?: $document->name) }}</h2>
        </div>

        <x-faq-list :faqs="$faqs" :open="1" />
    </div>
</section>
@endif

{{-- ============================================ GUÍAS RELACIONADAS --}}
@if ($posts->isNotEmpty())
<section class="section section--white section--tight">
    <div class="container">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>Antes de decidir, lee esto</h2>
            <p>Guías gratuitas sobre este mismo trámite.</p>
        </div>

        <div class="grid grid--3">
            @foreach ($posts as $post)
                <article class="post-card">
                    <div class="post-card__cover" aria-hidden="true">{{ $post->category?->emoji ?: '📘' }}</div>
                    <div class="post-card__body">
                        <h3><a href="{{ $post->url() }}">{{ $post->title }}</a></h3>
                        <p>{{ \Illuminate\Support\Str::limit($post->excerpt, 100) }}</p>
                        <div class="post-card__meta"><span>{{ $post->reading_minutes }} min de lectura</span></div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================ RELACIONADOS --}}
@if ($related->isNotEmpty())
<section class="section section--sunken section--tight">
    <div class="container">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>Otros trámites de {{ \Illuminate\Support\Str::lower($document->practice_area->getLabel()) }}</h2>
        </div>

        <div class="catalog-grid">
            @foreach ($related as $other)
                <x-doc-card :document="$other" :price="$pricing->formatCop($pricing->quote($other, $documentPlan)->amount)" />
            @endforeach
        </div>
    </div>
</section>
@endif

<x-cta-band
    title="Deja de posponer el trámite"
    text="Un abogado titulado lo redacta, lo audita y lo pone en tu correo. Precio fijo, plazo comprometido y garantía por escrito."
    :primary-label="'Pedir ' . \Illuminate\Support\Str::lower($document->short_name ?: $document->name)"
    :primary-href="route('checkout.configure', [$documentPlan->key->value, $document->slug])"
    secondary-label="Ver todos los trámites"
    :secondary-href="route('documents.index')" />
@endsection
