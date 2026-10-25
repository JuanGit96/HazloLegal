@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">El proceso</span>
            <h1>Cómo funciona Hazlo Legal</h1>
            <p>
                Automatizamos lo repetitivo para cobrar menos y responder más rápido.
                Lo que exige criterio jurídico lo sigue haciendo un abogado, a mano.
            </p>
        </div>
    </div>
</section>

<section class="section section--sunken">
    <div class="container container--narrow">
        <div class="stack" style="display: grid; gap: var(--s6);">
            @foreach ([
                ['1', 'Eliges tu trámite', 'Entras al catálogo y abres el documento que necesitas. Ahí mismo ves el precio final, el tiempo de entrega, qué incluye y qué no. Sin cotizaciones por correo ni «depende».'],
                ['2', 'Defines la velocidad', 'Estándar en 24 horas hábiles, o Express en menos de 4 si tienes un término encima. Es el mismo documento y el mismo abogado: lo único que cambia es la posición en la fila.'],
                ['3', 'Nos cuentas tu caso', 'Un formulario guiado que te pide exactamente lo que el abogado necesita. Antes de enviarlo te hacemos confirmar tus datos y los de la contraparte: un dígito mal en una cédula tumba un trámite.'],
                ['4', 'Pagas', 'Nequi, Daviplata, PSE, transferencia o tarjeta. El pago se hace dentro del sitio y recibes la confirmación con tu referencia. Nunca pedimos datos de tarjeta por WhatsApp.'],
                ['5', 'Un abogado trabaja tu caso', 'Si es un trámite de Fase 1, se ensambla sobre una plantilla maestra que nuestros abogados validaron y uno de ellos audita el PDF final. Si es de Fase 2, un abogado lo redacta desde cero sobre tus hechos.'],
                ['6', 'Recibes y radicas', 'El PDF llega a tu correo y a tu WhatsApp, con la guía de dónde presentarlo y qué anexar. Si lleva firma electrónica, te enviamos el enlace para firmar desde el celular.'],
            ] as [$num, $title, $text])
                <div class="card card--pad-lg" style="flex-direction: row; gap: var(--s6); align-items: flex-start;">
                    <div class="step__num" style="flex-shrink: 0; margin: 0;" aria-hidden="true">{{ $num }}</div>
                    <div>
                        <h2 style="font-size: var(--t-lg); margin-bottom: var(--s2);">{{ $title }}</h2>
                        <p class="text-muted mb-0">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container container--narrow">
        <div class="section-head">
            <h2>Por qué unos trámites son automatizados y otros no</h2>
            <p>Es la decisión técnica más importante del servicio, y preferimos explicarla.</p>
        </div>

        <div class="grid grid--2">
            <div class="card card--pad-lg" style="border-left: 4px solid var(--success);">
                <span class="pill pill--success" style="align-self: flex-start;">Fase 1 · Automatizado</span>
                <h3 style="margin-top: var(--s4);">Estructura estable, variables pocas</h3>
                <p class="text-sm text-muted">
                    Un NDA, una promesa de compraventa o un derecho de petición de Datacrédito tienen una
                    estructura que la ley o la práctica fijan. Cambian los nombres, las cifras y la entidad,
                    no la arquitectura del documento.
                </p>
                <p class="text-sm text-muted mb-0">
                    Aquí el sistema ensambla el documento sobre una plantilla maestra que nuestros abogados
                    escribieron y validaron, y <strong>un abogado audita el PDF final antes de enviarlo</strong>.
                    Eso nos permite entregarlo en horas y cobrar una fracción.
                </p>
            </div>

            <div class="card card--pad-lg" style="border-left: 4px solid var(--amber-500);">
                <span class="pill pill--warn" style="align-self: flex-start;">Fase 2 · Redacción manual</span>
                <h3 style="margin-top: var(--s4);">Hechos, cálculos o cláusulas condicionales</h3>
                <p class="text-sm text-muted">
                    Una tutela se gana o se pierde en la narración de los hechos. Una liquidación laboral
                    exige auditoría matemática. Un contrato de prestación de servicios cambia por completo
                    si eres desarrollador, diseñador o consultor.
                </p>
                <p class="text-sm text-muted mb-0">
                    Intentar automatizar estos documentos con reemplazo de variables
                    <strong>produce textos genéricos que un juez o una contraparte detecta de inmediato</strong>.
                    Por eso aquí un abogado redacta desde cero.
                </p>
            </div>
        </div>

        <div class="alert alert--info mt-8">
            <span class="alert__icon" aria-hidden="true">💡</span>
            <div>
                <strong>Lo decimos en cada ficha de producto.</strong>
                <p style="margin-top: var(--s1);">
                    Cada trámite del catálogo indica si es automatizado o de redacción manual,
                    para que sepas exactamente qué estás comprando.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section section--sunken">
    <div class="container">
        <div class="section-head">
            <h2>Los tres niveles de acompañamiento</h2>
            <p>Según cuánto necesites que un abogado se involucre.</p>
        </div>

        <div class="pricing-grid">
            @foreach ($plans as $plan)
                <div @class(['plan', 'plan--featured' => $plan->is_highlighted, 'plan--dark' => $plan->is_quote_only])>
                    @if ($plan->badge)<span class="badge-float">{{ $plan->badge }}</span>@endif
                    <h3>{{ $plan->name }}</h3>
                    <p class="plan__tagline">{{ $plan->tagline }}</p>
                    <div class="plan__price">
                        @if ($plan->price === null)
                            Cotizar
                        @else
                            {{ $pricing->formatCop($plan->price) }} <small>COP</small>
                        @endif
                    </div>
                    <ul class="plan__features">
                        @foreach ($plan->includes ?? [] as $f)
                            <li>{!! \Illuminate\Support\Str::inlineMarkdown($f) !!}</li>
                        @endforeach
                    </ul>
                    <a href="{{ $plan->is_quote_only ? route('cases.create') : route('checkout.documents', $plan->key->value) }}"
                       @class(['btn', 'btn--block',
                               'btn--primary' => $plan->is_highlighted,
                               'btn--white' => $plan->is_quote_only,
                               'btn--outline' => ! $plan->is_highlighted && ! $plan->is_quote_only])>
                        {{ $plan->cta_label }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<x-cta-band
    title="Empieza por tu caso concreto"
    text="Abre el trámite que necesitas y mira el precio, el plazo y qué recibes. Sin registro, sin cotizaciones por correo."
    primary-label="Ver el catálogo"
    :primary-href="route('documents.index')"
    secondary-label="Leer la garantía"
    :secondary-href="route('pages.guarantee')" />
@endsection
