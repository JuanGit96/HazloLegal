@extends('layouts.site')

@section('content')
@php $payments = config('hazlolegal.payments'); @endphp

<section class="section section--white">
    <div class="container">
        <x-wizard :step="$step" />

        <div class="section-head">
            <span class="eyebrow">Pedido {{ $order->reference }}</span>
            <h1>Último paso: confirma tu pago</h1>
            <p>El reloj de la entrega arranca cuando confirmamos el pago, no antes.</p>
        </div>

        <div class="alert alert--warn" style="max-width: 760px; margin: 0 auto var(--s8);">
            <span class="alert__icon" aria-hidden="true">🧪</span>
            <div>
                <strong>Prototipo de demostración.</strong>
                <p style="margin-top: var(--s1);">
                    Esta pantalla aún no cobra: al confirmar simulamos un pago aprobado para que puedas
                    recorrer el flujo completo. La integración con la pasarela real va en la siguiente etapa.
                </p>
            </div>
        </div>

        <div class="checkout-layout">
            <div>
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

                    <div class="card card--flat" style="background: var(--slate-50); margin-bottom: var(--s8);">
                        <h3 style="font-size: var(--t-base);">Datos para transferencia manual</h3>
                        <p class="text-sm text-muted mb-0">
                            Nequi / Daviplata: <strong class="mono">{{ $payments['nequi'] }}</strong><br>
                            {{ $payments['bank'] }}: <strong class="mono">{{ $payments['bank_account'] }}</strong><br>
                            A nombre de <strong>{{ config('hazlolegal.brand.legal_name') }}</strong><br>
                            Referencia: <strong class="mono">{{ $order->reference }}</strong>
                        </p>
                    </div>

                    <button type="submit" class="btn btn--primary btn--lg">
                        Confirmar pago de {{ $order->formattedAmount() }}
                    </button>

                    <p class="text-xs text-muted" style="margin-top: var(--s4);">
                        🔒 Nunca pedimos datos de tarjeta por WhatsApp ni por teléfono.
                        Si alguien lo hace en nuestro nombre, no somos nosotros.
                    </p>
                </form>
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
                        <div class="summary__row">
                            <dt>Plan</dt>
                            <dd>{{ $order->plan->name }}</dd>
                        </div>
                        <div class="summary__row">
                            <dt>Velocidad</dt>
                            <dd>{{ $order->speed->shortLabel() }}</dd>
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
                        <a href="{{ route('checkout.configure', [$order->plan->key->value, $order->document->slug]) }}">
                            Volver a empezar el pedido
                        </a>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
