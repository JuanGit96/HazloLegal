@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Caso {{ $case->reference }}</span>

            @if ($case->assignedLawyer)
                <h1>Ya elegiste a <span class="hl hl--blue">tu abogado</span></h1>
                <p>{{ $case->assignedLawyer->name }} tiene tu caso y te escribe directamente.</p>
            @else
                <h1>Elige a <span class="hl hl--blue">tu abogado</span></h1>
                <p>
                    Estos son los que quieren tomar tu caso. Mira sus perfiles y escoge con quién
                    te sientes mejor: <strong>ninguno tiene tus datos todavía</strong>.
                </p>
            @endif
        </div>

        @if ($case->assignedLawyer)
            <div class="card card--pad-lg">
                <div class="order-row__head">
                    <div>
                        <h2 style="font-size: var(--t-lg); margin-bottom: var(--s1);">
                            {{ $case->assignedLawyer->name }}
                        </h2>
                        <p class="text-sm text-muted mb-0">{{ $case->assignedLawyer->headline }}</p>
                    </div>
                    <span class="pill pill--success">Tu abogado</span>
                </div>

                <div class="buybox__divider"></div>

                <p class="text-sm mb-0">
                    Te escribe a <strong>{{ $case->client_email }}</strong> y a tu celular.
                    Si en 24 horas no sabes de él,
                    <a href="{{ route('pages.contact') }}">cuéntanos</a> y lo resolvemos.
                </p>
            </div>
        @elseif ($offers->isEmpty())
            <div class="alert alert--info">
                <span class="alert__icon" aria-hidden="true">🕒</span>
                <div>
                    <strong>Todavía no se ha postulado nadie.</strong>
                    <p style="margin-top: var(--s1);">
                        Tu caso ya les llegó a los abogados de tu área. En cuanto alguno quiera
                        tomarlo te escribimos, y lo verás aquí mismo.
                    </p>
                </div>
            </div>
        @else
            @if ($errors->any())
                <div class="alert alert--danger" style="margin-bottom: var(--s6);">
                    <span class="alert__icon" aria-hidden="true">!</span>
                    <div>@foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach</div>
                </div>
            @endif

            <form action="{{ route('cases.pick', $case->reference) }}" method="post">
                @csrf
                <input type="hidden" name="t" value="{{ request('t') }}">

                <div class="grid" style="gap: var(--s4);">
                    @foreach ($offers as $offer)
                        @php $lawyer = $offer->lawyer; @endphp
                        <label class="pick">
                            <input type="radio" name="offer" value="{{ $offer->id }}" required>
                            <span class="pick__box">
                                <span class="pick__head">
                                    <span class="pick__avatar" aria-hidden="true">{{ $lawyer->initials() }}</span>
                                    <span class="pick__who">
                                        <strong>{{ $lawyer->name }}</strong>
                                        <small>{{ $lawyer->headline ?: ($lawyer->isFirm() ? 'Firma de abogados' : 'Abogado titulado') }}</small>
                                    </span>
                                    <span class="choice__radio" aria-hidden="true"></span>
                                </span>

                                <span class="pick__meta">
                                    @if ($lawyer->isFirm() && $lawyer->team_size)
                                        <span class="pill pill--outline">{{ $lawyer->team_size }} abogados</span>
                                    @elseif ($lawyer->years_experience)
                                        <span class="pill pill--outline">{{ $lawyer->years_experience }} años de ejercicio</span>
                                    @endif
                                    <span class="pill pill--outline">{{ $lawyer->city }}</span>
                                    @if ($lawyer->offers_virtual)
                                        <span class="pill pill--success">Atiende virtual</span>
                                    @endif
                                    @if ($lawyer->offers_free_first_call)
                                        <span class="pill pill--action">Primera llamada gratis</span>
                                    @endif
                                </span>

                                @if ($offer->pitch)
                                    <span class="pick__pitch">«{{ $offer->pitch }}»</span>
                                @elseif ($lawyer->bio)
                                    <span class="pick__pitch">{{ \Illuminate\Support\Str::limit($lawyer->bio, 190) }}</span>
                                @endif

                                @if ($lawyer->is_published)
                                    <a href="{{ route('lawyers.show', $lawyer->slug) }}" class="pick__profile" target="_blank" rel="noopener">
                                        Ver su perfil completo →
                                    </a>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="center mt-10">
                    <button type="submit" class="btn btn--primary btn--lg">Elegir a este abogado</button>
                    <p class="text-xs text-muted" style="margin-top: var(--s4);">
                        Al elegir, le pasamos tus datos de contacto solo a él. Los demás no los ven.
                    </p>
                </div>
            </form>

            <p class="center text-sm text-muted mt-10">
                ¿Prefieres esperar a que se postulen más? Guarda este enlace y vuelve cuando quieras.
            </p>
        @endif
    </div>
</section>
@endsection
