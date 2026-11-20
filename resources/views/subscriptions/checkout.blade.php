@extends('signature.layout')

@section('title', 'Pagar mi plan')

@section('content')
<div class="sign__head">
    <span class="logo" style="justify-content: center;">
        <span class="logo__word">Hazlo</span><span class="logo__hl">Legal</span>
    </span>
    <h1>Activa <span class="hl hl--blue">tu plan</span></h1>
    <p>{{ $lawyer->name }}</p>
</div>

<div class="card card--pad-lg">
    <h2 style="font-size: var(--t-lg); margin-bottom: var(--s2);">{{ $plan->name }}</h2>
    <p class="text-sm text-muted">{{ $plan->tagline }}</p>

    <div class="price-live" style="margin: var(--s6) 0;">
        <span class="price-live__total">${{ number_format($plan->priceFor($period), 0, ',', '.') }}</span>
        <span class="text-sm text-muted">/ {{ $period->value === 'annual' ? 'año' : 'mes' }}</span>
    </div>

    @if ($period->value === 'monthly' && $plan->price_annual)
        <p class="text-sm">
            <a href="{{ route('subscription.checkout', ['plan' => $plan->id, 'periodo' => 'annual']) }}">
                Pagar un año y ahorrar ${{ number_format($plan->annualSaving(), 0, ',', '.') }}
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

    @if ($transaction)
        <form action="{{ $wompi->checkoutUrl() }}" method="GET" style="margin-top: var(--s6);">
            @foreach ($checkoutFields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <button type="submit" class="btn btn--primary btn--block btn--lg">
                Pagar ${{ number_format($plan->priceFor($period), 0, ',', '.') }}
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
                        Ningún cobro es real. Tarjeta <strong class="mono">4242 4242 4242 4242</strong>,
                        cualquier fecha futura, CVC <strong class="mono">123</strong>.
                    </p>
                </div>
            </div>
        @endif
    @else
        <div class="alert alert--info" style="margin-top: var(--s6);">
            <span class="alert__icon" aria-hidden="true">ℹ️</span>
            <div>
                <strong>Pago no disponible todavía.</strong>
                <p style="margin-top: var(--s1);">
                    Escríbenos a {{ config('hazlolegal.contact.email') }} y lo activamos a mano.
                </p>
            </div>
        </div>
    @endif

    <p class="sign__legal">
        No guardamos tus datos de tarjeta: el pago lo procesa Wompi.
        Cada periodo se cobra por separado, así que no hay cargos automáticos sorpresa.
    </p>
</div>

<div class="center mt-8">
    <a href="{{ url('/portal') }}" class="btn btn--outline">Volver al portal</a>
</div>
@endsection
