@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">{{ $area->emoji() }} Área de práctica</span>
            <h1><span class="hl hl--blue">{{ $area->getLabel() }}</span>: trámites y documentos</h1>
            <p>
                {{ $documents->count() }} {{ \Illuminate\Support\Str::plural('trámite', $documents->count()) }}
                disponibles. Los formatos los escribieron abogados del área y se mantienen al día
                con la norma vigente. Algunos son gratis.
            </p>
        </div>

        <div class="catalog-grid">
            @foreach ($documents as $document)
                <x-doc-card :document="$document"
                    :price="$document->priceLabel()" />
            @endforeach
        </div>

        <div class="center mt-10">
            <a href="{{ route('documents.index') }}" class="btn btn--outline">Ver todas las áreas</a>
        </div>
    </div>
</section>

@if ($lawyers->isNotEmpty())
<section class="section section--sunken section--tight">
    <div class="container">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <span class="eyebrow">Directorio</span>
            <h2>Abogados de {{ \Illuminate\Support\Str::lower($area->getLabel()) }}</h2>
            <p>Si tu caso necesita a alguien que lo lleve, estos abogados atienden esta área.</p>
        </div>

        <div class="grid grid--3">
            @foreach ($lawyers as $lawyer)
                <x-lawyer-card :lawyer="$lawyer" />
            @endforeach
        </div>

        <div class="center mt-8">
            <a href="{{ route('lawyers.index', ['area' => $area->value]) }}" class="btn btn--outline">
                Ver todo el directorio
            </a>
        </div>
    </div>
</section>
@endif

<x-cta-band
    :title="'Tu caso de ' . \Illuminate\Support\Str::lower($area->getLabel()) . ' puede ser más complejo'"
    text="Si ninguno de estos trámites encaja, cuéntanos qué está pasando. Un abogado del área lo evalúa y te cotiza sin costo."
    primary-label="Contarle mi caso a un abogado"
    :primary-href="route('cases.create') . '?area=' . $area->value"
    secondary-label="Ver todo el catálogo"
    :secondary-href="route('documents.index')" />
@endsection
