@extends('layouts.site')

@section('content')
@php
    $documentPlan = $plans->firstWhere('key', \App\Domain\Catalog\Enums\PlanKey::Document);
@endphp

<section class="section section--white section--tight">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Catálogo completo</span>
            <h1>Trámites y documentos legales en Colombia</h1>
            <p>Precio fijo publicado, plazo comprometido y formatos escritos por abogados colombianos.</p>
        </div>

        <form action="{{ route('documents.index') }}" method="get" role="search"
              style="max-width: 540px; margin: 0 auto var(--s8); display: flex; gap: var(--s3);">
            <label for="q" class="visually-hidden">Buscar un trámite</label>
            <input type="search" id="q" name="q" class="input" value="{{ $search }}"
                   placeholder="Busca: tutela, arriendo, datacrédito, liquidación…">
            <button type="submit" class="btn btn--brand">Buscar</button>
        </form>

        <div class="area-chips">
            <a href="{{ route('documents.index') }}" class="area-chip" @if (! request('q')) aria-current="page" @endif>Todos</a>
            @foreach ($areas as $area)
                <a href="{{ route('documents.area', $area->value) }}" class="area-chip">
                    <span aria-hidden="true">{{ $area->emoji() }}</span> {{ $area->getLabel() }}
                </a>
            @endforeach
        </div>

        @if ($search !== '')
            <p class="center text-sm text-muted" style="margin-bottom: var(--s8);">
                {{ $documents->count() }} {{ \Illuminate\Support\Str::plural('resultado', $documents->count()) }}
                para «{{ $search }}»
                @if ($documents->isEmpty())
                    · <a href="{{ route('documents.index') }}">ver todo el catálogo</a>
                @endif
            </p>
        @endif

        @if ($documents->isEmpty())
            <div class="alert alert--warn" style="max-width: 640px; margin: 0 auto;">
                <span class="alert__icon" aria-hidden="true">🔍</span>
                <div>
                    <strong>No encontramos ese trámite en el catálogo.</strong>
                    <p style="margin-top: var(--s1);">
                        Puede ser que lo manejemos como caso particular.
                        <a href="{{ route('cases.create') }}">Cuéntanos tu situación</a> y un abogado te responde sin costo.
                    </p>
                </div>
            </div>
        @elseif ($search !== '')
            <div class="catalog-grid">
                @foreach ($documents as $document)
                    <x-doc-card :document="$document"
                        :price="$documentPlan ? $pricing->formatCop($pricing->quote($document, $documentPlan)->amount) : null" />
                @endforeach
            </div>
        @else
            @foreach ($grouped as $group)
                <div class="area-group">
                    <div class="area-group__head">
                        <span class="area-group__emoji" aria-hidden="true">{{ $group['area']->emoji() }}</span>
                        <h2 style="font-size: var(--t-lg); margin: 0;">{{ $group['area']->getLabel() }}</h2>
                        <span class="area-group__count">
                            {{ $group['documents']->count() }}
                            {{ \Illuminate\Support\Str::plural('trámite', $group['documents']->count()) }}
                        </span>
                    </div>

                    <div class="catalog-grid">
                        @foreach ($group['documents'] as $document)
                            <x-doc-card :document="$document"
                                :price="$documentPlan ? $pricing->formatCop($pricing->quote($document, $documentPlan)->amount) : null" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</section>

<section class="section section--sunken section--tight">
    <div class="container container--narrow">
        <div class="grid grid--2">
            <div class="card card--pad-lg">
                <h3>⚡ Entrega automatizada</h3>
                <p class="text-sm text-muted mb-0">
                    Trámites con estructura estable: se arman sobre un formato que escribieron nuestros
                    abogados y que actualizamos cuando cambia la norma. El documento no se revisa caso
                    por caso, y por eso son los más rápidos y los más económicos.
                </p>
            </div>
            <div class="card card--pad-lg">
                <h3>✍️ Redacción a la medida</h3>
                <p class="text-sm text-muted mb-0">
                    Trámites que exigen narrar hechos, hacer cálculos o escribir cláusulas condicionales.
                    Aquí un abogado redacta desde cero: no hay plantilla que sirva, y automatizarlos
                    produciría documentos inservibles.
                </p>
            </div>
        </div>
    </div>
</section>

<x-cta-band
    title="¿No sabes cuál de todos necesitas?"
    text="Escríbenos lo que te está pasando y te decimos cuál trámite aplica, o si tu caso requiere algo distinto. Orientarte no cuesta nada."
    primary-label="Contarle mi caso a un abogado"
    :primary-href="route('cases.create')"
    secondary-label="Ver cómo funciona"
    :secondary-href="route('pages.how-it-works')" />
@endsection
