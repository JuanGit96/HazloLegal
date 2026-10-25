@extends('layouts.site')

@section('content')
@php
    $documentPlan = $plans->firstWhere('key', \App\Domain\Catalog\Enums\PlanKey::Document);
@endphp

<section class="section section--white">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">{{ $area->emoji() }} Área de práctica</span>
            <h1>{{ $area->getLabel() }}: trámites y documentos</h1>
            <p>
                {{ $documents->count() }} {{ \Illuminate\Support\Str::plural('trámite', $documents->count()) }}
                disponibles, todos con precio fijo y revisión de un abogado especializado en el área.
            </p>
        </div>

        <div class="catalog-grid">
            @foreach ($documents as $document)
                <x-doc-card :document="$document"
                    :price="$documentPlan ? $pricing->formatCop($pricing->quote($document, $documentPlan)->amount) : null" />
            @endforeach
        </div>

        <div class="center mt-10">
            <a href="{{ route('documents.index') }}" class="btn btn--outline">Ver todas las áreas</a>
        </div>
    </div>
</section>

<x-cta-band
    :title="'Tu caso de ' . \Illuminate\Support\Str::lower($area->getLabel()) . ' puede ser más complejo'"
    text="Si ninguno de estos trámites encaja, cuéntanos qué está pasando. Un abogado del área lo evalúa y te cotiza sin costo."
    primary-label="Contarle mi caso a un abogado"
    :primary-href="route('cases.create') . '?area=' . $area->value"
    secondary-label="Ver todo el catálogo"
    :secondary-href="route('documents.index')" />
@endsection
