@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $cop = fn ($n) => '$'.number_format((float) $n, 0, ',', '.');
@endphp

<section class="section section--white">
    <div class="container">
        <x-wizard :step="$step" :free="$quote->isFree()" />

        <div class="section-head" style="margin-bottom: var(--s10);">
            @if ($document->isFreeTier())
                <span class="pill pill--success">Este trámite es gratis</span>
            @endif
            <h1 style="margin-top: var(--s4);">{{ $document->name }}</h1>
            <p>{{ $document->tagline ?: $document->summary }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert--danger" style="max-width: 760px; margin: 0 auto var(--s8);">
                <span class="alert__icon" aria-hidden="true">!</span>
                <div>
                    <strong>Revisa estos datos:</strong>
                    <ul style="margin: var(--s2) 0 0; padding-left: var(--s5);">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('checkout.store', $document->slug) }}" method="post" id="checkout-form">
            @csrf

            <div class="checkout-layout">
                <div>
                    {{-- ---------------- 1. Adicionales ---------------- --}}
                    @if ($offers->isNotEmpty())
                        <h2 style="font-size: var(--t-xl);">1. Arma tu trámite</h2>
                        <p class="text-sm text-muted">
                            @if ($document->isFreeTier())
                                El documento es gratis. Esto es lo que puedes sumarle si lo necesitas.
                            @else
                                Lo básico ya está incluido. Agrega solo lo que te haga falta.
                            @endif
                        </p>

                        <p class="alert alert--info" id="aviso-incompatible" role="status" hidden
                           style="margin-bottom: var(--s5);">
                            <span class="alert__icon" aria-hidden="true">⚖️</span>
                            <span data-texto></span>
                        </p>

                        <div class="grid" style="gap: var(--s3); margin: var(--s6) 0 var(--s10);">
                            @foreach ($offers as $offer)
                                <label class="addon">
                                    <input type="checkbox"
                                           name="addons[]"
                                           value="{{ $offer->key() }}"
                                           data-price="{{ $offer->price }}"
                                           data-kind="{{ $offer->addon->kind->value }}"
                                           @checked($offer->isSelected)>
                                    <span class="addon__box">
                                        <span class="addon__check" aria-hidden="true">
                                            <svg viewBox="0 0 14 14" fill="none">
                                                <path d="M2 7.5L5.5 11L12 3.5" stroke="currentColor" stroke-width="2.5"
                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>

                                        <span class="addon__body">
                                            <span class="addon__head">
                                                <span class="addon__name">
                                                    {{ $offer->addon->emoji }} {{ $offer->addon->name }}
                                                </span>
                                                <span @class(['addon__price', 'addon__price--free' => $offer->price === 0])>
                                                    @if ($showDiscounts && $offer->formattedCompareAt())
                                                        <s>{{ $offer->formattedCompareAt() }}</s>
                                                    @endif
                                                    {{ $offer->formattedPrice() }}
                                                </span>
                                            </span>

                                            @if ($offer->reason)
                                                <p class="addon__reason">{{ $offer->reason }}</p>
                                            @endif

                                            @if ($offer->addon->bullets)
                                                <ul class="addon__bullets">
                                                    @foreach (array_slice($offer->addon->bullets, 0, 3) as $bullet)
                                                        <li>{{ $bullet }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            <span class="addon__tags">
                                                @if ($offer->addon->badge)
                                                    <span class="pill pill--action">{{ $offer->addon->badge }}</span>
                                                @endif
                                                @if ($offer->addon->social_proof)
                                                    <span class="pill pill--success">{{ $offer->addon->social_proof }}</span>
                                                @endif
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- ---------------- 2. El caso ---------------- --}}
                    <h2 style="font-size: var(--t-xl);">{{ $offers->isNotEmpty() ? '2' : '1' }}. Cuéntanos tu caso</h2>
                    <p class="text-sm text-muted">Esto es lo que va dentro del documento. Mientras más concreto, mejor queda.</p>

                    @if ($aiHook)
                        <div class="hook-strip" style="border-radius: var(--r-md); margin: var(--s5) 0; border: 1px solid var(--sand-300);">
                            <span class="hook-strip__item"><span aria-hidden="true">✨</span> Revisamos ortografía y redacción</span>
                            <span class="hook-strip__item"><span aria-hidden="true">🛡️</span> Formato escrito por abogados</span>
                            <span class="hook-strip__item"><span aria-hidden="true">⚖️</span> Tono formal ante la entidad</span>
                        </div>
                    @endif

                    <div class="form">
                        {{-- Las preguntas propias del trámite. Van antes del
                             relato libre: contestar «¿a qué entidad?» es mucho
                             más fácil que redactar, y empezar por lo fácil
                             hace que se termine el formulario. --}}
                        @foreach ($fields as $field)
                            <div class="field">
                                <label for="{{ $field->inputId() }}">
                                    {{ $field->label }}
                                    @if ($field->required)<span class="req">*</span>@endif
                                </label>

                                @if ($field->type === 'textarea')
                                    <textarea id="{{ $field->inputId() }}"
                                              name="{{ $field->inputName() }}"
                                              class="textarea"
                                              @required($field->required)
                                              placeholder="{{ $field->placeholder }}">{{ old('fields.'.$field->key) }}</textarea>
                                @else
                                    <input type="{{ $field->type === 'date' ? 'date' : ($field->type === 'number' ? 'number' : 'text') }}"
                                           id="{{ $field->inputId() }}"
                                           name="{{ $field->inputName() }}"
                                           class="input"
                                           @required($field->required)
                                           value="{{ old('fields.'.$field->key) }}"
                                           placeholder="{{ $field->placeholder }}">
                                @endif

                                @if ($field->help)
                                    <span class="field__hint">{{ $field->help }}</span>
                                @endif

                                <x-field-error :name="'fields.'.$field->key" />
                            </div>
                        @endforeach

                        <div class="field">
                            <label for="case_summary">¿Qué pasó y qué necesitas? <span class="req">*</span></label>
                            <textarea id="case_summary" name="case_summary" class="textarea" required minlength="30"
                                      style="min-height: 190px;"
                                      placeholder="Ejemplo: el 14 de enero radiqué la solicitud 00123 ante mi EPS para una cirugía de rodilla. El 3 de febrero me la negaron diciendo que no está en el PBS. Necesito que la autoricen.">{{ old('case_summary') }}</textarea>
                            <span class="field__hint">Incluye fechas, nombres de entidades y números de radicado si los tienes.</span>
                            <x-field-error name="case_summary" />
                        </div>

                        @if ($document->required_info)
                            <div class="alert alert--info">
                                <span class="alert__icon" aria-hidden="true">📋</span>
                                <div>
                                    <strong>Para este trámite necesitaremos:</strong>
                                    <ul style="margin: var(--s2) 0 0; padding-left: var(--s5); font-size: var(--t-sm);">
                                        @foreach ($document->required_info as $info)<li>{{ $info }}</li>@endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <h3 style="font-size: var(--t-lg); margin-top: var(--s6); margin-bottom: 0;">Tus datos</h3>

                        <div class="form-row">
                            <div class="field">
                                <label for="customer_name">Nombre completo <span class="req">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" class="input" required
                                       value="{{ old('customer_name') }}" autocomplete="name">
                                <x-field-error name="customer_name" />
                            </div>
                            <div class="field">
                                <label for="customer_email">Correo <span class="req">*</span></label>
                                <input type="email" id="customer_email" name="customer_email" class="input" required
                                       value="{{ old('customer_email') }}" autocomplete="email">
                                <span class="field__hint">Aquí llega tu documento.</span>
                                <x-field-error name="customer_email" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label for="customer_phone">Celular (WhatsApp) <span class="req">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" class="input" required
                                       value="{{ old('customer_phone') }}" autocomplete="tel" placeholder="300 123 4567">
                                <x-field-error name="customer_phone" />
                            </div>
                            <div class="field">
                                <label for="customer_city">Ciudad</label>
                                <input type="text" id="customer_city" name="customer_city" class="input"
                                       value="{{ old('customer_city') }}" autocomplete="address-level2">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label for="customer_document_type">Tipo de documento <span class="req">*</span></label>
                                <select id="customer_document_type" name="customer_document_type" class="select" required>
                                    @foreach (['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'NIT' => 'NIT', 'PA' => 'Pasaporte', 'PEP' => 'PEP / PPT'] as $v => $l)
                                        <option value="{{ $v }}" @selected(old('customer_document_type', 'CC') === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="customer_document_number">Número de documento</label>
                                <input type="text" id="customer_document_number" name="customer_document_number"
                                       class="input" value="{{ old('customer_document_number') }}" inputmode="numeric">
                            </div>
                        </div>

                        <hr>

                        <label class="checkbox">
                            <input type="checkbox" name="accepts_terms" value="1" required @checked(old('accepts_terms'))>
                            <span>Acepto los <a href="{{ route('pages.terms') }}" target="_blank">términos y condiciones</a>. <span class="req">*</span></span>
                        </label>
                        <x-field-error name="accepts_terms" />

                        <label class="checkbox">
                            <input type="checkbox" name="accepts_privacy" value="1" required @checked(old('accepts_privacy'))>
                            <span>
                                Autorizo el tratamiento de mis datos conforme a la
                                <a href="{{ route('pages.privacy') }}" target="_blank">política de privacidad</a>
                                y la Ley 1581 de 2012. <span class="req">*</span>
                            </span>
                        </label>
                        <x-field-error name="accepts_privacy" />

                        {{-- Consentimientos separados y opcionales: uno no implica el otro. --}}
                        <label class="checkbox">
                            <input type="checkbox" name="shareable_with_lawyers" value="1" @checked(old('shareable_with_lawyers'))>
                            <span>
                                <strong>Quiero que un abogado me contacte</strong> sobre este caso.
                                Compartiremos tu caso solo con abogados de nuestra red y puedes revocarlo cuando quieras.
                            </span>
                        </label>

                        <label class="checkbox">
                            <input type="checkbox" name="accepts_marketing" value="1" @checked(old('accepts_marketing'))>
                            <span>Quiero recibir guías legales gratuitas por correo. Puedo darme de baja en cualquier momento.</span>
                        </label>
                    </div>
                </div>

                {{-- ---------------- Resumen ---------------- --}}
                <aside class="checkout-aside">
                    <div class="card summary">
                        <h2 style="font-size: var(--t-base); margin-bottom: var(--s5);">Tu pedido</h2>

                        <dl class="summary__rows">
                            <div class="summary__row">
                                <dt>{{ $document->short_name ?: $document->name }}</dt>
                                <dd data-base-price>{{ $quote->formattedBase() }}</dd>
                            </div>
                            <div data-addon-lines></div>
                            <div class="summary__row">
                                <dt>Entrega</dt>
                                <dd data-delivery>{{ $quote->deliveryLabel() }}</dd>
                            </div>
                        </dl>

                        <div class="summary__total">
                            <span>Total</span>
                            <span class="price-live">
                                <span class="price-live__total" data-total>{{ $quote->formattedTotal() }}</span>
                            </span>
                        </div>

                        <button type="submit" class="btn btn--primary btn--block btn--lg summary__submit" style="margin-top: var(--s6);">
                            <span data-cta>{{ $quote->isFree() ? 'Generar mi documento gratis' : 'Continuar al pago' }}</span>
                        </button>

                        <p class="text-xs text-muted center" style="margin-top: var(--s4);">
                            🛡️ Cubierto por el <a href="{{ route('pages.guarantee') }}" target="_blank">Certificado de Garantía</a>
                        </p>
                    </div>

                    <div class="card card--flat" style="margin-top: var(--s5); background: var(--cream-100);">
                        <p class="text-xs text-muted mb-0">
                            <strong>¿Dudas antes de empezar?</strong><br>
                            Escríbenos a <a href="{{ $wa->forDocument($document->name) }}" target="_blank" rel="noopener">{{ $wa->display() }}</a>.
                        </p>
                    </div>
                </aside>
            </div>

            {{-- Barra fija en móvil: el resumen va arriba y sin esto el formulario
                 termina sin botón.

                 Se despliega porque en el teléfono los adicionales ocupan
                 varias pantallas: al llegar abajo el total ha subido y ya no
                 se ve por qué. Va cerrada por defecto y con `<details>`
                 nativo, así que abrirla no recarga, no mueve el scroll y
                 funciona sin JS. --}}
            <div class="checkout-spacer" aria-hidden="true"></div>
            <div class="checkout-bar">
                <details class="checkout-bar__detail">
                    <summary>
                        <span data-bar-count>Ver qué incluye</span>
                        <span class="checkout-bar__chevron" aria-hidden="true"></span>
                    </summary>

                    <div class="checkout-bar__panel">
                        <dl class="summary__rows">
                            <div class="summary__row">
                                <dt>{{ $document->short_name ?: $document->name }}</dt>
                                <dd>{{ $quote->formattedBase() }}</dd>
                            </div>
                            <div data-addon-lines></div>
                            <div class="summary__row">
                                <dt>Entrega</dt>
                                <dd data-delivery>{{ $quote->deliveryLabel() }}</dd>
                            </div>
                        </dl>
                    </div>
                </details>

                <div class="checkout-bar__row">
                    <span class="checkout-bar__total">
                        <span>Total</span>
                        <strong data-bar-total>{{ $quote->formattedTotal() }}</strong>
                    </span>
                    <button type="submit" class="btn btn--primary">
                        <span data-cta>{{ $quote->isFree() ? 'Generar gratis' : 'Continuar' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
/* Recalcula el total al marcar adicionales. Sin peticiones al servidor: el
   precio de cada uno viaja en data-price, y el servidor vuelve a calcularlo
   al guardar, así que esto es solo la vista. */
(function () {
    var form = document.getElementById('checkout-form');
    if (!form) return;

    var base = {{ $quote->baseAmount }};
    var boxes = form.querySelectorAll('input[name="addons[]"]');
    var totals = form.querySelectorAll('[data-total], [data-bar-total]');
    var ctas = form.querySelectorAll('[data-cta]');
    // Dos resúmenes: el de la columna y el de la barra de móvil.
    var lines = form.querySelectorAll('[data-addon-lines]');
    var deliveries = form.querySelectorAll('[data-delivery]');
    var counts = form.querySelectorAll('[data-bar-count]');

    var standardDelivery = @json($document->deliveryLabel());
    var expressDelivery = 'Ahora mismo';

    function cop(n) {
        return n === 0 ? 'Gratis' : '$' + n.toLocaleString('es-CO');
    }

    function update() {
        var total = base;
        var html = '';
        var express = false;
        var cuantos = 0;

        boxes.forEach(function (box) {
            if (!box.checked) return;
            var price = parseInt(box.dataset.price, 10) || 0;
            total += price;
            cuantos++;
            if (box.value === 'express') express = true;

            var label = box.closest('.addon').querySelector('.addon__name').textContent.trim();
            html += '<div class="summary__row"><dt>' + label + '</dt><dd>' +
                    (price === 0 ? 'Incluido' : cop(price)) + '</dd></div>';
        });

        lines.forEach(function (el) { el.innerHTML = html; });
        deliveries.forEach(function (el) {
            el.textContent = express ? expressDelivery : standardDelivery;
        });

        // El número en la barra responde sin abrirla a «¿qué llevo?».
        counts.forEach(function (el) {
            el.textContent = cuantos === 0
                ? 'Ver qué incluye'
                : (cuantos === 1 ? '1 adicional · Ver detalle' : cuantos + ' adicionales · Ver detalle');
        });

        totals.forEach(function (el) { el.textContent = cop(total); });
        ctas.forEach(function (el) {
            el.textContent = total === 0
                ? (el.closest('.checkout-bar') ? 'Generar gratis' : 'Generar mi documento gratis')
                : (el.closest('.checkout-bar') ? 'Continuar' : 'Continuar al pago');
        });
    }

    /* La entrega inmediata y que lo escriba un abogado se excluyen: nadie
       redacta sobre hechos ajenos al instante. Al marcar una se quita la
       otra y se dice por qué, en vez de esconder la opción: una casilla que
       desaparece sin explicación se lee como un error de la página. */
    var INCOMPATIBLES = { review: 'delivery', delivery: 'review' };

    function excluir(elegido) {
        var contraria = INCOMPATIBLES[elegido.dataset.kind];
        if (!contraria || !elegido.checked) return;

        var quitados = [];

        boxes.forEach(function (box) {
            if (box === elegido || box.dataset.kind !== contraria || !box.checked) return;

            box.checked = false;
            quitados.push(box.closest('.addon').querySelector('.addon__name').textContent.trim());
        });

        if (quitados.length) {
            anunciar(quitados.join(' y '), elegido.closest('.addon').querySelector('.addon__name').textContent.trim());
        }
    }

    function anunciar(quitado, motivo) {
        var aviso = document.getElementById('aviso-incompatible');
        if (!aviso) return;

        aviso.querySelector('[data-texto]').innerHTML =
            'Quitamos <strong>' + quitado + '</strong> porque no se puede combinar con <strong>' +
            motivo + '</strong>: un documento que escribe un abogado sobre tu caso no puede estar ' +
            'listo al instante. Puedes volver a marcarlo y se quitará el otro.';

        aviso.hidden = false;
    }

    boxes.forEach(function (box) {
        box.addEventListener('change', function () {
            excluir(box);
            update();
        });
    });

    /* Al continuar, el detalle se cierra.
       Si faltan datos el navegador salta al primer campo vacío, y con el
       panel abierto ese salto ocurre detrás de un panel que tapa un tercio
       de la pantalla: parece que el botón no hizo nada. Va en el `click` y
       no en el `submit` porque cuando la validación falla no hay submit. */
    var detalle = form.querySelector('.checkout-bar__detail');

    if (detalle) {
        form.addEventListener('click', function (e) {
            if (e.target.closest('.checkout-bar__row button')) detalle.open = false;
        });

        form.addEventListener('submit', function () { detalle.open = false; });
    }

    update();
})();
</script>
@endpush
@endsection
