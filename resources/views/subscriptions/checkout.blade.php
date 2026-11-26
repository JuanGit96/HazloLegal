@extends('signature.layout')

@section('title', 'Activar mi plan')

@section('content')
<div class="sign__head">
    <span class="logo" style="justify-content: center;">
        <span class="logo__word">Hazlo</span><span class="logo__hl">Legal</span>
    </span>
    <h1>Activa <span class="hl hl--blue">tu plan</span></h1>
    <p>{{ $lawyer->name }}</p>
</div>

@if (session('paymentError'))
    <div class="alert alert--danger" style="margin-bottom: var(--s6);">
        <span class="alert__icon" aria-hidden="true">!</span>
        <div>{{ session('paymentError') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert--danger" style="margin-bottom: var(--s6);">
        <span class="alert__icon" aria-hidden="true">!</span>
        <div>
            <ul style="margin: 0; padding-left: var(--s5);">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<div class="card card--pad-lg">
    <h2 style="font-size: var(--t-lg); margin-bottom: var(--s2);">{{ $plan->name }}</h2>
    <p class="text-sm text-muted">{{ $plan->tagline }}</p>

    {{-- ---------------- Qué paga hoy y qué pagará después ----------------
         Las dos cifras juntas y con fecha. Un «gratis» que no dice qué pasa
         el día que se acaba es la receta de la queja, y además es publicidad
         engañosa. --}}
    @if ($quote->hasPromo())
        <div class="promo-box">
            <span class="pill pill--action">{{ $quote->promo?->isInvitation() ? 'Invitación' : 'Precio de lanzamiento' }}</span>
            <strong class="promo-box__headline">{{ $quote->headline() }}</strong>
            @if ($quote->promo?->tagline)
                <p class="text-sm text-muted" style="margin: var(--s2) 0 0;">{{ $quote->promo->tagline }}</p>
            @endif
        </div>
    @endif

    <div class="price-live" style="margin: var(--s6) 0 var(--s2);">
        @if ($quote->hasPromo() && $quote->dueToday < $quote->listPrice)
            <s class="text-muted" style="font-size: var(--t-lg);">{{ $quote->formattedListPrice() }}</s>
        @endif
        <span class="price-live__total">{{ $quote->formattedDueToday() }}</span>
        @if ($quote->dueToday > 0)
            <span class="text-sm text-muted">/ {{ $quote->periodLabel() }}</span>
        @else
            <span class="text-sm text-muted">hoy</span>
        @endif
    </div>

    <dl class="summary__rows" style="margin-bottom: var(--s6);">
        <div class="summary__row">
            <dt>Hoy pagas</dt>
            <dd>{{ $quote->formattedDueToday() }}</dd>
        </div>
        @if ($quote->freeMonths > 0)
            <div class="summary__row">
                <dt>Primer cobro</dt>
                <dd>{{ $quote->firstChargeAt?->translatedFormat('j \d\e F \d\e Y') }}</dd>
            </div>
        @endif
        @if ($quote->discountMonths > 0)
            <div class="summary__row">
                <dt>Precio con descuento</dt>
                <dd>{{ $quote->formattedPromoPrice() }} / {{ $quote->periodLabel() }} ({{ $quote->discountMonths }} {{ $quote->discountMonths === 1 ? 'periodo' : 'periodos' }})</dd>
            </div>
        @endif
        <div class="summary__row">
            <dt>Después</dt>
            <dd>
                {{ $quote->formattedRecurringPrice() }} / {{ $quote->periodLabel() }}
                @if ($quote->fullPriceFrom)
                    <small class="text-muted">desde el {{ $quote->fullPriceFrom->translatedFormat('j \d\e F \d\e Y') }}</small>
                @endif
            </dd>
        </div>
        @if ($quote->savings() > 0)
            <div class="summary__row">
                <dt>Te ahorras</dt>
                <dd><strong>{{ $quote->formattedSavings() }}</strong></dd>
            </div>
        @endif
    </dl>

    @if ($period->value === 'monthly' && $plan->price_annual)
        <p class="text-sm">
            <a href="{{ route('subscription.checkout', ['plan' => $plan->id, 'periodo' => 'annual']) }}">
                Pagar un año y ahorrar {{ \App\Domain\Catalog\Data\Quote::cop($plan->annualSaving()) }}
            </a>
        </p>
    @elseif ($period->value === 'annual')
        <p class="text-sm">
            <a href="{{ route('subscription.checkout', ['plan' => $plan->id, 'periodo' => 'monthly']) }}">
                Prefiero pagar mes a mes
            </a>
        </p>
    @endif

    @if ($plan->features)
        <ul class="plan__features">
            @foreach ($plan->features as $feature)
                <li>{{ $feature }}</li>
            @endforeach
        </ul>
    @endif
</div>

{{-- ---------------- La tarjeta ----------------
     Los campos están en nuestra página pero los datos no pasan por nuestro
     servidor: el navegador los manda directo a Wompi con la llave pública y
     recibe un token de un solo uso. Lo que se envía aquí es ese token. --}}
@if ($acceptance)
    <div class="card card--pad-lg mt-6" id="tarjeta">
        <h2 style="font-size: var(--t-lg);">
            {{ $quote->startsFree() ? 'Registra tu tarjeta para empezar' : 'Paga con tarjeta' }}
        </h2>
        <p class="text-sm text-muted">
            Débito o crédito. La suscripción solo acepta tarjeta porque es lo único que
            permite cobrar la renovación sola; con Nequi o PSE tendrías que acordarte tú
            cada periodo.
        </p>

        @if ($quote->startsFree())
            {{-- El cobro de verificación se anuncia antes de hacerlo. Un cargo
                 que nadie avisó acaba en una disputa con el banco, y eso
                 cuesta mucho más que el mes que se iba a cobrar. --}}
            <div class="alert alert--info" style="margin: var(--s5) 0;">
                <span class="alert__icon" aria-hidden="true">💳</span>
                <div>
                    <strong>Haremos un cobro de {{ $verifier->formattedAmount() }} y lo anularemos enseguida.</strong>
                    <p style="margin-top: var(--s1);">
                        Es la única forma de saber que tu tarjeta funciona de verdad: sin esto
                        nos enteraríamos dentro de {{ $quote->freeMonths }} meses, cuando toque
                        el primer cobro. No te quedamos debiendo nada. Si alcanza a aparecer en
                        tu extracto, se cae solo; en débito algunos bancos tardan un par de días
                        en devolverlo.
                    </p>
                </div>
            </div>
        @endif

        @if ($subscription->hasStoredCard())
            <div class="alert alert--success" style="margin: var(--s5) 0;">
                <span class="alert__icon" aria-hidden="true">💳</span>
                <div>
                    <strong>Ya tienes una tarjeta registrada: {{ $subscription->cardLabel() }}.</strong>
                    <p style="margin-top: var(--s1);">
                        Si quieres cambiarla, llena el formulario con la nueva y reemplaza la anterior.
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('subscription.card') }}" method="post" class="form" id="form-tarjeta" novalidate>
            @csrf
            <input type="hidden" name="plan" value="{{ $plan->id }}">
            <input type="hidden" name="periodo" value="{{ $period->value }}">
            <input type="hidden" name="invitacion" value="{{ $quote->promo?->code }}">
            <input type="hidden" name="card_token" id="card_token">

            <div class="field">
                <label for="card_number">Número de la tarjeta <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="card_number" class="input mono" inputmode="numeric" autocomplete="cc-number"
                       placeholder="4242 4242 4242 4242" maxlength="23" data-card="number">
            </div>

            <div class="field">
                <label for="card_holder">Nombre como aparece en la tarjeta <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="card_holder" class="input" autocomplete="cc-name"
                       value="{{ $lawyer->name }}" maxlength="80" data-card="holder">
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="card_exp">Vence (MM/AA) <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="card_exp" class="input mono" inputmode="numeric" autocomplete="cc-exp"
                           placeholder="08/29" maxlength="5" data-card="exp">
                </div>
                <div class="field">
                    <label for="card_cvc">Código de seguridad <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="card_cvc" class="input mono" inputmode="numeric" autocomplete="cc-csc"
                           placeholder="123" maxlength="4" data-card="cvc">
                </div>
            </div>

            <label class="checkbox">
                <input type="checkbox" name="accepts_recurring" value="1" required>
                <span>
                    Autorizo a {{ config('hazlolegal.brand.legal_name') }} a cobrar en esta tarjeta
                    @if ($quote->startsFree())
                        <strong>{{ $verifier->formattedAmount() }} ahora para comprobarla, que se anula
                        enseguida</strong>, y
                        <strong>{{ $quote->formattedRecurringPrice() }} cada {{ $quote->periodLabel() }} a partir del
                        {{ $quote->firstChargeAt?->translatedFormat('j \d\e F \d\e Y') }}</strong>,
                    @else
                        <strong>{{ $quote->formattedDueToday() }} hoy y la renovación de cada {{ $quote->periodLabel() }}</strong>,
                    @endif
                    hasta que yo cancele desde el portal. Acepto el
                    <a href="{{ $acceptance['permalink'] }}" target="_blank" rel="noopener">reglamento de Wompi</a>
                    @if (!empty($acceptance['personal_data_permalink']))
                        y la
                        <a href="{{ $acceptance['personal_data_permalink'] }}" target="_blank" rel="noopener">autorización de tratamiento de datos</a>
                    @endif
                    y la <a href="{{ route('pages.privacy') }}" target="_blank">política de datos de HazloLegal</a>.
                    <span class="req" aria-hidden="true">*</span>
                </span>
            </label>

            <p class="text-xs text-muted" id="error-tarjeta" role="alert" aria-live="polite" hidden></p>

            <button type="submit" class="btn btn--primary btn--block btn--lg" id="btn-tarjeta">
                {{ $quote->startsFree()
                    ? 'Comprobar mi tarjeta y empezar'
                    : 'Pagar '.$quote->formattedDueToday().' y activar' }}
            </button>
        </form>

        <p class="text-xs text-muted" style="margin-top: var(--s4);">
            Comprobamos la tarjeta con un cobro real contra tu banco, que es lo que descarta un
            número inventado, una tarjeta sin cupo o un débito no habilitado para compras en
            línea. Que hoy la apruebe no garantiza que apruebe todos los cobros futuros: si
            alguno falla te escribimos antes de suspender nada.
        </p>

        <div class="pay-badges">
            <span>🔒 Cifrado de punta a punta</span>
            <span>💳 Débito y crédito</span>
            <span>🚫 No guardamos tu número</span>
        </div>

        @if ($wompi->isSandbox())
            <div class="alert alert--warn" style="margin-top: var(--s6);">
                <span class="alert__icon" aria-hidden="true">🧪</span>
                <div>
                    <strong>Modo de pruebas.</strong>
                    <p style="margin-top: var(--s1);">
                        Ningún cobro es real. Tarjeta <strong class="mono">4242 4242 4242 4242</strong>,
                        cualquier fecha futura, CVC <strong class="mono">123</strong>.
                    </p>
                </div>
            </div>
        @endif

        <noscript>
            <div class="alert alert--info" style="margin-top: var(--s6);">
                <span class="alert__icon" aria-hidden="true">ℹ️</span>
                <div>
                    <strong>Necesitas JavaScript para registrar la tarjeta.</strong>
                    <p style="margin-top: var(--s1);">
                        Es a propósito: así los datos de tu tarjeta van de tu navegador a la pasarela
                        sin pasar por nuestros servidores. Si lo tienes desactivado, escríbenos a
                        {{ config('hazlolegal.contact.email') }} y te activamos el plan a mano.
                    </p>
                </div>
            </div>
        </noscript>
    </div>
@endif

{{-- Nequi, PSE y transferencia no aparecen a propósito: ninguno deja nada
     con qué cobrar el periodo siguiente, y una suscripción que hay que
     perseguir a mano cada mes no es una suscripción. --}}
@if (! $acceptance)
    <div class="card card--pad-lg mt-6">
        <div class="alert alert--info">
            <span class="alert__icon" aria-hidden="true">ℹ️</span>
            <div>
                <strong>Pago no disponible todavía.</strong>
                <p style="margin-top: var(--s1);">
                    Escríbenos a {{ config('hazlolegal.contact.email') }} y lo activamos a mano.
                </p>
            </div>
        </div>
    </div>
@endif

<p class="sign__legal">
    No guardamos tu número de tarjeta: lo custodia Wompi y aquí solo queda
    {{ $subscription->cardLabel() ?: 'la marca y los últimos cuatro dígitos' }}.
    Puedes cancelar la suscripción cuando quieras desde el portal y no se vuelve a cobrar.
</p>

<div class="center mt-8">
    <a href="{{ url('/portal') }}" class="btn btn--outline">Volver al portal</a>
</div>
@endsection

@push('scripts')
<script>
/*
    La tokenización va directa del navegador a Wompi con la llave pública: el
    número de la tarjeta no toca nuestro servidor, que es exactamente lo que
    no queremos custodiar. Lo que se envía en el formulario es el token.
*/
(function () {
    var form = document.getElementById('form-tarjeta');
    if (!form) return;

    var boton = document.getElementById('btn-tarjeta');
    var error = document.getElementById('error-tarjeta');
    var etiquetaOriginal = boton.textContent;
    var API = @json(rtrim($wompi->apiUrl(), '/').'/tokens/cards');
    var LLAVE = @json((string) config('wompi.public_key'));

    function campo(nombre) {
        return form.querySelector('[data-card="' + nombre + '"]');
    }

    function avisar(mensaje) {
        error.textContent = mensaje;
        error.hidden = !mensaje;
        error.classList.toggle('field__error', !!mensaje);
    }

    // Separar en grupos de cuatro mientras se escribe: es la forma en que
    // está impresa la tarjeta, y así se puede comparar de un vistazo.
    campo('number').addEventListener('input', function () {
        var digitos = this.value.replace(/\D/g, '').slice(0, 19);
        this.value = digitos.replace(/(.{4})/g, '$1 ').trim();
    });

    campo('exp').addEventListener('input', function () {
        var digitos = this.value.replace(/\D/g, '').slice(0, 4);
        this.value = digitos.length > 2 ? digitos.slice(0, 2) + '/' + digitos.slice(2) : digitos;
    });

    campo('cvc').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    form.addEventListener('submit', function (evento) {
        if (document.getElementById('card_token').value) return;

        evento.preventDefault();
        avisar('');

        var vence = campo('exp').value.split('/');
        var datos = {
            number: campo('number').value.replace(/\s/g, ''),
            cvc: campo('cvc').value,
            exp_month: (vence[0] || '').padStart(2, '0'),
            exp_year: (vence[1] || '').slice(-2),
            card_holder: campo('holder').value.trim()
        };

        if (!datos.number || !datos.cvc || datos.exp_month.length !== 2 || datos.exp_year.length !== 2 || !datos.card_holder) {
            avisar('Completa los cuatro campos de la tarjeta.');
            return;
        }

        boton.disabled = true;
        boton.textContent = 'Validando la tarjeta…';

        fetch(API, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + LLAVE, 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (cuerpo) {
                var token = cuerpo && cuerpo.data && cuerpo.data.id;

                if (!token) {
                    // Wompi devuelve el motivo campo a campo; se muestra el
                    // primero, que es el que el abogado puede corregir.
                    var mensajes = cuerpo && cuerpo.error && cuerpo.error.messages;
                    var primero = mensajes ? Object.keys(mensajes)[0] : null;

                    throw new Error(primero ? mensajes[primero][0] : 'No pudimos validar esa tarjeta.');
                }

                document.getElementById('card_token').value = token;

                // Los campos de la tarjeta no se envían: el servidor solo
                // necesita el token, y lo que no se manda no se puede filtrar.
                ['number', 'cvc', 'exp', 'holder'].forEach(function (nombre) {
                    campo(nombre).value = '';
                    campo(nombre).disabled = true;
                });

                form.submit();
            })
            .catch(function (fallo) {
                boton.disabled = false;
                boton.textContent = etiquetaOriginal;
                avisar(fallo.message || 'No pudimos validar esa tarjeta. Revísala e inténtalo otra vez.');
            });
    });
})();
</script>
@endpush
