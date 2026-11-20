@extends('signature.layout')

@section('title', 'Estamos confirmando tu pago')

@section('content')
<div class="sign__head">
    <div class="done-mark" style="background: var(--amber-50); border-color: var(--amber-500);" aria-hidden="true">🕒</div>
    <h1>Estamos confirmando tu pago</h1>
    <p>Referencia <strong class="mono">{{ $reference }}</strong></p>
</div>

<div class="card card--pad-lg">
    <p class="text-sm">
        @if ($status === 'APPROVED')
            <strong>Tu pago salió aprobado.</strong> Estamos terminando de registrarlo.
            Te llega la confirmación al correo en unos minutos.
        @else
            El banco todavía no nos ha confirmado la operación. Esto puede tardar unos
            minutos, sobre todo con PSE.
        @endif
    </p>

    <p class="text-sm" style="margin-top: var(--s4);">
        <strong>No vuelvas a pagar.</strong> Si el cobro se hizo, lo vas a ver reflejado y te
        escribimos; si no se hizo, no se te cobró nada.
    </p>

    <div class="buybox__divider"></div>

    <p class="text-sm mb-0">
        ¿Prefieres que lo revisemos ahora? Escríbenos con esa referencia a
        <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>
        o por WhatsApp a {{ config('hazlolegal.contact.whatsapp_display') }}.
    </p>
</div>

<div class="center mt-8">
    <a href="{{ route('home') }}" class="btn btn--outline">Volver al inicio</a>
</div>
@endsection
