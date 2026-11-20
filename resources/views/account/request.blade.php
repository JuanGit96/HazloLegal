@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Soy cliente</span>
            <h1>Tus trámites, <span class="hl hl--blue">cuando los necesites</span></h1>
            <p>
                Escribe el correo con el que los pediste y te mandamos un enlace.
                <strong>Sin contraseña</strong>: no hay nada que recordar ni que perder.
            </p>
        </div>

        @if (session('accountStatus'))
            <div class="alert alert--success" role="status" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">✓</span>
                <div>
                    <strong>Revisa tu correo.</strong>
                    <p style="margin-top: var(--s1);">
                        Si <strong>{{ session('accountStatus') }}</strong> tiene trámites con
                        nosotros, el enlace ya va en camino. Vence en 30 minutos.
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('account.request.send') }}" method="post" class="form"
              style="max-width: 460px; margin: 0 auto;">
            @csrf

            <div class="field">
                <label for="email">Tu correo</label>
                <input type="email" id="email" name="email" class="input" required
                       value="{{ old('email') }}" autocomplete="email"
                       placeholder="el mismo con el que pediste el documento">
                <x-field-error name="email" />
            </div>

            <button type="submit" class="btn btn--primary btn--block btn--lg">Enviarme el enlace</button>

            <p class="text-xs text-muted center">
                ¿Todavía no has pedido nada?
                <a href="{{ route('tools.index') }}">Empieza por algo gratis</a>.
            </p>
        </form>
    </div>
</section>
@endsection
