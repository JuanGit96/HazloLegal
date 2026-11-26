@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<article class="section section--white">
    <div class="container container--prose">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <header>
            @if ($post->category)
                <a href="{{ $post->category->url() }}" class="pill pill--brand" style="text-decoration: none;">
                    {{ $post->category->emoji }} {{ $post->category->name }}
                </a>
            @endif

            <h1 style="margin-top: var(--s5);">{{ $post->title }}</h1>

            @if ($post->excerpt)
                <p class="text-muted" style="font-size: var(--t-lg);">{{ $post->excerpt }}</p>
            @endif

            <div class="post-card__meta" style="padding-block: var(--s5); border-block: 1px solid var(--border); margin-bottom: var(--s8);">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">
                        Publicado el {{ $post->published_at->translatedFormat('j \d\e F \d\e Y') }}
                    </time>
                @endif
                @if ($post->reviewed_at)
                    <span aria-hidden="true">·</span>
                    <span>Revisado el {{ $post->reviewed_at->translatedFormat('j M Y') }}</span>
                @endif
                <span aria-hidden="true">·</span>
                <span>{{ $post->reading_minutes }} min de lectura</span>
            </div>
        </header>

        {{-- E-E-A-T: la autoría verificable es la señal más fuerte en contenido legal. --}}
        @unless ($post->author)
            {{-- Firmado por la casa. Se dice igual quién responde por el
                 texto: un artículo sin autor visible no inspira confianza, y
                 en contenido legal la confianza es la conversión. --}}
            <div class="author-box" style="margin-bottom: var(--s10);">
                <div class="author-box__avatar" aria-hidden="true">HL</div>
                <div>
                    <h3>Escrito por el equipo de {{ config('hazlolegal.brand.name') }}</h3>
                    <p>Equipo jurídico y editorial</p>
                    <p class="author-box__cred">
                        Nuestros abogados escriben y mantienen los formatos del catálogo.
                        Este artículo es informativo y no sustituye una asesoría particular.
                    </p>
                </div>
            </div>
        @endunless

        @if ($post->author)
            <div class="author-box" style="margin-bottom: var(--s10);">
                <div class="author-box__avatar" aria-hidden="true">{{ $post->author->initials() }}</div>
                <div>
                    <h2 style="font-size: var(--t-base); margin-bottom: var(--s1);">
                        Escrito por {{ $post->author->name }}
                    </h2>
                    <p>{{ $post->author->headline }}</p>
                    <p class="author-box__cred">
                        {{ $post->author->years_experience }} años ejerciendo en Colombia{{ $post->author->university ? ' · '.$post->author->university : '' }}
                    </p>
                </div>
            </div>
        @endif

        <div class="prose">
            {!! $post->body !!}
        </div>

        {{-- Imán de leads: la plantilla gratis a cambio del correo. --}}
        @if ($post->hasLeadMagnet())
            <div class="lead-magnet mt-10" id="plantilla">
                @if (session('leadStatus'))
                    <div class="alert alert--success" style="margin-bottom: var(--s5);">
                        <span class="alert__icon" aria-hidden="true">✓</span>
                        <div>{{ session('leadStatus') }}</div>
                    </div>
                @endif

                <h2 style="font-size: var(--t-lg);">📄 {{ $post->lead_magnet_title }}</h2>
                <p class="text-sm text-muted">
                    Te la enviamos gratis por correo. Sin letra menuda: es tuya aunque nunca nos compres nada.
                </p>

                <form action="{{ route('blog.lead-magnet', $post->slug) }}" method="post" class="form" style="margin-top: var(--s5);">
                    @csrf
                    <div class="form-row">
                        <div class="field">
                            <label for="lead_name">Tu nombre</label>
                            <input type="text" id="lead_name" name="name" class="input" value="{{ old('name') }}">
                        </div>
                        <div class="field">
                            <label for="lead_email">Tu correo <span class="req" aria-hidden="true">*</span></label>
                            <input type="email" id="lead_email" name="email" class="input" required value="{{ old('email') }}">
                            <x-field-error name="email" />
                        </div>
                    </div>

                    <label class="checkbox">
                        <input type="checkbox" name="accepts_privacy" value="1" required>
                        <span>
                            Autorizo el tratamiento de mis datos para recibir la plantilla y guías ocasionales.
                            Puedo darme de baja cuando quiera.
                            <a href="{{ route('pages.privacy') }}" target="_blank">Política de privacidad</a>.
                        </span>
                    </label>
                    <x-field-error name="accepts_privacy" />

                    <button type="submit" class="btn btn--brand">Enviarme la plantilla gratis</button>
                </form>
            </div>
        @endif

        {{-- Conversión contextual al servicio del cluster.

             Si el trámite es gratis se dice en el botón y no se esconde
             detrás de «ver precio»: quien acaba de leer la guía está
             decidiendo si vale la pena el clic, y «gratis» es la respuesta.
             Tampoco se le ofrece un abogado, porque un trámite que se arma
             solo no lo escribe ninguno. --}}
        @if ($post->relatedDocument)
            @php
                $relacionado = $post->relatedDocument;
                $esGratis = $relacionado->isFreeTier();
            @endphp

            <div class="card card--pad-lg mt-10"
                 style="border-color: var({{ $esGratis ? '--action' : '--brand' }}); background: var({{ $esGratis ? '--orange-50' : '--blue-50' }});">
                <span class="eyebrow">
                    {{ $esGratis ? 'No tienes que pagar por esto' : '¿Prefieres que lo haga un abogado?' }}
                </span>
                <h2 style="font-size: var(--t-lg); margin-top: var(--s3);">
                    {{ $relacionado->name }}
                    @if ($esGratis)<span class="hl hl--orange">es gratis</span>@endif
                </h2>
                <p class="text-sm text-muted">{{ $relacionado->summary }}</p>

                @if ($esGratis)
                    <p class="text-sm" style="margin-bottom: var(--s5);">
                        Lo armas en línea sobre el formato que escribieron nuestros abogados y lo
                        recibes en PDF, listo para firmar y radicar. Solo te pedimos tus datos para
                        poder enviártelo.
                    </p>
                @endif

                <div class="btn-row">
                    <a href="{{ $relacionado->url() }}" class="btn btn--{{ $esGratis ? 'action' : 'primary' }}">
                        {{ $esGratis ? '¡Es gratis! Hacerlo ahora' : 'Ver precio y plazo' }}
                    </a>
                    <a href="{{ $wa->forDocument($relacionado->name) }}" class="btn btn--outline" target="_blank" rel="noopener">
                        Preguntar por WhatsApp
                    </a>
                </div>
            </div>
        @endif

        {{-- «Presencia en los artículos»: lo que el plan del abogado dice
             que incluye, aquí es donde ocurre.

             La guía se queda publicada con su firma pase lo que pase —eso
             no se borra cuando alguien se va—, pero el enlace a la ficha
             solo sale mientras la ficha exista. No se afirma que revisara
             nada más que lo que escribió. --}}
        @if ($post->author?->offersBlogPlacement())
            <div class="card card--pad-lg mt-10">
                <span class="eyebrow">Quién escribió esta guía</span>

                <div class="author-box" style="margin-top: var(--s4);">
                    <div class="author-box__avatar" aria-hidden="true">{{ $post->author->initials() }}</div>
                    <div>
                        <h2 style="font-size: var(--t-base); margin-bottom: var(--s1);">{{ $post->author->name }}</h2>
                        <p>{{ $post->author->headline }}</p>
                        <p class="author-box__cred">
                            {{ $post->author->years_experience }} años ejerciendo en Colombia{{ $post->author->city ? ' · '.$post->author->city : '' }}
                        </p>
                    </div>
                </div>

                <div class="btn-row" style="margin-top: var(--s5);">
                    <a href="{{ route('lawyers.show', $post->author->slug) }}" class="btn btn--outline">
                        Ver su perfil
                    </a>
                </div>
            </div>
        @endif

        <div class="alert alert--warn mt-10">
            <span class="alert__icon" aria-hidden="true">ℹ️</span>
            <div>
                <strong>Aviso.</strong>
                <p style="margin-top: var(--s1);">
                    Esta guía es información general sobre la legislación colombiana vigente al
                    {{ ($post->reviewed_at ?? $post->published_at ?? now())->translatedFormat('j \d\e F \d\e Y') }}.
                    No reemplaza una asesoría particular: cada caso tiene detalles que pueden cambiar
                    el resultado. Si tienes dudas sobre tu situación concreta,
                    <a href="{{ route('cases.create') }}">consúltanos</a>.
                </p>
            </div>
        </div>
    </div>
</article>

@if ($related->isNotEmpty())
<section class="section section--sunken section--tight">
    <div class="container">
        <div class="section-head" style="margin-bottom: var(--s8);">
            <h2>Sigue leyendo</h2>
        </div>

        <div class="grid grid--3">
            @foreach ($related as $other)
                <article class="post-card">
                    <div class="post-card__cover" aria-hidden="true">{{ $other->category?->emoji ?: '📘' }}</div>
                    <div class="post-card__body">
                        <h3><a href="{{ $other->url() }}">{{ $other->title }}</a></h3>
                        <p>{{ \Illuminate\Support\Str::limit($other->excerpt, 100) }}</p>
                        <div class="post-card__meta"><span>{{ $other->reading_minutes }} min</span></div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
