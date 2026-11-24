@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $trust = config('hazlolegal.trust');
    $entry = $pricing->formatCop($entryPrice);
    $headline = $settings->get('hook_headline', 'Tu derecho de petición, ==gratis== y sin vacíos legales');
    $sub = $settings->get('hook_subheadline', 'Lo generas en minutos y te lo llevas listo para radicar.');
    $freeCount = $settings->get('hook_free_count');
    $firstFree = $freeDocuments->first();
@endphp

{{-- ============================================ HERO --}}
<section class="hero">
    <div class="container hero__grid">
        <div>
            <div class="hero__kicker">
                <span class="pill pill--success">Gratis · sin registro</span>
                <span class="pill pill--brand">Formatos escritos por abogados</span>
            </div>

            {{-- El titular y el subtítulo se editan desde el panel: son lo
                 primero que se prueba cuando la conversión no sube. Lo que se
                 escriba entre ==dobles iguales== sale con el marcador. --}}
            <h1>{!! \App\Domain\Shared\Services\TextMarker::render($headline) !!}</h1>

            <p class="hero__lead">{{ $sub }}</p>

            <div class="btn-row">
                @if ($firstFree)
                    <a href="{{ route('checkout.configure', $firstFree->slug) }}" class="btn btn--primary btn--lg">
                        Generar el mío gratis
                    </a>
                @endif
                <a href="{{ route('tools.index') }}" class="btn btn--outline btn--lg">Ver todo lo gratis</a>
            </div>

            <p class="btn-note">
                Sin tarjeta, sin suscripción. Si después quieres firma, instructivo o un abogado,
                lo sumas por aparte desde {{ $entry === 'Gratis' ? '$19.900' : $entry }}.
            </p>

            <div class="hero__proof">
                @if ($freeCount)
                <div class="hero__proof-item">
                    <strong>{{ $freeCount }}</strong>
                    <span>documentos generados</span>
                </div>
                @endif
                <div class="hero__proof-item">
                    <strong>{{ $trust['avg_delivery_hours'] }} h</strong>
                    <span>entrega promedio real</span>
                </div>
                <div class="hero__proof-item">
                    <strong>{{ $trust['satisfaction'] }}/5</strong>
                    <span>satisfacción de clientes</span>
                </div>
            </div>
        </div>

        {{-- Maqueta del entregable: muestra el producto, no una foto de stock. --}}
        <div class="hero__visual">
            <div class="doc-preview" aria-hidden="true">
                <div class="doc-preview__bar">
                    <span class="doc-preview__dot"></span>
                    <span class="doc-preview__dot"></span>
                    <span class="doc-preview__dot"></span>
                    <span class="doc-preview__name">derecho-de-peticion.pdf</span>
                </div>
                <div class="doc-preview__body">
                    <div class="doc-preview__title">Derecho de petición</div>
                    <div class="doc-preview__line doc-preview__line--accent"></div>
                    <div class="doc-preview__line"></div>
                    <div class="doc-preview__line doc-preview__line--mid"></div>
                    <div class="doc-preview__line"></div>
                    <div class="doc-preview__line doc-preview__line--short"></div>

                    <div class="doc-preview__seal">
                        <span>✓</span>
                        <span>Formato vigente · Ley 1755 de 2015</span>
                    </div>

                    <div class="doc-preview__sign">
                        <div>
                            <div class="doc-preview__scribble">firma</div>
                            <small>Firma electrónica · Ley 527 de 1999</small>
                        </div>
                        <small>{{ now()->translatedFormat('d M Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ GANCHO DE REVISIÓN --}}
@if ($settings->bool('hook_ai_enabled', true))
<div class="hook-strip">
    <span class="hook-strip__item"><span aria-hidden="true">✨</span> Revisamos ortografía y redacción</span>
    <span class="hook-strip__item"><span aria-hidden="true">🛡️</span> Formato escrito por abogados</span>
    <span class="hook-strip__item"><span aria-hidden="true">⚖️</span> Tono formal ante la entidad</span>
    <span class="hook-strip__item"><span aria-hidden="true">📄</span> PDF listo para radicar</span>
</div>
@endif

{{-- ============================================ LO GRATIS --}}
@if ($settings->bool('free_docs_enabled', true) && ($freeDocuments->isNotEmpty() || $calculators->isNotEmpty()))
<section class="section section--white" id="gratis">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Empieza por aquí</span>
            <h2>Lo que puedes resolver <span class="hl">hoy y gratis</span></h2>
            <p>
                No es una prueba de 7 días ni una versión recortada: el documento sale completo,
                listo para firmar y radicar, y es tuyo.
            </p>
        </div>

        <div class="grid grid--3 reveal">
            @foreach ($freeDocuments as $free)
                <a href="{{ route('checkout.configure', $free->slug) }}" class="tool-card">
                    <span class="tool-card__tag">Gratis</span>
                    <span class="tool-card__emoji" aria-hidden="true">{{ $free->emojiOrArea() }}</span>
                    <h3>{{ $free->short_name ?: $free->name }}</h3>
                    <p>{{ $free->hook ?: \Illuminate\Support\Str::limit($free->tagline ?: $free->summary, 90) }}</p>
                    <span class="tool-card__cta">Generarlo ahora →</span>
                </a>
            @endforeach

            @foreach ($calculators as $calculator)
                <a href="{{ route('tools.calculator', $calculator->slug) }}" class="tool-card tool-card--light">
                    <span class="tool-card__tag">Calculadora</span>
                    <span class="tool-card__emoji" aria-hidden="true">{{ $calculator->emoji ?: '🧮' }}</span>
                    <h3>{{ $calculator->name }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($calculator->intro, 90) }}</p>
                    <span class="tool-card__cta" style="color: var(--brand);">Calcular →</span>
                </a>
            @endforeach
        </div>

        <div class="center mt-10">
            <a href="{{ route('tools.index') }}" class="btn btn--outline">Ver todas las herramientas gratis</a>
        </div>

        <p class="center text-xs text-muted mt-6" style="max-width: 640px; margin-left: auto; margin-right: auto;">
            Para entregarte el documento te pedimos tu correo y tu autorización de tratamiento de datos
            (Ley 1581 de 2012). Tú decides si además quieres que un abogado te contacte.
            <a href="{{ route('pages.privacy') }}">Así usamos tus datos</a>.
        </p>
    </div>
</section>
@endif

{{-- ============================================ ADICIONALES --}}
@if ($addons->isNotEmpty())
<section class="section section--sunken" id="adicionales">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Sin paquetes ni letra menuda</span>
            <h2>Tú armas el trámite. <span class="hl hl--blue hl--right">Pagas solo lo que le sumes</span></h2>
            <p>
                No vendemos planes con cosas que no necesitas. Empiezas por el documento y
                le agregas lo que tu caso pida. Cada cosa con su precio, a la vista.
            </p>
        </div>

        <div class="grid grid--3 reveal">
            @foreach ($addons as $addon)
                <div class="tile">
                    <span class="tile__icon" aria-hidden="true">{{ $addon->emoji ?: '➕' }}</span>
                    <h3>{{ $addon->name }}</h3>
                    <p>{{ $addon->tagline }}</p>
                    <p style="margin-top: var(--s4); font-weight: 800; color: var(--brand-dark);">
                        @if ($addon->default_price === 0)
                            Incluido
                        @else
                            +{{ $pricing->formatCop($addon->default_price) }}
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <p class="center text-sm text-muted mt-8">
            El precio de cada adicional puede cambiar según el trámite: acompañar una tutela no es
            lo mismo que acompañar un contrato de arriendo. Siempre lo ves antes de pagar.
        </p>
    </div>
</section>
@endif

{{-- ============================================ CATÁLOGO --}}
<section class="section section--white" id="catalogo">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Catálogo</span>
            <h2>¿Qué necesitas <span class="hl hl--blue">resolver hoy</span>?</h2>
            <p>Precio fijo publicado y tiempo de entrega comprometido. Entra a tu caso y mira exactamente qué recibes.</p>
        </div>

        <div class="catalog-grid reveal">
            @foreach ($documents as $document)
                <x-doc-card :document="$document" :price="$document->priceLabel()" />
            @endforeach
        </div>

        <p class="center mt-8 text-sm text-muted">
            ¿No ves tu caso? <a href="{{ route('cases.create') }}"><strong>Cuéntanos tu situación</strong></a>
            y un abogado te cotiza sin costo.
        </p>
    </div>
</section>

{{-- ============================================ ANCLAJE DE PRECIO --}}
{{-- Compara solo contra la vía tradicional. Nombrar alternativas gratuitas
     aquí le daría al cliente una idea que quizá no traía. Ese contenido vive
     en el blog, donde llega quien ya la está buscando. --}}
<section class="section section--sunken">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Compara antes de decidir</span>
            <h2>Lo mismo que resolverías en una oficina, <span class="hl hl--blue hl--right">sin la oficina</span></h2>
            <p>Quitamos la parte cara del proceso, no la parte que te protege.</p>
        </div>

        <div class="compare-wrap reveal">
            <table class="compare">
                <caption class="visually-hidden">Comparación entre resolver tu trámite con HazloLegal o acudiendo a una oficina de abogados</caption>
                <thead>
                    <tr>
                        <th scope="col">&nbsp;</th>
                        <th scope="col" class="is-us">HazloLegal</th>
                        <th scope="col">Oficina de abogados</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">Cuánto cuesta</th>
                        <td class="is-us">Desde {{ $entry }}, publicado</td>
                        <td>Entre $300.000 y $800.000</td>
                    </tr>
                    <tr>
                        <th scope="row">Cuándo sabes el precio</th>
                        <td class="is-us">Antes de dar tus datos</td>
                        <td>Después de la primera cita</td>
                    </tr>
                    <tr>
                        <th scope="row">Cuándo lo tienes</th>
                        <td class="is-us">De 4 a 24 horas</td>
                        <td>De 3 a 15 días</td>
                    </tr>
                    <tr>
                        <th scope="row">Qué te toca hacer</th>
                        <td class="is-us">Llenar un formulario desde el celular</td>
                        <td>Pedir cita y desplazarte en horario de oficina</td>
                    </tr>
                    <tr>
                        <th scope="row">Quién escribió el documento</th>
                        <td class="is-us">Abogados colombianos, en un formato que se actualiza con la ley</td>
                        <td>El abogado que te atienda ese día</td>
                    </tr>
                    <tr>
                        <th scope="row">Si lo rechazan por un error nuestro</th>
                        <td class="is-us"><span class="yes">Lo corregimos o te devolvemos el 100 %</span>, por escrito</td>
                        <td>Según lo que hayan acordado</td>
                    </tr>
                    <tr>
                        <th scope="row">La consulta inicial</th>
                        <td class="is-us">No se cobra</td>
                        <td>Suele cobrarse aparte</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="center mt-6 text-sm text-muted">
            Lo caro de un trámite legal nunca fue el papel: era el tiempo, el desplazamiento y
            no saber cuánto ibas a terminar pagando. Eso es lo que quitamos.
        </p>
    </div>
</section>

{{-- ============================================ PRECIOS --}}
{{-- Una sola tarjeta por nivel y el precio real al lado. Lo que diferencia
     a un nivel del siguiente es un adicional concreto, no una lista de
     beneficios vagos. --}}
<section class="section section--white" id="precios">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Precios</span>
            <h2>Tres formas de resolverlo. <span class="hl hl--blue">Tú eliges hasta dónde</span></h2>
            <p>Sin anticipos y sin «depende». El precio sale del trámite más lo que le sumes.</p>
        </div>

        <div class="pricing-grid reveal">
            <div class="plan">
                <div class="plan__head">
                    <h3>Hazlo tú</h3>
                    <p class="plan__tagline">Para trámites estándar que solo necesitan estar bien escritos.</p>
                </div>

                <div class="plan__price">Gratis<small>algunos trámites</small></div>
                <p class="plan__anchor">Listo en minutos · sin tarjeta</p>

                <ul class="plan__features">
                    <li>Documento completo en PDF</li>
                    <li>Revisión automática de ortografía y redacción</li>
                    <li>Formato escrito por abogados y al día con la norma</li>
                    <li>Lo descargas y lo radicas tú</li>
                </ul>

                <a href="{{ route('tools.index') }}" class="btn btn--outline btn--block mt-6">Ver los gratis</a>
                <p class="plan__foot">No incluye instructivo de radicación.</p>
            </div>

            <div class="plan plan--featured">
                <span class="badge-float">MÁS ELEGIDO</span>

                <div class="plan__head">
                    <h3>Hazlo con guía</h3>
                    <p class="plan__tagline">El documento y el instructivo que te dice exactamente qué hacer con él.</p>
                </div>

                <div class="plan__price">
                    <span style="font-size: var(--t-lg); font-weight: 600; color: var(--text-subtle);">desde</span>
                    {{ $entry === 'Gratis' ? '$19.900' : $entry }}
                    <small>COP</small>
                </div>
                <p class="plan__anchor">Pago único · sin mensualidad</p>

                <ul class="plan__features">
                    <li>Todo lo anterior</li>
                    <li><strong>Instructivo de radicación</strong>: dónde entregarlo, qué anexar y qué plazo tiene la entidad</li>
                    <li>Qué hacer si no te responden</li>
                    <li>Opción de entrega inmediata</li>
                </ul>

                <a href="{{ route('documents.index') }}" class="btn btn--primary btn--block mt-6">Elegir mi trámite</a>
                <p class="plan__foot">Cubierto por el Certificado de Garantía</p>
            </div>

            <div class="plan plan--dark">
                <div class="plan__head">
                    <h3>Que lo haga un abogado</h3>
                    <p class="plan__tagline">Para lo que no cabe en un formato: hechos propios, cálculos, cláusulas a medida.</p>
                </div>

                <div class="plan__price">Cotizar<small>según el caso</small></div>
                <p class="plan__anchor">La cotización no cuesta y no te compromete.</p>

                <ul class="plan__features">
                    <li>Un abogado lee tu caso y redacta sobre él</li>
                    <li>Puedes hablar con él antes de radicar</li>
                    <li>Te acompaña hasta la respuesta de la entidad</li>
                    <li>Si tu caso da para proceso, te lo decimos</li>
                </ul>

                <a href="{{ route('cases.create') }}" class="btn btn--white btn--block mt-6">Contar mi caso</a>
                <p class="plan__foot">Te responde un abogado, no un bot.</p>
            </div>
        </div>

        <p class="center mt-8 text-sm text-muted">
            Lo que pagas está cubierto por el
            <a href="{{ route('pages.guarantee') }}"><strong>Certificado de Garantía HazloLegal</strong></a>:
            si el documento sale con un error nuestro, lo corregimos o te devolvemos el dinero.
        </p>
    </div>
</section>

{{-- ============================================ CERTIFICADO DE GARANTÍA --}}
<section class="section section--sunken" id="garantia">
    <div class="container">
        <div class="guarantee reveal">
            <div class="guarantee__grid">
                <x-guarantee-seal />

                <div>
                    <span class="eyebrow" style="color: var(--green-700);">Nuestro compromiso</span>
                    <h2 style="margin-bottom: var(--s3);">Certificado de Garantía HazloLegal</h2>
                    <p class="text-muted" style="font-size: var(--t-lg);">
                        No te pedimos que confíes a ciegas. Te lo damos por escrito: si el documento
                        sale con errores nuestros, lo arreglamos. Y si una entidad lo rechaza por un
                        defecto de forma que nosotros cometimos, te devolvemos el 100 %.
                    </p>

                    <ul class="guarantee-list">
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong>Correcciones ilimitadas por errores nuestros</strong>
                                Ortografía, redacción, datos tuyos o de la contraparte mal transcritos, normas mal citadas.
                                Lo corregimos en máximo {{ config('hazlolegal.guarantee.correction_hours') }} horas, sin costo y sin discutir.
                            </span>
                        </li>
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong>Devolución del {{ config('hazlolegal.guarantee.refund_percentage') }} % si lo rechazan por nuestra culpa</strong>
                                Si una EPS, un juzgado o una entidad devuelve tu documento por un defecto de forma
                                atribuible a nosotros, te regresamos todo lo que pagaste.
                            </span>
                        </li>
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong>Cumplimos el plazo o no lo cobras</strong>
                                Si nos pasamos del tiempo prometido por una razón nuestra, el trámite queda sin costo.
                            </span>
                        </li>
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong>{{ config('hazlolegal.guarantee.claim_window_days') }} días para reclamar</strong>
                                Tienes un mes completo desde la entrega para pedir una corrección o la devolución.
                            </span>
                        </li>
                    </ul>

                    <div class="btn-row mt-8">
                        <a href="{{ route('pages.guarantee') }}" class="btn btn--brand">Leer el certificado completo</a>
                        <a href="{{ route('pages.guarantee') }}#reclamar" class="btn btn--outline">Activar mi garantía</a>
                    </div>

                    <p class="text-xs text-muted mt-6">
                        Qué no cubre, con total transparencia: cambios de opinión sobre lo que querías pedir,
                        información que no nos diste, y el resultado de la decisión de un juez o de una entidad
                        &mdash; eso no lo controla ningún abogado.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ CÓMO FUNCIONA --}}
<section class="section section--white">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Proceso</span>
            <h2>De tu problema al documento firmado, <span class="hl hl--blue hl--right">en 4 pasos</span></h2>
            <p>Sin desplazamientos, sin salas de espera y sin cobrar la hora de consulta.</p>
        </div>

        <div class="steps reveal">
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">1</div>
                <h3>Eliges tu trámite</h3>
                <p>Precio y plazo a la vista. Varios no cuestan nada.</p>
            </div>
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">2</div>
                <h3>Nos cuentas tu caso</h3>
                <p>Un formulario guiado que valida cada dato antes de pasarlo al abogado.</p>
            </div>
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">3</div>
                <h3>Le sumas lo que necesites</h3>
                <p>Instructivo, entrega inmediata, firma o un abogado que lo revise. Cada cosa con su precio.</p>
            </div>
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">4</div>
                <h3>Recibes y radicas</h3>
                <p>PDF listo en tu correo y WhatsApp, con la guía exacta de dónde presentarlo.</p>
            </div>
        </div>

        <div class="center mt-10">
            <a href="{{ route('pages.how-it-works') }}" class="btn btn--outline">Ver el proceso en detalle</a>
        </div>
    </div>
</section>

{{-- ============================================ CTA INTERMEDIO --}}
<x-cta-band
    eyebrow="Empieza ahora"
    title="Tu trámite puede estar listo antes de mañana"
    text="Elige el documento que necesitas, cuéntanos qué pasó y deja que un abogado se encargue del resto."
    primary-label="Elegir mi trámite"
    :primary-href="route('documents.index')"
    secondary-label="Tengo un caso complejo"
    :secondary-href="route('cases.create')"
    note="Pago único · Garantía de corrección o devolución · Atención por WhatsApp" />

{{-- ============================================ BENEFICIOS --}}
<section class="section section--white" id="beneficios">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Cómo evitamos errores</span>
            <h2>Un flujo diseñado para que <span class="hl hl--blue">no te rechacen el documento</span></h2>
            <p>La mayoría de los trámites se caen por detalles de forma. Atacamos justo eso.</p>
        </div>

        <div class="grid grid--4 reveal">
            <div class="feature">
                <div class="feature__icon" aria-hidden="true">🔍</div>
                <h3>Doble verificación</h3>
                <p>Antes de ensamblar el documento te obligamos a confirmar tus datos y los de la contraparte. Un dígito mal en una cédula tumba un trámite.</p>
            </div>
            <div class="feature">
                <div class="feature__icon" aria-hidden="true">⚖️</div>
                <h3>Normas al día</h3>
                <p>Nuestros abogados revisan y actualizan los formatos cuando cambia la ley o la jurisprudencia. Ningún documento sale citando una norma derogada.</p>
            </div>
            <div class="feature">
                <div class="feature__icon" aria-hidden="true">✍️</div>
                <h3>Firma electrónica válida</h3>
                <p>Firma contratos desde el celular con código OTP. Plena validez probatoria según la Ley 527 de 1999.</p>
            </div>
            <div class="feature">
                <div class="feature__icon" aria-hidden="true">📱</div>
                <h3>Entrega multicanal</h3>
                <p>Sin plataformas confusas: el PDF llega a tu correo y a tu WhatsApp, con instrucciones de radicación.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ TESTIMONIOS --}}
@if ($testimonials->isNotEmpty())
<section class="section section--dark">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Casos reales</span>
            <h2>Lo que resolvieron <span class="hl hl--blue hl--right">nuestros clientes</span></h2>
            <p>Resultados concretos, no adjetivos.</p>
        </div>
    </div>

    <div class="marquee" aria-label="Testimonios de clientes">
        <div class="marquee__track">
            @foreach ($testimonials->concat($testimonials) as $testimonial)
                <figure class="quote">
                    <div class="quote__stars" aria-label="{{ $testimonial->rating }} de 5 estrellas">
                        {{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}
                    </div>
                    @if ($testimonial->outcome)
                        <span class="pill pill--success quote__outcome">✓ {{ $testimonial->outcome }}</span>
                    @endif
                    <blockquote class="quote__text">{{ $testimonial->quote }}</blockquote>
                    <figcaption class="quote__who">
                        <span class="quote__avatar" aria-hidden="true">{{ $testimonial->initials() }}</span>
                        <span>
                            <strong>{{ $testimonial->author_name }}</strong>
                            <span>{{ collect([$testimonial->author_role, $testimonial->author_city])->filter()->implode(' · ') }}</span>
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================ EQUIPO (E-E-A-T) --}}
@if ($team->isNotEmpty())
<section class="section section--white">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Quién está detrás</span>
            <h2>Abogados colombianos, <span class="hl hl--blue hl--right">con nombre y apellido</span></h2>
            <p>
                Nuestro equipo jurídico escribe y mantiene los formatos. Y cuando tu caso necesita
                a alguien que lo lleve, estos son los abogados de la red que lo toman.
            </p>
        </div>

        <div class="grid grid--4 reveal">
            @foreach ($team as $lawyer)
                <x-lawyer-card :lawyer="$lawyer" />
            @endforeach
        </div>

        <div class="center mt-10">
            <a href="{{ route('lawyers.index') }}" class="btn btn--outline">Ver el directorio completo</a>
        </div>
    </div>
</section>
@endif

{{-- ============================================ BLOG --}}
@if ($posts->isNotEmpty())
<section class="section section--sunken">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Guías gratis</span>
            <h2>Entiende tu trámite <span class="hl hl--blue">en cinco minutos</span></h2>
            <p>Qué dice la ley, qué plazos tienes y qué hacer si no te responden. Explicado en español.</p>
        </div>

        <div class="grid grid--3 reveal">
            @foreach ($posts as $post)
                <article class="post-card">
                    <div class="post-card__cover" aria-hidden="true">{{ $post->category?->emoji ?: '📘' }}</div>
                    <div class="post-card__body">
                        @if ($post->category)
                            <span class="pill pill--brand" style="align-self: flex-start; margin-bottom: var(--s3);">{{ $post->category->name }}</span>
                        @endif
                        <h3><a href="{{ $post->url() }}">{{ $post->title }}</a></h3>
                        <p>{{ \Illuminate\Support\Str::limit($post->excerpt, 110) }}</p>
                        <div class="post-card__meta">
                            <span>{{ $post->reading_minutes }} min de lectura</span>
                            @if ($post->author)
                                <span aria-hidden="true">·</span>
                                <span>Por {{ $post->author->name }}</span>
                            @else
                                <span aria-hidden="true">·</span>
                                <span>Equipo {{ config('hazlolegal.brand.name') }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="center mt-10">
            <a href="{{ route('blog.index') }}" class="btn btn--outline">Ver todas las guías</a>
        </div>
    </div>
</section>
@endif

{{-- ============================================ FAQ --}}
@if ($faqs->isNotEmpty())
<section class="section section--white" id="faq">
    <div class="container container--narrow">
        <div class="section-head reveal">
            <span class="eyebrow">Preguntas frecuentes</span>
            <h2>Lo que todos <span class="hl hl--blue">nos preguntan</span></h2>
        </div>

        <div class="reveal">
            <x-faq-list :faqs="$faqs" :open="1" />
        </div>

        <div class="alert alert--info mt-10">
            <span class="alert__icon" aria-hidden="true">💬</span>
            <div>
                <strong>¿Te quedó una duda que no está aquí?</strong>
                <p style="margin-top: var(--s1);">
                    Escríbenos por WhatsApp a <a href="{{ $wa->link('Hola, tengo una duda antes de pedir un trámite.') }}" target="_blank" rel="noopener">{{ $wa->display() }}</a>.
                    Responde una persona del equipo, no un bot.
                </p>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ============================================ PARA ABOGADOS --}}
<section class="section section--sunken" id="abogados">
    <div class="container">
        <div class="guarantee reveal" style="background: var(--ink); color: #fff; border-color: var(--ink);">
            <div class="guarantee__grid">
                <div>
                    <span class="eyebrow" style="color: var(--amber-400);">¿Eres abogado?</span>
                    <h2 style="margin-bottom: var(--s3); color: #fff;">
                        Nosotros atraemos a quien tiene el problema. Tú atiendes el caso
                    </h2>
                    <p style="font-size: var(--t-lg); color: #CBD5E1;">
                        Cada mes miles de personas llegan aquí buscando cómo resolver algo legal.
                        La mayoría se lo resuelve sola con un formato. Una parte necesita a alguien
                        como tú, y hoy no sabe a quién llamar.
                    </p>

                    <ul class="guarantee-list" style="color: #CBD5E1;">
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong style="color:#fff;">Perfil público en el directorio</strong>
                                Con tu especialidad, tu ciudad y tu forma de atender. Indexable en Google.
                            </span>
                        </li>
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong style="color:#fff;">Apareces en los instructivos que circulan</strong>
                                El PDF que el cliente imprime y lleva a la ventanilla lleva tu nombre.
                            </span>
                        </li>
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong style="color:#fff;">Aviso cuando alguien pide acompañamiento</strong>
                                En tu área y tu ciudad. Te postulas y el cliente elige entre los interesados.
                            </span>
                        </li>
                        <li>
                            <span class="guarantee-list__check" aria-hidden="true">✓</span>
                            <span>
                                <strong style="color:#fff;">Contactos que autorizaron ser contactados</strong>
                                Solo de quien marcó esa casilla. Sin listas compradas y sin sorpresas con la SIC.
                            </span>
                        </li>
                    </ul>

                    <div class="btn-row mt-8">
                        <a href="{{ route('lawyers.join') }}" class="btn btn--action btn--lg">Ver planes y registrarme</a>
                        <a href="{{ route('lawyers.index') }}" class="btn btn--white">Ver el directorio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ CTA FINAL --}}
<x-cta-band
    eyebrow="Última cosa"
    title="Los términos legales no esperan a que estés listo"
    text="Una tutela tiene plazos. Un contrato sin firmar te deja expuesto. Un reporte en Datacrédito sigue bloqueando tu crédito mientras lo piensas. Resuélvelo hoy por el precio de una consulta."
    primary-label="Ver trámites y precios"
    :primary-href="route('documents.index')"
    secondary-label="Hablar con un abogado"
    :secondary-href="$wa->link('Hola, quiero que un abogado me oriente sobre mi caso.')"
    note="Atención de lunes a sábado, 7:00 a.m. a 9:00 p.m." />
@endsection
