@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--prose">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <h1>Política de <span class="hl hl--blue">cookies</span></h1>
        <p class="text-muted">
            Versión {{ $consent->version() }} &middot; última actualización
            {{ now()->translatedFormat('j \d\e F \d\e Y') }}
        </p>

        @include('pages._draft-notice')

        <div class="prose">
            <p>
                Una cookie es un archivo pequeño que el sitio guarda en tu navegador. Unas hacen
                falta para que el sitio funcione; otras solo sirven para medir, y esas no se activan
                si no nos das permiso. Aquí está la lista completa, con lo que hace cada una.
            </p>

            <h2>1. Quién responde por esto</h2>
            <p>
                {{ config('hazlolegal.brand.legal_name') }}, NIT {{ config('hazlolegal.brand.nit') }},
                con domicilio en {{ config('hazlolegal.contact.address') }},
                {{ config('hazlolegal.contact.city') }}, Colombia.
                Para ejercer tus derechos escribe a
                <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>.
            </p>

            <h2>2. Por qué te pedimos permiso</h2>
            <p>
                Las cookies que permiten reconocer a una persona son tratamiento de datos
                personales, y en Colombia eso exige autorización <strong>previa, expresa e
                informada</strong> (Ley 1581 de 2012 y Decreto 1377 de 2013). Por eso:
            </p>
            <ul>
                <li>Las cookies de analítica y publicidad <strong>no se cargan</strong> hasta que las autorizas.</li>
                <li>Rechazarlas cuesta un clic, igual que aceptarlas.</li>
                <li>Puedes cambiar de opinión en esta misma página, cuando quieras.</li>
                <li>Guardamos la fecha de tu decisión para poder demostrar que existió.</li>
            </ul>

            <h2>3. Qué cookies hay</h2>

            @foreach ($catalog as $grupo)
                @continue(empty($grupo['cookies']))

                <h3>{{ $grupo['category']->getLabel() }}</h3>
                <p>{{ $grupo['category']->description() }}</p>

                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Cookie</th>
                                <th scope="col">Para qué</th>
                                <th scope="col">Duración</th>
                                <th scope="col">Quién la pone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grupo['cookies'] as $cookie)
                                <tr>
                                    <td class="mono">{{ $cookie['name'] }}</td>
                                    <td>{{ $cookie['purpose'] }}</td>
                                    <td>{{ $cookie['duration'] }}</td>
                                    <td>{{ $cookie['provider'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <h2>4. Terceros y transferencias</h2>
            <p>
                Las cookies de analítica y publicidad las pone un tercero que está fuera de
                Colombia, de modo que autorizarlas implica una <strong>transferencia internacional
                de datos</strong> a Estados Unidos. Si no la autorizas, esos servicios no se
                cargan. El sitio también pide las tipografías a Google Fonts, que no instala
                cookies pero sí recibe tu dirección IP al servir el archivo.
            </p>

            <h2>5. Tus derechos</h2>
            <p>
                Puedes conocer, actualizar, rectificar y suprimir tus datos, revocar esta
                autorización y pedir prueba de que la diste. Tienes la página de
                <a href="{{ route('pages.data-deletion') }}">eliminar mis datos</a> para lo primero
                y el panel de aquí abajo para lo segundo. El detalle completo está en la
                <a href="{{ route('pages.privacy') }}">política de tratamiento de datos</a>.
            </p>

            <h2>6. Borrarlas desde tu navegador</h2>
            <p>
                Revocar el permiso aquí impide que se vuelvan a cargar, pero no borra lo que ya
                esté guardado en tu equipo: eso se hace desde los ajustes del navegador
                (&laquo;Privacidad y seguridad &rarr; Borrar datos de navegación&raquo; en Chrome
                y Edge, &laquo;Gestionar datos de sitios web&raquo; en Safari y Firefox). También
                puedes bloquearlas todas desde ahí; si bloqueas las necesarias, los formularios
                del sitio dejarán de funcionar.
            </p>
        </div>

        {{-- ---------------- El panel de preferencias ---------------- --}}
        <div class="card card--pad-lg mt-10" id="preferencias" style="border-color: var(--brand);">
            <h2 style="font-size: var(--t-xl);">Tus preferencias</h2>
            <p class="text-sm text-muted">
                @if ($choice->answered)
                    Guardaste tu decisión y la puedes cambiar aquí mismo.
                @else
                    Todavía no has decidido, así que solo están activas las necesarias.
                @endif
            </p>

            <form action="{{ route('cookies.store') }}" method="post" class="form" style="margin-top: var(--s6);">
                @csrf
                <input type="hidden" name="decision" value="custom">
                <input type="hidden" name="redirect" value="{{ route('pages.cookies', [], false) }}#preferencias">

                <label class="checkbox">
                    <input type="checkbox" checked disabled>
                    <span>
                        <strong>{{ \App\Domain\Shared\Enums\CookieCategory::Necessary->getLabel() }}</strong><br>
                        {{ \App\Domain\Shared\Enums\CookieCategory::Necessary->description() }}
                    </span>
                </label>

                @foreach (\App\Domain\Shared\Enums\CookieCategory::optional() as $categoria)
                    <label class="checkbox">
                        <input type="checkbox" name="{{ $categoria->value }}" value="1"
                               @checked($choice->allows($categoria))>
                        <span>
                            <strong>{{ $categoria->getLabel() }}</strong><br>
                            {{ $categoria->description() }}
                        </span>
                    </label>
                @endforeach

                <div class="btn-row">
                    <button type="submit" class="btn btn--primary">Guardar mis preferencias</button>
                </div>
            </form>

            <form action="{{ route('cookies.store') }}" method="post" style="margin-top: var(--s5);">
                @csrf
                <input type="hidden" name="decision" value="necessary">
                <input type="hidden" name="redirect" value="{{ route('pages.cookies', [], false) }}#preferencias">
                <button type="submit" class="btn btn--outline btn--sm">
                    Revocar todo y dejar solo las necesarias
                </button>
            </form>
        </div>

        <p class="text-xs text-muted mt-8">
            Si cambiamos lo que hacemos con las cookies subimos la versión de esta página y te
            volvemos a preguntar: una autorización que diste sobre otro texto no cubre el nuevo.
        </p>
    </div>
</section>
@endsection
