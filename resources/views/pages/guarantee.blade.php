@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="section section--white">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="center">
            <x-guarantee-seal />
            <span class="eyebrow" style="color: var(--green-700); margin-top: var(--s6); display: block;">
                Versión {{ $guarantee['version'] }} · vigente
            </span>
            <h1>Certificado de Garantía Hazlo Legal</h1>
            <p class="text-muted" style="font-size: var(--t-lg);">
                No te pedimos que confíes a ciegas en un servicio legal que compras por internet.
                Te damos por escrito qué pasa si algo sale mal por nuestra culpa.
            </p>
        </div>
    </div>
</section>

<section class="section section--sunken section--tight">
    <div class="container container--narrow">
        <div class="grid grid--2">
            <div class="card card--pad-lg" style="border-color: var(--green-600);">
                <div style="font-size: 2rem;" aria-hidden="true">🔧</div>
                <h2 style="font-size: var(--t-lg);">Lo corregimos en {{ $guarantee['correction_hours'] }} horas</h2>
                <p class="text-sm text-muted mb-0">
                    Errores de ortografía, redacción, datos mal transcritos o normas mal citadas:
                    los arreglamos sin costo, sin discutir y
                    {{ $guarantee['unlimited_corrections'] ? 'sin límite de rondas' : 'dentro de las rondas pactadas' }}.
                </p>
            </div>

            <div class="card card--pad-lg" style="border-color: var(--green-600);">
                <div style="font-size: 2rem;" aria-hidden="true">💸</div>
                <h2 style="font-size: var(--t-lg);">Devolvemos el {{ $guarantee['refund_percentage'] }} %</h2>
                <p class="text-sm text-muted mb-0">
                    Si una entidad rechaza tu documento por un defecto de forma que cometimos nosotros,
                    o si no cumplimos el plazo prometido, te regresamos todo lo que pagaste.
                </p>
            </div>
        </div>

        <div class="card card--pad-lg mt-8">
            <h2 style="font-size: var(--t-xl);">El compromiso, punto por punto</h2>

            <ul class="guarantee-list" style="margin-top: var(--s6);">
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">1</span>
                    <span>
                        <strong>Revisión por abogado titulado en todos los trámites</strong>
                        Ningún documento sale sin que un abogado con tarjeta profesional vigente lo haya auditado.
                        Puedes verificar la tarjeta de cada uno en el registro del Consejo Superior de la Judicatura.
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">2</span>
                    <span>
                        <strong>Corrección sin costo de nuestros errores</strong>
                        Ortografía, gramática, datos tuyos o de la contraparte mal transcritos, normas derogadas
                        o mal citadas. Máximo {{ $guarantee['correction_hours'] }} horas desde que nos avisas.
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">3</span>
                    <span>
                        <strong>Devolución total si lo rechazan por nuestra culpa</strong>
                        Si una EPS, un juzgado, una secretaría de tránsito o cualquier entidad devuelve tu documento
                        por un defecto de forma atribuible a nuestra redacción, te devolvemos el
                        {{ $guarantee['refund_percentage'] }} % de lo pagado.
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">4</span>
                    <span>
                        <strong>Cumplimos el plazo o no lo cobras</strong>
                        Si nos pasamos del tiempo que prometimos por una razón nuestra, el trámite queda sin costo.
                        El plazo se cuenta desde que confirmamos el pago y tenemos la información completa.
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">5</span>
                    <span>
                        <strong>{{ $guarantee['claim_window_days'] }} días para reclamar</strong>
                        Tienes un mes completo desde la entrega. Tiempo suficiente para radicar el documento
                        y ver la respuesta de la entidad antes de que la garantía expire.
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">6</span>
                    <span>
                        <strong>Confidencialidad</strong>
                        Tu caso solo lo ven los abogados que lo atienden, sujetos a reserva profesional.
                        No publicamos tu información ni la vendemos. Puedes pedir su eliminación cuando quieras.
                    </span>
                </li>
            </ul>
        </div>

        {{-- La transparencia sobre los límites genera más confianza que ocultarlos. --}}
        <div class="card card--pad-lg mt-8" style="border-color: var(--amber-500); background: var(--amber-50);">
            <h2 style="font-size: var(--t-xl);">Qué no cubre (y por qué lo decimos de frente)</h2>
            <p class="text-sm" style="color: #92400E;">
                Una garantía que promete todo no es creíble. Estos son los límites reales:
            </p>

            <ul style="font-size: var(--t-sm); color: #92400E; margin-bottom: 0;">
                <li>
                    <strong>El resultado de la decisión de un juez o una entidad.</strong>
                    Ningún abogado en el mundo puede garantizar un fallo. Quien te lo prometa está mintiendo.
                    Garantizamos la calidad técnica del documento, no la decisión de un tercero.
                </li>
                <li>
                    <strong>Información que no nos diste o que nos diste incorrecta.</strong>
                    Si un dato clave no aparecía en lo que nos contaste, no podíamos incluirlo.
                </li>
                <li>
                    <strong>Cambios de opinión después de la entrega.</strong>
                    Si quieres pedir algo distinto, con otra contraparte u otros hechos, es un documento nuevo.
                    Te lo diremos con claridad en vez de cobrarte sin avisar.
                </li>
                <li>
                    <strong>Trámites, tasas y pagos ante terceros.</strong>
                    Derechos de Cámara de Comercio, notarías, juzgados o impuestos de registro no los cobramos
                    nosotros ni los podemos devolver.
                </li>
                <li>
                    <strong>Hechos posteriores a la entrega</strong> que cambien tu situación.
                </li>
            </ul>
        </div>
    </div>
</section>

{{-- ============================================ FORMULARIO DE RECLAMACIÓN --}}
<section class="section section--white" id="reclamar">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Activar la garantía</span>
            <h2>Algo salió mal. Cuéntanos qué pasó.</h2>
            <p>Un abogado revisa tu solicitud y te responde dentro de las próximas 24 horas hábiles.</p>
        </div>

        @if (session('claimStatus'))
            <div class="alert alert--success" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">✓</span>
                <div>{{ session('claimStatus') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert--danger" style="margin-bottom: var(--s8);">
                <span class="alert__icon" aria-hidden="true">!</span>
                <div>
                    <strong>Revisa estos datos:</strong>
                    <ul style="margin: var(--s2) 0 0; padding-left: var(--s5);">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('pages.guarantee.claim') }}" method="post" class="form">
            @csrf

            <div class="form-row">
                <div class="field">
                    <label for="client_name">Tu nombre <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="client_name" name="client_name" class="input" required
                           value="{{ old('client_name') }}" autocomplete="name">
                    <x-field-error name="client_name" />
                </div>

                <div class="field">
                    <label for="client_email">Tu correo <span class="req" aria-hidden="true">*</span></label>
                    <input type="email" id="client_email" name="client_email" class="input" required
                           value="{{ old('client_email') }}" autocomplete="email">
                    <x-field-error name="client_email" />
                </div>
            </div>

            <div class="field">
                <label for="order_reference">Referencia de tu pedido <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="order_reference" name="order_reference" class="input" required
                       value="{{ old('order_reference') }}" placeholder="HL-2026-00042">
                <span class="field__hint">Aparece en el correo de confirmación. Empieza por HL.</span>
                <x-field-error name="order_reference" />
            </div>

            <div class="field">
                <label for="reason">¿Qué pasó? <span class="req" aria-hidden="true">*</span></label>
                <select id="reason" name="reason" class="select" required>
                    <option value="">Elige el motivo…</option>
                    @foreach ($reasons as $reason)
                        <option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>
                            {{ $reason->getLabel() }}
                        </option>
                    @endforeach
                </select>
                <x-field-error name="reason" />
            </div>

            <div class="field">
                <label for="description">Cuéntanos los detalles <span class="req" aria-hidden="true">*</span></label>
                <textarea id="description" name="description" class="textarea" required minlength="30" maxlength="4000"
                          placeholder="Describe el error concreto o, si una entidad lo rechazó, qué te dijeron exactamente.">{{ old('description') }}</textarea>
                <span class="field__hint">
                    Si la entidad te dio una respuesta escrita, transcribe el motivo del rechazo: acelera todo.
                </span>
                <x-field-error name="description" />
            </div>

            <button type="submit" class="btn btn--brand btn--lg">Activar mi garantía</button>

            <p class="text-xs text-muted">
                También puedes escribirnos directamente por
                <a href="{{ $wa->link('Hola, quiero activar la garantía de mi pedido.') }}" target="_blank" rel="noopener">WhatsApp</a>
                o a <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>.
            </p>
        </form>
    </div>
</section>

@if ($faqs->isNotEmpty())
<section class="section section--sunken">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Preguntas frecuentes</span>
            <h2>Sobre la garantía</h2>
        </div>
        <x-faq-list :faqs="$faqs" :open="2" />
    </div>
</section>
@endif

<x-cta-band
    title="Ahora sí, sin riesgo de por medio"
    text="Ya sabes exactamente qué pasa si algo sale mal. Elige tu trámite y deja que un abogado se encargue."
    primary-label="Ver trámites y precios"
    :primary-href="route('documents.index')" />
@endsection
