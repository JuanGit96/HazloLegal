@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container">
        <x-wizard :step="$step" />

        <div class="section-head">
            <span class="eyebrow">Paso 1 de 3</span>
            <h1>¿Cuál trámite <span class="hl">necesitas</span>?</h1>
            <p>
                Elige el documento. En el siguiente paso lo armas con lo que te haga falta
                y nos cuentas tu caso.
            </p>
        </div>

        @foreach ($grouped as $group)
            <div class="area-group">
                <div class="area-group__head">
                    <span class="area-group__emoji" aria-hidden="true">{{ $group['area']->emoji() }}</span>
                    <h2 style="font-size: var(--t-lg); margin: 0;">{{ $group['area']->getLabel() }}</h2>
                </div>

                <div class="catalog-grid">
                    @foreach ($group['documents'] as $document)
                        <x-doc-card
                            :document="$document"
                            :price="$document->priceLabel()"
                            :href="route('checkout.configure', $document->slug)" />
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="alert alert--info" style="max-width: 680px; margin: var(--s8) auto 0;">
            <span class="alert__icon" aria-hidden="true">💬</span>
            <div>
                <strong>¿Ninguno encaja con lo que necesitas?</strong>
                <p style="margin-top: var(--s1);">
                    <a href="{{ route('cases.create') }}">Cuéntanos tu caso</a> y un abogado del área lo evalúa
                    y te cotiza sin costo.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
