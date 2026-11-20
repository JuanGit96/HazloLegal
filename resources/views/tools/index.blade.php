@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="section section--dark" style="padding-block: var(--s16) var(--s20);">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head" style="margin-bottom: var(--s10);">
            <span class="pill pill--dark">✓ 100 % gratis · sin registro</span>
            <h1 style="margin-top: var(--s5);">
                Herramientas legales <span class="hl">gratis</span><br>para colombianos
            </h1>
            <p>
                Genera documentos y haz tus cuentas con herramientas basadas en la ley
                colombiana vigente. Resultado inmediato, sin pagar nada.
            </p>
        </div>

        <div class="stat-row">
            <div class="stat">
                <span class="stat__value" style="color: #fff;">{{ $totalTools }}</span>
                <span class="stat__label" style="color: var(--blue-200);">herramientas</span>
            </div>
            <div class="stat">
                <span class="stat__value" style="color: var(--amber-500);">Gratis</span>
                <span class="stat__label" style="color: var(--blue-200);">no tienes que pagar</span>
            </div>
            <div class="stat">
                <span class="stat__value" style="color: #fff;">&lt; 3 min</span>
                <span class="stat__label" style="color: var(--blue-200);">para tener tu resultado</span>
            </div>
        </div>
    </div>
</section>

{{-- ============================ DOCUMENTOS GRATIS ============================ --}}
@if ($freeDocuments->isNotEmpty())
<section class="section section--white">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Generadores de documentos</span>
            <h2>Documentos listos para radicar, sin costo</h2>
            <p>Respondes unas preguntas y te llevas el documento. Nuestra revisión automática corrige la redacción antes de generarlo.</p>
        </div>

        <div class="grid grid--3 reveal">
            @foreach ($freeDocuments as $document)
                <a href="{{ $document->url() }}" class="tool-card tool-card--light">
                    <span class="tool-card__emoji" aria-hidden="true">{{ $document->emojiOrArea() }}</span>
                    <span class="tool-card__tag">Gratis</span>
                    <h3>{{ $document->name }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($document->tagline ?: $document->summary, 110) }}</p>
                    <span class="tool-card__cta" style="color: var(--brand);">Usar herramienta →</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================ CALCULADORAS ============================ --}}
@if ($calculators->isNotEmpty())
<section class="section section--sunken">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Calculadoras</span>
            <h2>Haz tus cuentas antes de reclamar</h2>
            <p>Con las fórmulas del Código Sustantivo del Trabajo y los valores vigentes de este año.</p>
        </div>

        <div class="grid grid--2 reveal">
            @foreach ($calculators as $calculator)
                <a href="{{ $calculator->url() }}" class="tool-card">
                    <span class="tool-card__emoji" aria-hidden="true">{{ $calculator->emoji }}</span>
                    <span class="tool-card__tag">Laboral</span>
                    <h3>{{ $calculator->name }}</h3>
                    <p>{{ $calculator->intro }}</p>
                    <span class="tool-card__cta">Calcular gratis →</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================ POR QUÉ ES GRATIS ============================ --}}
{{-- La pregunta es real y conviene responderla, pero sin explicar el embudo:
     lo que tranquiliza es saber qué pasa con sus datos, no cómo ganamos. --}}
<section class="section section--white">
    <div class="container container--narrow">
        <div class="tile" style="border-color: var(--brand);">
            <div class="tile__icon" aria-hidden="true">🤔</div>
            <h2 style="font-size: var(--t-xl);">¿Dónde está la trampa?</h2>
            <p class="text-muted" style="font-size: var(--t-base);">
                No hay. El documento sale completo, sin marca de agua y sin límite de descargas.
                Si te resuelve el problema, ya está: para eso lo hicimos.
            </p>
            <p class="text-muted" style="font-size: var(--t-base); margin-top: var(--s4);">
                <strong>Y no vendemos tus datos.</strong> Te pedimos el correo para enviarte el
                documento. Que un abogado te contacte es una casilla aparte que marcas solo si
                quieres, y puedes revocarla cuando quieras.
            </p>
        </div>
    </div>
</section>

<x-cta-band
    eyebrow="¿Tu caso es más complicado?"
    title="Cuando el formato no alcanza, lo escribe un abogado"
    text="Si tu situación tiene detalles que un documento automático no cubre, un abogado de la red lo redacta sobre tus hechos."
    primary-label="Ver trámites con abogado"
    :primary-href="route('documents.index')"
    secondary-label="Buscar abogado"
    :secondary-href="route('lawyers.index')" />
@endsection
