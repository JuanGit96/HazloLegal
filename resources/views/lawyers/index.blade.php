@extends('layouts.site')

@section('content')
<section class="section section--white section--tight">
    <div class="container">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Directorio</span>
            <h1>Encuentra el abogado <span class="hl hl--blue">que tu caso necesita</span></h1>
            <p>Perfiles con experiencia, áreas de práctica y contacto directo. Tú hablas con el abogado; nosotros no cobramos comisión.</p>
        </div>

        <form action="{{ route('lawyers.index') }}" method="get" class="card card--pad-lg"
              style="max-width: 760px; margin: 0 auto var(--s10);">
            <div class="form-row">
                <div class="field">
                    <label for="area">Especialidad</label>
                    <select id="area" name="area" class="select">
                        <option value="">Todas las especialidades</option>
                        @foreach ($areas as $a)
                            <option value="{{ $a->value }}" @selected($selectedArea?->value === $a->value)>
                                {{ $a->emoji() }} {{ $a->getLabel() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="ciudad">Ciudad</label>
                    <input list="ciudades" id="ciudad" name="ciudad" class="input" value="{{ $city }}" placeholder="Todas">
                    <datalist id="ciudades">
                        @foreach ($cities as $c)<option value="{{ $c }}">@endforeach
                    </datalist>
                </div>
            </div>
            <button type="submit" class="btn btn--brand btn--block">Buscar abogado</button>
        </form>

        @if ($lawyers->isEmpty())
            <div class="alert alert--info" style="max-width: 640px; margin: 0 auto;">
                <span class="alert__icon" aria-hidden="true">🔍</span>
                <div>
                    <strong>Todavía no hay abogados publicados con ese filtro.</strong>
                    <p style="margin-top: var(--s1);">
                        Prueba sin filtros, o <a href="{{ route('cases.create') }}">cuéntanos tu caso</a>
                        y lo repartimos entre los abogados de la red.
                    </p>
                </div>
            </div>
        @else
            <div class="grid grid--2">
                @foreach ($lawyers as $lawyer)
                    <article @class(['lawyer-tile', 'lawyer-tile--featured' => $lawyer->is_featured])>
                        <div class="lawyer-tile__avatar" aria-hidden="true">{{ $lawyer->initials() }}</div>
                        <div class="lawyer-tile__body">
                            @if ($lawyer->is_featured)
                                <span class="pill pill--action" style="margin-bottom: var(--s2);">Destacado</span>
                            @endif
                            <h2 class="lawyer-tile__name">
                                <a href="{{ $lawyer->profileUrl() }}" style="color: inherit; text-decoration: none;">
                                    {{ $lawyer->displayName() }}
                                </a>
                            </h2>
                            <p class="lawyer-tile__role">{{ $lawyer->headline }}</p>
                            <p class="text-sm text-muted">{{ \Illuminate\Support\Str::limit($lawyer->bio, 120) }}</p>

                            <div class="lawyer-tile__meta">
                                <span class="pill pill--outline">📍 {{ $lawyer->city }}</span>
                                @if ($lawyer->offers_virtual)
                                    <span class="pill pill--success">Atiende virtual</span>
                                @endif
                                @if ($lawyer->years_experience)
                                    <span class="pill pill--outline">{{ $lawyer->years_experience }} años</span>
                                @endif
                            </div>

                            <a href="{{ $lawyer->profileUrl() }}" class="btn btn--outline btn--sm" style="margin-top: var(--s4);">
                                Ver perfil →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{ $lawyers->links() }}
        @endif
    </div>
</section>

<x-cta-band
    eyebrow="¿Eres abogado?"
    title="Aparece donde la gente ya está buscando"
    text="Miles de personas generan documentos legales aquí cada mes. Algunos necesitan un abogado justo después. Dos meses gratis para probarlo."
    primary-label="Ver planes para abogados"
    :primary-href="route('lawyers.join')" />
@endsection
