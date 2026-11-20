@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Tus datos, tus reglas</span>
            <h1>Eliminar mis datos personales</h1>
            <p>
                Es tu derecho y no tienes que dar explicaciones. Lo atendemos en
                <strong>15 días hábiles</strong>, como manda la Ley 1581 de 2012.
            </p>
        </div>

        @if (session('deletionStatus'))
            <div class="alert alert--success" role="status" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">✓</span>
                <div>
                    <strong>Recibimos tu solicitud.</strong>
                    <p style="margin-top: var(--s1);">
                        Tu radicado es <strong class="mono">{{ session('deletionStatus') }}</strong>.
                        Guárdalo: es tu constancia de que la pediste y en qué fecha.
                        Te lo enviamos también por correo.
                    </p>
                </div>
            </div>
        @endif

        <div class="alert alert--info" style="margin-bottom: var(--s8);">
            <span class="alert__icon" aria-hidden="true">ℹ️</span>
            <div>
                <strong>Qué pasa cuando la pides.</strong>
                <p style="margin-top: var(--s1);">
                    Borramos tus datos de nuestras bases y dejas de aparecer para los abogados de
                    la red. Si tienes trámites en curso, algunos datos pueden conservarse el
                    tiempo que exigen las normas contables y tributarias; si es tu caso te lo
                    explicamos en la respuesta, con detalle y sin tecnicismos.
                </p>
            </div>
        </div>

        <form action="{{ route('pages.data-deletion.store') }}" method="post" class="form">
            @csrf

            <div class="field">
                <label for="name">Nombre completo <span class="req">*</span></label>
                <input type="text" id="name" name="name" class="input" required
                       value="{{ old('name') }}" autocomplete="name">
                <x-field-error name="name" />
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="email">Correo con el que nos diste tus datos <span class="req">*</span></label>
                    <input type="email" id="email" name="email" class="input" required
                           value="{{ old('email') }}" autocomplete="email">
                    <span class="field__hint">Es como encontramos tu información.</span>
                    <x-field-error name="email" />
                </div>
                <div class="field">
                    <label for="phone">Celular</label>
                    <input type="tel" id="phone" name="phone" class="input" value="{{ old('phone') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="document_type">Tipo de documento <span class="req">*</span></label>
                    <select id="document_type" name="document_type" class="select" required>
                        @foreach (['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'NIT' => 'NIT', 'PA' => 'Pasaporte', 'PEP' => 'PEP / PPT'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('document_type', 'CC') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="document_number">Número de documento <span class="req">*</span></label>
                    <input type="text" id="document_number" name="document_number" class="input" required
                           value="{{ old('document_number') }}" inputmode="numeric">
                    <span class="field__hint">Lo pedimos para comprobar que eres el titular.</span>
                    <x-field-error name="document_number" />
                </div>
            </div>

            <div class="field">
                <label for="message">¿Quieres añadir algo? (opcional)</label>
                <textarea id="message" name="message" class="textarea" style="min-height: 110px;"
                          placeholder="Por ejemplo, si solo quieres dejar de recibir correos pero conservar tu cuenta.">{{ old('message') }}</textarea>
            </div>

            <label class="checkbox">
                <input type="checkbox" name="confirms_ownership" value="1" required @checked(old('confirms_ownership'))>
                <span>
                    Declaro que soy el titular de los datos que pido eliminar, o su representante
                    autorizado. <span class="req">*</span>
                </span>
            </label>
            <x-field-error name="confirms_ownership" />

            <button type="submit" class="btn btn--primary btn--lg">Enviar solicitud</button>

            <p class="text-xs text-muted">
                Si solo quieres dejar de recibir correos, puedes darte de baja desde el enlace que
                lleva cada uno, sin pasar por aquí.
            </p>
        </form>
    </div>
</section>
@endsection
