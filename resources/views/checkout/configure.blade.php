@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $hasSpeedChoice = $expressQuote !== null;
    $chosen = old('speed', $selectedSpeed->value);
@endphp

<section class="section section--white">
    <div class="container">
        <x-wizard :step="$step" />

        <div class="section-head">
            <span class="eyebrow">{{ $plan->name }}</span>
            <h1>{{ $document->name }}</h1>
            <p>{{ $document->tagline ?: $document->summary }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert--danger" style="max-width: 760px; margin: 0 auto var(--s8);">
                <span class="alert__icon" aria-hidden="true">!</span>
                <div>
                    <strong>Revisa estos datos antes de continuar:</strong>
                    <ul style="margin: var(--s2) 0 0; padding-left: var(--s5);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('checkout.store', [$plan->key->value, $document->slug]) }}" method="post">
            @csrf

            <div class="checkout-layout">
                <div>
                    {{-- ---------------- Velocidad de entrega: las 2 opciones ---------------- --}}
                    @if ($hasSpeedChoice)
                        <h2 style="font-size: var(--t-xl);">1. ¿Para cuándo lo necesitas?</h2>
                        <p class="text-sm text-muted">Es el mismo documento y el mismo abogado. Lo único que cambia es la fila.</p>

                        <div class="choice-grid" style="margin-bottom: var(--s10);">
                            <label class="choice">
                                <input type="radio" name="speed" value="standard"
                                       @checked($chosen === 'standard') data-speed="standard">
                                <span class="choice__box">
                                    <span class="choice__head">
                                        <span class="choice__title">Estándar</span>
                                        <span class="choice__radio" aria-hidden="true"></span>
                                    </span>
                                    <span class="choice__price">{{ $pricing->formatCop($standardQuote->amount) }}</span>
                                    <span class="choice__meta">Entrega en {{ \Illuminate\Support\Str::lcfirst($standardQuote->deliveryLabel()) }}</span>
                                    <ul class="choice__list">
                                        <li>Formato escrito por abogados colombianos</li>
                                        <li>Guía de radicación incluida</li>
                                        <li>Envío por correo y WhatsApp</li>
                                    </ul>
                                </span>
                            </label>

                            <label class="choice">
                                <input type="radio" name="speed" value="express"
                                       @checked($chosen === 'express') data-speed="express">
                                <span class="choice__box choice__box--action">
                                    <span class="choice__head">
                                        <span class="choice__title">Express ⚡</span>
                                        <span class="choice__radio" aria-hidden="true"></span>
                                    </span>
                                    <span class="choice__price">{{ $pricing->formatCop($expressQuote->amount) }}</span>
                                    <span class="choice__meta">
                                        Entrega en {{ \Illuminate\Support\Str::lcfirst($expressQuote->deliveryLabel()) }}
                                        @if ($upgradeCost)
                                            · solo {{ $pricing->formatCop($upgradeCost) }} más
                                        @endif
                                    </span>
                                    <ul class="choice__list">
                                        <li>Todo lo del plan estándar</li>
                                        <li><strong>Tu trámite salta al inicio de la fila</strong></li>
                                        <li>Soporte directo mientras se redacta</li>
                                    </ul>
                                </span>
                            </label>
                        </div>
                    @else
                        <input type="hidden" name="speed" value="standard">
                    @endif

                    {{-- ---------------- Datos del caso ---------------- --}}
                    <h2 style="font-size: var(--t-xl);">{{ $hasSpeedChoice ? '2' : '1' }}. Cuéntanos tu caso</h2>
                    <p class="text-sm text-muted">
                        Esto es lo que lee el abogado antes de redactar. Mientras más concreto, mejor queda el documento.
                    </p>

                    <div class="form" style="margin-top: var(--s6);">
                        <div class="field">
                            <label for="case_summary">
                                ¿Qué pasó y qué necesitas? <span class="req" aria-hidden="true">*</span>
                            </label>
                            <textarea id="case_summary" name="case_summary" class="textarea" required minlength="30"
                                      aria-describedby="case_summary_hint"
                                      aria-invalid="{{ $errors->has('case_summary') ? 'true' : 'false' }}"
                                      placeholder="Ejemplo: el 14 de enero mi médico ordenó una cirugía de rodilla. Radiqué la autorización el 20 de enero y el 3 de febrero la EPS la negó diciendo que no está en el PBS. Necesito que me la autoricen.">{{ old('case_summary') }}</textarea>
                            <span class="field__hint" id="case_summary_hint">
                                Incluye fechas, nombres de entidades y números de radicado si los tienes.
                            </span>
                            <x-field-error name="case_summary" />
                        </div>

                        @if ($document->required_info)
                            <div class="alert alert--info">
                                <span class="alert__icon" aria-hidden="true">📋</span>
                                <div>
                                    <strong>Para este trámite necesitaremos:</strong>
                                    <ul style="margin: var(--s2) 0 0; padding-left: var(--s5); font-size: var(--t-sm);">
                                        @foreach ($document->required_info as $info)
                                            <li>{{ $info }}</li>
                                        @endforeach
                                    </ul>
                                    <p style="margin-top: var(--s3); font-size: var(--t-xs);">
                                        Si no lo tienes todo a mano, no te preocupes: después de pagar te escribimos
                                        por WhatsApp para completar lo que falte.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <h3 style="font-size: var(--t-lg); margin-top: var(--s6); margin-bottom: 0;">Tus datos</h3>

                        <div class="form-row">
                            <div class="field">
                                <label for="customer_name">Nombre completo <span class="req" aria-hidden="true">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" class="input" required
                                       value="{{ old('customer_name') }}" autocomplete="name"
                                       aria-invalid="{{ $errors->has('customer_name') ? 'true' : 'false' }}"
                                       placeholder="Como aparece en tu documento">
                                <x-field-error name="customer_name" />
                            </div>

                            <div class="field">
                                <label for="customer_email">Correo electrónico <span class="req" aria-hidden="true">*</span></label>
                                <input type="email" id="customer_email" name="customer_email" class="input" required
                                       value="{{ old('customer_email') }}" autocomplete="email"
                                       aria-describedby="email_hint"
                                       aria-invalid="{{ $errors->has('customer_email') ? 'true' : 'false' }}">
                                <span class="field__hint" id="email_hint">Aquí llega tu documento. Revísalo bien.</span>
                                <x-field-error name="customer_email" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label for="customer_phone">Celular (WhatsApp) <span class="req" aria-hidden="true">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" class="input" required
                                       value="{{ old('customer_phone') }}" autocomplete="tel"
                                       placeholder="300 123 4567"
                                       aria-invalid="{{ $errors->has('customer_phone') ? 'true' : 'false' }}">
                                <x-field-error name="customer_phone" />
                            </div>

                            <div class="field">
                                <label for="customer_city">Ciudad</label>
                                <input type="text" id="customer_city" name="customer_city" class="input"
                                       value="{{ old('customer_city') }}" autocomplete="address-level2">
                                <x-field-error name="customer_city" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label for="customer_document_type">Tipo de documento <span class="req" aria-hidden="true">*</span></label>
                                <select id="customer_document_type" name="customer_document_type" class="select" required>
                                    @foreach (['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'NIT' => 'NIT', 'PA' => 'Pasaporte', 'PEP' => 'PEP / PPT'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('customer_document_type', 'CC') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <label for="customer_document_number">Número de documento</label>
                                <input type="text" id="customer_document_number" name="customer_document_number" class="input"
                                       value="{{ old('customer_document_number') }}" inputmode="numeric">
                                <span class="field__hint">Va dentro del documento legal, por eso lo pedimos.</span>
                                <x-field-error name="customer_document_number" />
                            </div>
                        </div>

                        @if ($document->supports_signature)
                            <label class="checkbox">
                                <input type="checkbox" name="wants_signature" value="1" @checked(old('wants_signature'))>
                                <span>
                                    <strong>Quiero firma electrónica con OTP</strong> para que las partes firmen desde el celular.
                                    Sin costo adicional, con validez plena según la Ley 527 de 1999.
                                </span>
                            </label>
                        @endif

                        <hr>

                        <label class="checkbox">
                            <input type="checkbox" name="accepts_terms" value="1" required @checked(old('accepts_terms'))>
                            <span>
                                Acepto los <a href="{{ route('pages.terms') }}" target="_blank">términos y condiciones</a>
                                y entiendo que esto no constituye una relación abogado&#8209;cliente hasta la confirmación del pago.
                                <span class="req" aria-hidden="true">*</span>
                            </span>
                        </label>
                        <x-field-error name="accepts_terms" />

                        <label class="checkbox">
                            <input type="checkbox" name="accepts_privacy" value="1" required @checked(old('accepts_privacy'))>
                            <span>
                                Autorizo el tratamiento de mis datos personales conforme a la
                                <a href="{{ route('pages.privacy') }}" target="_blank">política de privacidad</a>
                                y la Ley 1581 de 2012. <span class="req" aria-hidden="true">*</span>
                            </span>
                        </label>
                        <x-field-error name="accepts_privacy" />
                    </div>
                </div>

                {{-- ---------------- Resumen lateral ---------------- --}}
                <aside class="checkout-aside">
                    <div class="card summary">
                        <h2 style="font-size: var(--t-base); margin-bottom: var(--s5);">Resumen de tu pedido</h2>

                        <dl class="summary__rows">
                            <div class="summary__row">
                                <dt>Trámite</dt>
                                <dd>{{ $document->short_name ?: $document->name }}</dd>
                            </div>
                            <div class="summary__row">
                                <dt>Plan</dt>
                                <dd>{{ $plan->name }}</dd>
                            </div>
                            <div class="summary__row">
                                <dt>Entrega</dt>
                                <dd data-summary-delivery>
                                    {{ $chosen === 'express' && $expressQuote ? $expressQuote->deliveryLabel() : $standardQuote->deliveryLabel() }}
                                </dd>
                            </div>
                        </dl>

                        <div class="summary__total">
                            <span>Total</span>
                            <span data-summary-total
                                  data-standard="{{ $pricing->formatCop($standardQuote->amount) }}"
                                  data-express="{{ $expressQuote ? $pricing->formatCop($expressQuote->amount) : '' }}"
                                  data-standard-delivery="{{ $standardQuote->deliveryLabel() }}"
                                  data-express-delivery="{{ $expressQuote?->deliveryLabel() }}">
                                {{ $chosen === 'express' && $expressQuote ? $pricing->formatCop($expressQuote->amount) : $pricing->formatCop($standardQuote->amount) }}
                            </span>
                        </div>

                        <button type="submit" class="btn btn--primary btn--block btn--lg summary__submit" style="margin-top: var(--s6);">
                            Continuar al pago
                        </button>

                        <p class="text-xs text-muted center" style="margin-top: var(--s4);">
                            🛡️ Cubierto por el <a href="{{ route('pages.guarantee') }}" target="_blank">Certificado de Garantía</a>.
                            Aún no se te cobra nada.
                        </p>
                    </div>

                    <div class="card card--flat" style="margin-top: var(--s5); background: var(--slate-50);">
                        <p class="text-xs text-muted mb-0">
                            <strong>¿Prefieres hablar antes de pagar?</strong><br>
                            Escríbenos por WhatsApp a
                            <a href="{{ $wa->forDocument($document->name) }}" target="_blank" rel="noopener">{{ $wa->display() }}</a>
                            y te orientamos sin compromiso.
                        </p>
                    </div>
                </aside>
            </div>

            {{-- Hueco y barra fija: sólo se ven en móvil. --}}
            <div class="checkout-spacer" aria-hidden="true"></div>

            <div class="checkout-bar">
                <span class="checkout-bar__total">
                    <span>Total</span>
                    <strong data-bar-total>
                        {{ $chosen === 'express' && $expressQuote ? $pricing->formatCop($expressQuote->amount) : $pricing->formatCop($standardQuote->amount) }}
                    </strong>
                </span>
                <button type="submit" class="btn btn--primary">Continuar al pago</button>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
/* El resumen se actualiza al cambiar la velocidad, sin recargar la página. */
(function () {
    var total = document.querySelector('[data-summary-total]');
    var barTotal = document.querySelector('[data-bar-total]');
    var delivery = document.querySelector('[data-summary-delivery]');
    var radios = document.querySelectorAll('input[name="speed"]');

    if (!total || !radios.length) return;

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            var express = radio.value === 'express';
            var price = express ? total.dataset.express : total.dataset.standard;
            var when = express ? total.dataset.expressDelivery : total.dataset.standardDelivery;

            if (price) {
                total.textContent = price;
                if (barTotal) barTotal.textContent = price;
            }
            if (when && delivery) delivery.textContent = when;
        });
    });
})();
</script>
@endpush
@endsection
