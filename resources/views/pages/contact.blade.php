@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="section section--white">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Contacto</span>
            <h1>Escríbenos y <span class="hl hl--blue">te respondemos</span></h1>
            <p>Menos de 24 horas hábiles. Responde una persona del equipo, no un bot.</p>
        </div>

        @if (session('contactStatus'))
            <div class="alert alert--success" role="status" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">✓</span>
                <div>
                    <strong>Recibimos tu mensaje.</strong>
                    <p style="margin-top: var(--s1);">
                        Tu radicado es <strong class="mono">{{ session('contactStatus') }}</strong>.
                        Te lo enviamos también por correo.
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid--2" style="gap: var(--s10); align-items: start;">
            <div>
                <form action="{{ route('pages.contact.store') }}" method="post" class="form">
                    @csrf

                    <div class="field">
                        <label for="name">Tu nombre <span class="req">*</span></label>
                        <input type="text" id="name" name="name" class="input" required
                               value="{{ old('name') }}" autocomplete="name">
                        <x-field-error name="name" />
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="email">Correo <span class="req">*</span></label>
                            <input type="email" id="email" name="email" class="input" required
                                   value="{{ old('email') }}" autocomplete="email">
                            <x-field-error name="email" />
                        </div>
                        <div class="field">
                            <label for="phone">Celular</label>
                            <input type="tel" id="phone" name="phone" class="input"
                                   value="{{ old('phone') }}" autocomplete="tel">
                        </div>
                    </div>

                    <div class="field">
                        <label for="subject">Asunto</label>
                        <input type="text" id="subject" name="subject" class="input"
                               value="{{ old('subject') }}" placeholder="Sobre qué nos escribes">
                    </div>

                    <div class="field">
                        <label for="message">Tu mensaje <span class="req">*</span></label>
                        <textarea id="message" name="message" class="textarea" required minlength="15"
                                  style="min-height: 150px;">{{ old('message') }}</textarea>
                        <x-field-error name="message" />
                    </div>

                    <label class="checkbox">
                        <input type="checkbox" name="accepts_privacy" value="1" required @checked(old('accepts_privacy'))>
                        <span>
                            Autorizo el tratamiento de mis datos para responder esta consulta,
                            conforme a la <a href="{{ route('pages.privacy') }}" target="_blank">política de privacidad</a>.
                            <span class="req">*</span>
                        </span>
                    </label>
                    <x-field-error name="accepts_privacy" />

                    <button type="submit" class="btn btn--primary btn--lg">Enviar mensaje</button>
                </form>
            </div>

            <aside>
                <div class="card card--pad-lg">
                    <h2 style="font-size: var(--t-lg);">Otras formas</h2>

                    <ul class="buybox__rows">
                        <li>
                            <span aria-hidden="true">💬</span>
                            <span>
                                <strong>WhatsApp</strong><br>
                                <a href="{{ $wa->link('Hola, tengo una consulta.') }}" target="_blank" rel="noopener">{{ $wa->display() }}</a>
                            </span>
                        </li>
                        <li>
                            <span aria-hidden="true">✉️</span>
                            <span>
                                <strong>Correo</strong><br>
                                <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>
                            </span>
                        </li>
                        <li>
                            <span aria-hidden="true">🕒</span>
                            <span><strong>Horario</strong><br>{{ config('hazlolegal.contact.hours') }}</span>
                        </li>
                    </ul>

                    <div class="buybox__divider"></div>

                    <p class="text-xs text-muted mb-0">
                        🔒 Nunca pedimos datos de tarjeta por WhatsApp ni por teléfono.
                        Si alguien lo hace en nuestro nombre, no somos nosotros.
                    </p>
                </div>

                <div class="card card--flat" style="margin-top: var(--s5); background: var(--cream-100);">
                    <p class="text-sm mb-0">
                        <strong>¿Quieres que eliminemos tus datos?</strong><br>
                        Tiene su propio formulario:
                        <a href="{{ route('pages.data-deletion') }}">eliminar mis datos</a>.
                    </p>
                </div>
            </aside>
        </div>

        @if ($faqs->isNotEmpty())
            <div style="margin-top: var(--s16);">
                <div class="section-head" style="margin-bottom: var(--s8);">
                    <h2>Antes de escribir, mira si ya está respondido</h2>
                </div>
                <x-faq-list :faqs="$faqs->map(fn ($f) => ['question' => $f->question, 'answer' => $f->answer])->all()" />
            </div>
        @endif
    </div>
</section>
@endsection
