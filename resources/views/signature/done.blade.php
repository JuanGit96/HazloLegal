@extends('signature.layout')

@section('title', 'Documento firmado')

@section('content')
<div class="sign__head">
    <div class="done-mark" aria-hidden="true">✓</div>
    <h1>Documento <span class="hl hl--blue">firmado</span></h1>
    <p>{{ $order->document->name }} · {{ $order->reference }}</p>
</div>

<div class="card card--pad-lg">
    <p class="text-sm">
        Tu firma quedó estampada en el documento. Ya puedes cerrar esta pantalla:
        <strong>el PDF firmado te llega al correo</strong> y está en la página de tu pedido.
    </p>

    <dl class="summary__rows" style="margin-top: var(--s5);">
        <div class="summary__row">
            <dt>Firmado por</dt>
            <dd>{{ $signature->signer_name }}</dd>
        </div>
        <div class="summary__row">
            <dt>Fecha y hora</dt>
            <dd>{{ $signature->signed_at?->translatedFormat('j \d\e F \d\e Y, g:i a') }}</dd>
        </div>
        @if ($signature->document_hash)
        <div class="summary__row">
            <dt>Huella del documento</dt>
            <dd class="mono" style="font-size: var(--t-xs); word-break: break-all;">
                {{ \Illuminate\Support\Str::limit($signature->document_hash, 24) }}
            </dd>
        </div>
        @endif
    </dl>

    <p class="text-xs text-muted" style="margin-top: var(--s5); margin-bottom: 0;">
        Esa huella es lo que permite comprobar que el documento no cambió después de que lo
        firmaste. Va impresa en el propio PDF.
    </p>
</div>

<div class="center mt-8">
    <a href="{{ $order->trackingUrl() }}" class="btn btn--outline">Ver mi pedido</a>
</div>
@endsection
