@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="section section--white">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Mis trámites</span>
            <h1>Todo lo que <span class="hl hl--blue">has pedido</span></h1>
            <p>{{ $email }}</p>
        </div>

        @if ($orders->isEmpty())
            <div class="alert alert--info">
                <span class="alert__icon" aria-hidden="true">📭</span>
                <div>
                    <strong>Todavía no hay nada aquí.</strong>
                    <p style="margin-top: var(--s1);">
                        <a href="{{ route('tools.index') }}">Empieza por un trámite gratis</a>.
                    </p>
                </div>
            </div>
        @else
            <div class="grid" style="gap: var(--s4);">
                @foreach ($orders as $order)
                    <div class="card card--pad-lg order-row">
                        <div class="order-row__head">
                            <div>
                                <h2 style="font-size: var(--t-lg); margin-bottom: var(--s1);">
                                    {{ $order->document->name }}
                                </h2>
                                <p class="text-xs text-muted mb-0">
                                    <span class="mono">{{ $order->reference }}</span> ·
                                    {{ $order->created_at->translatedFormat('j \d\e F \d\e Y') }}
                                </p>
                            </div>
                            <span class="pill pill--{{ $order->status->value === 'delivered' ? 'success' : ($order->paid_at ? 'brand' : 'warn') }}">
                                {{ $order->status->getLabel() }}
                            </span>
                        </div>

                        @if ($order->addons->isNotEmpty())
                            <p class="text-xs text-muted" style="margin-top: var(--s3);">
                                {{ $order->addons->pluck('name')->implode(' · ') }}
                            </p>
                        @endif

                        <div class="btn-row" style="margin-top: var(--s5);">
                            @if ($order->document_path)
                                <a href="{{ $order->downloadUrl('documento') }}" class="btn btn--primary btn--sm">
                                    Descargar documento
                                </a>
                            @endif
                            @if ($order->instructions_path)
                                <a href="{{ $order->downloadUrl('instructivo') }}" class="btn btn--outline btn--sm">
                                    Instructivo
                                </a>
                            @endif
                            <a href="{{ $order->trackingUrl() }}" class="btn btn--ghost btn--sm">Ver el pedido</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="center text-xs text-muted mt-10">
            ¿Algo no cuadra? Escríbenos por
            <a href="{{ $wa->link('Hola, tengo una duda sobre mis trámites.') }}" target="_blank" rel="noopener">WhatsApp</a>.
        </p>
    </div>
</section>
@endsection
