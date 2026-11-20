@extends('layouts.site')

@section('content')
@php $payments = config('hazlolegal.payments'); @endphp

<section class="section section--white">
    <div class="container">
        <x-wizard :step="$step" />

        <div class="section-head">
            <span class="eyebrow">Pedido {{ $order->reference }}</span>
            <h1>Último paso: <span class="hl">confirma tu pago</span></h1>
            <p>El reloj de la entrega arranca cuando confirmamos el pago, no antes.</p>
        </div>

        <div class="checkout-layout">
            <div>
                @if (session('paymentError'))
                    <div class="alert alert--danger" style="margin-bottom: var(--s6);">
                        <span class="alert__icon" aria-hidden="true">!</span>
                        <div>
                            <strong>El pago no se completó.</strong>
                            <p style="margin-top: var(--s1);">{{ session('paymentError') }} Puedes intentarlo otra vez.</p>
                        </div>
                    </div>
                @endif

                @if ($bump && $bump->price > 0)
                    {{-- Se suma al mismo pedido y vuelve a esta pantalla con
                         el total actualizado. --}}
                    <form action="{{ route('checkout.add-addon', $order->reference) }}" method="post" class="bump">
                        @csrf
                        <input type="hidden" name="addon" value="{{ $bump->key() }}">

                        <span class="bump__tag">Añádelo antes de pagar</span>

                        <div class="bump__body">
                            <strong>{{ $bump->addon->emoji }} {{ $bump->addon->name }}</strong>
                            @if ($bump->reason)
                                <p>{{ $bump->reason }}</p>
                            @endif
                        </div>

                        <button type="submit" class="bump__add">
                            + {{ $bump->formattedPrice() }}
                        </button>
                    </form>
                @endif

                @if ($transaction)
                    <h2 style="font-size: var(--t-xl);">Paga de forma segura</h2>
                    <p class="text-sm text-muted">
                        Te llevamos a Wompi para completar el pago. Puedes pagar con tarjeta, Nequi,
                        PSE o transferencia desde Bancolombia.
                    </p>

                    {{-- El formulario sale al dominio de Wompi: aquí nunca
                         entra un número de tarjeta. La firma de integridad
                         impide que alguien edite el monto por el camino. --}}
                    <form action="{{ $wompi->checkoutUrl() }}" method="GET" class="pay-form">
                        @foreach ($checkoutFields as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach

                        <button type="submit" class="btn btn--primary btn--lg btn--block">
                            Pagar {{ $order->formattedAmount() }}
                        </button>
                    </form>

                    <div class="pay-badges">
                        <span>🔒 Pago cifrado</span>
                        <span>💳 Tarjeta</span>
                        <span>📱 Nequi</span>
                        <span>🏦 PSE</span>
                    </div>

                    @if ($wompi->isSandbox())
                        <div class="alert alert--warn" style="margin-top: var(--s6);">
                            <span class="alert__icon" aria-hidden="true">🧪</span>
                            <div>
                                <strong>Modo de pruebas.</strong>
                                <p style="margin-top: var(--s1);">
                                    Estás en el entorno de pruebas de Wompi: ningún cobro es real.
                                    Usa la tarjeta de prueba <strong class="mono">4242 4242 4242 4242</strong>,
                                    cualquier fecha futura y CVC <strong class="mono">123</strong>.
                                </p>
                            </div>
                        </div>
                    @endif

                    <details class="pay-manual">
                        <summary>Prefiero transferir manualmente</summary>

                        <div class="card card--flat" style="background: var(--slate-50); margin-top: var(--s4);">
                            <p class="text-sm text-muted mb-0">
                                Nequi / Daviplata: <strong class="mono">{{ $payments['nequi'] }}</strong><br>
                                {{ $payments['bank'] }}: <strong class="mono">{{ $payments['bank_account'] }}</strong><br>
                                A nombre de <strong>{{ config('hazlolegal.brand.legal_name') }}</strong><br>
                                Referencia: <strong class="mono">{{ $order->reference }}</strong>
                            </p>
                            <p class="text-xs text-muted" style="margin-top: var(--s3); margin-bottom: 0;">
                                Mándanos el comprobante por WhatsApp y lo confirmamos a mano.
                            </p>
                        </div>
                    </details>
                @else
                    {{-- Sin pasarela configurada: se simula para poder recorrer
                         el flujo completo en desarrollo. --}}
                    <form action="{{ route('checkout.pay', $order->reference) }}" method="post">
                        @csrf

                        <h2 style="font-size: var(--t-xl);">¿Cómo quieres pagar?</h2>

                        <div class="choice-grid" style="margin-bottom: var(--s8);">
                            @foreach ($paymentMethods as $i => $method)
                                <label class="choice">
                                    <input type="radio" name="payment_method" value="{{ $method->value }}" required
                                           @checked($i === 0)>
                                    <span class="choice__box">
                                        <span class="choice__head">
                                            <span class="choice__title">{{ $method->getLabel() }}</span>
                                            <span class="choice__radio" aria-hidden="true"></span>
                                        </span>
                                        <span class="choice__meta">{{ $method->helper() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn--primary btn--lg">
                            Confirmar pago de {{ $order->formattedAmount() }}
                        </button>
                    </form>
                @endif

                <p class="text-xs text-muted" style="margin-top: var(--s5);">
                    🔒 Nunca pedimos datos de tarjeta por WhatsApp ni por teléfono.
                    Si alguien lo hace en nuestro nombre, no somos nosotros.
                </p>
            </div>

            <aside class="checkout-aside">
                <div class="card summary">
                    <h2 style="font-size: var(--t-base); margin-bottom: var(--s5);">Tu pedido</h2>

                    <dl class="summary__rows">
                        <div class="summary__row">
                            <dt>Referencia</dt>
                            <dd class="mono">{{ $order->reference }}</dd>
                        </div>
                        <div class="summary__row">
                            <dt>Trámite</dt>
                            <dd>{{ $order->document->short_name ?: $order->document->name }}</dd>
                        </div>
                        @foreach ($order->addons as $addon)
                            <div class="summary__row">
                                <dt>{{ $addon->name }}</dt>
                                <dd>{{ $addon->price === 0 ? 'Incluido' : $addon->formattedPrice() }}</dd>
                            </div>
                        @endforeach
                        <div class="summary__row">
                            <dt>Entrega</dt>
                            <dd>{{ $order->isExpress() ? 'Ahora mismo' : $order->document->deliveryLabel() }}</dd>
                        </div>
                        <div class="summary__row">
                            <dt>A nombre de</dt>
                            <dd>{{ $order->customer_name }}</dd>
                        </div>
                        <div class="summary__row">
                            <dt>Entrega a</dt>
                            <dd style="word-break: break-all;">{{ $order->customer_email }}</dd>
                        </div>
                    </dl>

                    <div class="summary__total">
                        <span>Total</span>
                        <span>{{ $order->formattedAmount() }}</span>
                    </div>

                    <p class="text-xs text-muted" style="margin-top: var(--s5);">
                        ¿Algo quedó mal?
                        <a href="{{ route('checkout.configure', $order->document->slug) }}">
                            Volver a empezar el pedido
                        </a>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
