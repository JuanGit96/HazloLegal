@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $paid = $order->paid_at !== null;
    $review = $order->ai_review ?? [];
    $fixes = $review['issues'] ?? [];
    $warnings = $review['warnings'] ?? [];
@endphp

<section class="section section--white">
    <div class="container container--narrow">
        <x-wizard :step="$paid ? 4 : 3" :free="$order->isFree()" />

        <div class="center">
            <div class="done-mark" aria-hidden="true">{{ $paid ? '✓' : '🕒' }}</div>

            @php $listo = filled($order->document_path); @endphp

            <h1>
                @if ($paid && $listo)
                    ¡Listo! Tu documento <span class="hl hl--blue">está preparado</span>
                @elseif ($paid)
                    Recibido. <span class="hl hl--blue">Lo estamos preparando</span>
                @else
                    Tu pedido <span class="hl hl--blue">quedó registrado</span>
                @endif
            </h1>
            <p class="text-muted" style="font-size: var(--t-lg);">
                @if ($paid && $listo)
                    Lo tienes aquí abajo y también te llegó al correo {{ $order->customer_email }}.
                @elseif ($paid)
                    Te lo enviamos a {{ $order->customer_email }} en cuanto esté.
                @else
                    Falta confirmar el pago para que entre a la fila de preparación.
                @endif
            </p>

            <p class="pill pill--brand" style="font-size: var(--t-base); padding: var(--s3) var(--s5);">
                Referencia <strong class="mono">{{ $order->reference }}</strong>
            </p>
        </div>

        {{-- ---------------- En preparación ---------------- --}}
        @if ($paid && ! $order->document_path && $order->promised_at)
            <div class="card card--pad-lg mt-8 waiting">
                <span class="pill pill--brand">En preparación</span>
                <h2 style="font-size: var(--t-lg); margin-top: var(--s4);">
                    Lo tendrás antes del {{ $order->promised_at->translatedFormat('l j \d\e F, g:i a') }}
                </h2>
                <p class="text-sm text-muted">
                    Te llega al correo y puedes volver a esta página cuando quieras.
                </p>

                {{-- Con abogado detrás no se ofrece la entrega inmediata: tiene
                     que leer los hechos antes de escribir. --}}
                @if (! $order->isExpress() && ! $order->hasLawyerWork())
                    <form action="{{ route('checkout.add-addon', $order->reference) }}" method="post" class="upsell__form">
                        @csrf
                        <input type="hidden" name="addon" value="express">
                        <input type="hidden" name="t" value="{{ request('t') }}">
                        <button type="submit" class="btn btn--action">¿Lo necesitas ya? Pasar a entrega inmediata</button>
                    </form>
                @endif
            </div>
        @endif

        {{-- ---------------- Descargas ---------------- --}}
        @if ($paid && ($order->document_path || $order->instructions_path))
            <div class="card card--pad-lg mt-8 deliver">
                <h2 style="font-size: var(--t-lg);">Tus archivos</h2>

                <div class="deliver__files">
                    @if ($order->document_path)
                        <a href="{{ $order->downloadUrl('documento') }}" class="deliver__file">
                            <span class="deliver__icon" aria-hidden="true">📄</span>
                            <span>
                                <strong>{{ $order->document->short_name ?: $order->document->name }}</strong>
                                <small>PDF listo para firmar y radicar</small>
                            </span>
                            <span class="deliver__dl" aria-hidden="true">Descargar</span>
                        </a>
                    @endif

                    @if ($order->instructions_path)
                        <a href="{{ $order->downloadUrl('instructivo') }}" class="deliver__file">
                            <span class="deliver__icon" aria-hidden="true">🧭</span>
                            <span>
                                <strong>Instructivo de radicación</strong>
                                <small>Dónde entregarlo, qué anexar y qué plazo tiene la entidad</small>
                            </span>
                            <span class="deliver__dl" aria-hidden="true">Descargar</span>
                        </a>
                    @endif
                </div>

                <p class="text-xs text-muted" style="margin-top: var(--s5); margin-bottom: 0;">
                    Revisa que tus datos estén exactos antes de firmar. Si algo quedó mal,
                    <a href="{{ $wa->forOrder($order->reference) }}" target="_blank" rel="noopener">escríbenos</a>
                    y lo corregimos sin costo.
                </p>
            </div>
        @endif

        {{-- ---------------- Firma pendiente ----------------
             El QR es para quien está en el computador: se escanea y se firma
             en el celular, que es donde el dedo sirve de lápiz. --}}
        @php
            $firma = $order->signatureRequest()->latest()->first();
        @endphp

        @if ($paid && $firma && ! $firma->isSigned() && ! $firma->hasExpired())
            <div class="card card--pad-lg mt-8 sign-cta">
                <span class="pill pill--action">Falta tu firma</span>
                <h2 style="font-size: var(--t-lg); margin-top: var(--s4);">Firma desde tu celular</h2>
                <p class="text-sm text-muted">
                    Escanea el código con la cámara del celular y traza tu firma con el dedo.
                    Queda estampada en el documento, con validez legal según la Ley 527 de 1999.
                </p>

                <div class="sign-cta__grid">
                    <img src="{{ app(\App\Domain\Documents\Services\SignatureService::class)->qrDataUri($firma) }}"
                         alt="Código QR para firmar desde el celular" class="sign-cta__qr">

                    <div>
                        <p class="text-sm"><strong>¿Ya estás en el celular?</strong></p>
                        <a href="{{ $firma->url() }}" class="btn btn--primary">Firmar ahora</a>
                        <p class="text-xs text-muted" style="margin-top: var(--s4); margin-bottom: 0;">
                            El enlace vence el {{ $firma->expires_at?->translatedFormat('j \d\e F') }}.
                        </p>
                    </div>
                </div>
            </div>
        @elseif ($paid && $firma?->isSigned())
            <div class="alert alert--success mt-8">
                <span class="alert__icon" aria-hidden="true">✍️</span>
                <div>
                    <strong>Documento firmado el {{ $firma->signed_at?->translatedFormat('j \d\e F, g:i a') }}.</strong>
                    <p style="margin-top: var(--s1);">
                        El PDF lleva tu firma y el certificado con la fecha, la hora y la huella
                        del archivo.
                    </p>
                </div>
            </div>
        @endif

        {{-- ---------------- Lo que corrigió la revisión ---------------- --}}
        @if ($paid && ($fixes || $warnings))
            <div class="card card--flat mt-6" style="background: var(--cream-100);">
                <h3 style="font-size: var(--t-base); margin-bottom: var(--s3);">
                    ✨ Lo que revisamos de tu redacción
                </h3>
                @if ($fixes)
                    <ul class="text-sm text-muted" style="margin: 0; padding-left: var(--s5);">
                        @foreach (array_slice($fixes, 0, 5) as $fix)
                            <li>{{ $fix }}</li>
                        @endforeach
                    </ul>
                @endif

                @if ($warnings)
                    <p class="text-sm" style="margin: var(--s4) 0 var(--s2);"><strong>Revisa tú esto:</strong></p>
                    <ul class="text-sm text-muted" style="margin: 0; padding-left: var(--s5);">
                        @foreach (array_slice($warnings, 0, 4) as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                @endif

                <p class="text-xs text-muted" style="margin: var(--s4) 0 0;">
                    Corregimos ortografía, tono y puntuación. No cambiamos los hechos que nos contaste.
                </p>
            </div>
        @endif

        {{-- ---------------- Resumen ---------------- --}}
        <div class="card card--pad-lg mt-8">
            <h2 style="font-size: var(--t-lg);">Resumen</h2>

            <dl class="summary__rows" style="margin-top: var(--s5);">
                <div class="summary__row">
                    <dt>Trámite</dt>
                    <dd><a href="{{ $order->document->url() }}">{{ $order->document->name }}</a></dd>
                </div>
                @foreach ($order->addons as $addon)
                    <div class="summary__row">
                        <dt>{{ $addon->name }}</dt>
                        <dd>{{ $addon->formattedPrice() }}</dd>
                    </div>
                @endforeach
                <div class="summary__row">
                    <dt>Estado</dt>
                    <dd><span class="pill pill--{{ $paid ? 'success' : 'warn' }}">{{ $order->status->getLabel() }}</span></dd>
                </div>
                @if ($order->promised_at && ! $order->isExpress())
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
                    <dt>{{ $paid ? 'Pagado' : 'Total' }}</dt>
                    <dd>
                        {{ $order->isFree() ? 'Gratis' : $order->formattedAmount() }}
                        {{ $order->payment_method ? '· '.$order->payment_method->getLabel() : '' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- ---------------- El siguiente adicional ----------------
             Se ofrece uno solo y después de entregar, no antes: a estas
             alturas el cliente ya vio que el documento sirve, y ese es el
             momento en que la oferta es honesta y además funciona. --}}
        @if ($paid && $nextOffer)
            <div class="card card--pad-lg mt-8 upsell">
                <span class="pill pill--action">Te puede faltar esto</span>
                <h2 style="font-size: var(--t-lg); margin-top: var(--s4);">
                    {{ $nextOffer->addon->emoji }} {{ $nextOffer->addon->name }}
                </h2>
                @if ($nextOffer->reason)
                    <p class="text-sm text-muted">{{ $nextOffer->reason }}</p>
                @endif

                @if ($nextOffer->addon->bullets)
                    <ul class="plan__features">
                        @foreach (array_slice($nextOffer->addon->bullets, 0, 3) as $bullet)
                            <li>{{ $bullet }}</li>
                        @endforeach
                    </ul>
                @endif

                <form action="{{ route('checkout.add-addon', $order->reference) }}" method="post" class="upsell__form">
                    @csrf
                    <input type="hidden" name="addon" value="{{ $nextOffer->key() }}">
                    <input type="hidden" name="t" value="{{ request('t') }}">
                    <button type="submit" class="btn btn--primary">
                        Agregarlo por {{ $nextOffer->formattedPrice() }}
                    </button>
                    <span class="text-xs text-muted">Se suma a este mismo pedido. No vuelves a escribir nada.</span>
                </form>
            </div>
        @endif

        {{-- ---------------- Qué pasa ahora ---------------- --}}
        @if ($paid && ! $order->document_path)
            <div class="card card--pad-lg mt-8" style="background: var(--blue-50); border-color: var(--blue-200);">
                <h2 style="font-size: var(--t-lg);">Qué pasa ahora</h2>
                <div class="steps" style="gap: var(--s6); margin-top: var(--s5);">
                    <div class="step">
                        <div class="step__num" aria-hidden="true">1</div>
                        <h3>Revisamos tus datos</h3>
                        <p>Comprobamos que esté todo lo que el trámite necesita. Si falta algo, te escribimos por WhatsApp.</p>
                    </div>
                    <div class="step">
                        <div class="step__num" aria-hidden="true">2</div>
                        <h3>Se prepara tu documento</h3>
                        <p>Sobre el formato de ese trámite, con las normas vigentes que le aplican.</p>
                    </div>
                    <div class="step">
                        <div class="step__num" aria-hidden="true">3</div>
                        <h3>Entrega</h3>
                        <p>Recibes el PDF en tu correo y WhatsApp, con la guía de radicación.</p>
                    </div>
                </div>
            </div>
        @elseif (! $paid)
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

        <div class="card card--flat mt-10" style="background: var(--cream-100);">
            <p class="text-sm mb-0">
                <strong>¿Quieres volver a esto más adelante?</strong>
                Entra en <a href="{{ route('account.request') }}">Mis trámites</a> con
                <strong>{{ $order->customer_email }}</strong> y te mandamos un enlace.
                No hay contraseña que recordar.
            </p>
        </div>

        <p class="center text-xs text-muted mt-8">
            Guarda el enlace de esta página: es el único que abre tu pedido.
            Tu pedido está cubierto por el <a href="{{ route('pages.guarantee') }}">Certificado de Garantía</a>.
        </p>
    </div>
</section>
@endsection
