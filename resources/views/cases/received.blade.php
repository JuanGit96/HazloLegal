@extends('layouts.site')

@section('content')
@php $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class); @endphp

<section class="section section--white">
    <div class="container container--narrow center">
        <div style="display: grid; place-items: center; width: 84px; height: 84px; margin: 0 auto var(--s6);
                    background: var(--green-50); border: 3px solid var(--green-600); border-radius: 50%; font-size: 2.2rem;"
             aria-hidden="true">✓</div>

        <h1>Tu caso ya está con los abogados</h1>
        <p class="text-muted" style="font-size: var(--t-lg);">
            Lo enviamos a los especialistas en {{ \Illuminate\Support\Str::lower($case->practice_area->getLabel()) }}
            de nuestra red. El primero que lo acepte se queda con él y te escribimos de inmediato
            con su nombre, su especialidad y su experiencia.
        </p>

        <p class="pill pill--brand" style="font-size: var(--t-base); padding: var(--s3) var(--s5);">
            Referencia <strong class="mono">{{ $case->reference }}</strong>
        </p>

        <div class="card card--pad-lg mt-10" style="text-align: left;">
            <h2 style="font-size: var(--t-lg);">Qué sigue</h2>
            <ul class="guarantee-list" style="margin-top: var(--s5);">
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">1</span>
                    <span>
                        <strong>Ya enviamos tu caso</strong>
                        {{ $case->offers_sent > 0 ? "A {$case->offers_sent} abogados especializados, al mismo tiempo." : 'A los abogados especializados en tu área.' }}
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">2</span>
                    <span>
                        <strong>Te avisamos en cuanto alguien lo acepte</strong>
                        Recibirás un correo en {{ $case->client_email }} con el perfil completo del abogado.
                        Plazo máximo: {{ $ttlHours }} horas.
                    </span>
                </li>
                <li>
                    <span class="guarantee-list__check" aria-hidden="true">3</span>
                    <span>
                        <strong>El abogado te contacta y cotiza</strong>
                        Evaluará tu caso y te propondrá un esquema de honorarios claro. Sin compromiso
                        hasta que tú aceptes.
                    </span>
                </li>
            </ul>
        </div>

        <div class="card card--flat mt-8" style="background: var(--slate-50); text-align: left;">
            <h3 style="font-size: var(--t-base);">Tu caso</h3>
            <dl class="summary__rows" style="margin-bottom: 0;">
                <div class="summary__row"><dt>Área</dt><dd>{{ $case->practice_area->getLabel() }}</dd></div>
                <div class="summary__row"><dt>Resumen</dt><dd style="max-width: 60%;">{{ $case->title }}</dd></div>
                <div class="summary__row"><dt>Urgencia</dt><dd>{{ $case->urgency->shortLabel() }}</dd></div>
                <div class="summary__row"><dt>Estado</dt><dd><span class="pill pill--warn">{{ $case->status->getLabel() }}</span></dd></div>
            </dl>
        </div>

        <div class="btn-row btn-row--center mt-10">
            <a href="{{ $wa->forCase($case->reference) }}" class="btn btn--wa" target="_blank" rel="noopener">
                Escribirnos por WhatsApp
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn--outline">Ver trámites con precio fijo</a>
        </div>

        <p class="text-xs text-muted mt-8">
            Guarda tu referencia <strong class="mono">{{ $case->reference }}</strong>.
            Si en {{ $ttlHours }} horas nadie ha tomado el caso, te escribimos para explicarte por qué.
        </p>
    </div>
</section>
@endsection
