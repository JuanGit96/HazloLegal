@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $paid = $order->paid_at !== null;
@endphp

<section class="section section--white">
    <div class="container container--narrow">
        <x-wizard :step="$paid ? 4 : 3" />

        <div class="center">
            <div style="display: grid; place-items: center; width: 84px; height: 84px; margin: 0 auto var(--s6);
                        background: var(--green-50); border: 3px solid var(--green-600); border-radius: 50%; font-size: 2.2rem;"
                 aria-hidden="true">
                {{ $paid ? '✓' : '🕒' }}
            </div>

            <h1>{{ $paid ? '¡Listo! Tu trámite está en marcha' : 'Tu pedido quedó registrado' }}</h1>
            <p class="text-muted" style="font-size: var(--t-lg);">
                @if ($paid)
                    Un abogado titulado ya tiene tu caso. Te avisamos por correo y WhatsApp en cuanto esté.
                @else
                    Falta confirmar el pago para que entre a la fila de redacción.
                @endif
            </p>

            <p class="pill pill--brand" style="font-size: var(--t-base); padding: var(--s3) var(--s5);">
                Referencia <strong class="mono">{{ $order->reference }}</strong>
            </p>
        </div>

        <div class="card card--pad-lg mt-10">
            <h2 style="font-size: var(--t-lg);">Resumen</h2>

            <dl class="summary__rows" style="margin-top: var(--s5);">
                <div class="summary__row">
                    <dt>Trámite</dt>
                    <dd><a href="{{ $order->document->url() }}">{{ $order->document->name }}</a></dd>
                </div>
                <div class="summary__row">
                    <dt>Plan</dt>
                    <dd>{{ $order->plan->name }} · {{ $order->speed->shortLabel() }}</dd>
                </div>
                <div class="summary__row">
                    <dt>Estado</dt>
                    <dd><span class="pill pill--{{ $paid ? 'success' : 'warn' }}">{{ $order->status->getLabel() }}</span></dd>
                </div>
                @if ($order->promised_at)
                <div class="summary__row">
                    <dt>Fecha comprometida</dt>
                    <dd>{{ $order->promised_at->translatedFormat('l j \d\e F, g:i a') }}</dd>
                </div>
                @endif
                <div class="summary__row">
                    <dt>Entrega a</dt>
                    <dd style="word-break: break-all;">{{ $order->customer_email }}</dd>
                </div>
                <div class="summary__row">
                    <dt>Pagado</dt>
                    <dd>{{ $order->formattedAmount() }} {{ $order->payment_method ? '· '.$order->payment_method->getLabel() : '' }}</dd>
                </div>
            </dl>
        </div>

        @if ($paid)
            <div class="card card--pad-lg mt-8" style="background: var(--blue-50); border-color: var(--blue-200);">
                <h2 style="font-size: var(--t-lg);">Qué pasa ahora</h2>
                <div class="steps" style="gap: var(--s6); margin-top: var(--s5);">
                    <div class="step">
                        <div class="step__num" aria-hidden="true">1</div>
                        <h3>Revisión de tu caso</h3>
                        <p>Un abogado del área lee lo que nos contaste. Si falta algo, te escribe por WhatsApp.</p>
                    </div>
                    <div class="step">
                        <div class="step__num" aria-hidden="true">2</div>
                        <h3>Redacción y auditoría</h3>
                        <p>Se redacta el documento y se audita ortografía, datos y normas citadas.</p>
                    </div>
                    <div class="step">
                        <div class="step__num" aria-hidden="true">3</div>
                        <h3>Entrega</h3>
                        <p>Recibes el PDF en tu correo y WhatsApp, con la guía de radicación.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="center mt-8">
                <a href="{{ route('checkout.payment', $order->reference) }}" class="btn btn--primary btn--lg">
                    Ir a confirmar el pago
                </a>
            </div>
        @endif

        <div class="btn-row btn-row--center mt-10">
            <a href="{{ $wa->forOrder($order->reference) }}" class="btn btn--wa" target="_blank" rel="noopener">
                Escribirnos por WhatsApp
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn--outline">Ver otros trámites</a>
        </div>

        <p class="center text-xs text-muted mt-8">
            Guarda esta página: puedes volver con tu referencia
            <strong class="mono">{{ $order->reference }}</strong> para consultar el estado.
            Tu pedido está cubierto por el <a href="{{ route('pages.guarantee') }}">Certificado de Garantía</a>.
        </p>
    </div>
</section>
@endsection
