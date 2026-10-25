@extends('layouts.site')

@section('content')
@php
    $wa = app(\App\Domain\Shared\Services\WhatsAppLinkBuilder::class);
    $trust = config('hazlolegal.trust');
    $documentPlan = $plans->firstWhere('key', \App\Domain\Catalog\Enums\PlanKey::Document);
    $entry = $pricing->formatCop($entryPrice);
    // Promesa honesta y calculada, no una cuenta regresiva falsa.
    $cutoff = now()->setTime(15, 0);
    $sameDay = now()->isWeekday() && now()->lt($cutoff);
@endphp

{{-- ============================================ HERO --}}
<section class="hero">
    <div class="container hero__grid">
        <div>
            <div class="hero__kicker">
                <span class="pill pill--success">✓ Abogados con tarjeta profesional</span>
                <span class="pill pill--brand">Ley colombiana vigente</span>
            </div>

            <h1>Tutelas, contratos y derechos de petición <span class="accent">hechos por abogados colombianos</span></h1>

            <p class="hero__lead">
                Certeza legal, sin honorarios sorpresa. Recibe tu documento listo para radicar
                en 24 horas &mdash; o en menos de 4 si tienes un término encima.
            </p>

            <div class="btn-row">
                <a href="#catalogo" class="btn btn--primary btn--lg">Buscar mi trámite</a>
                <a href="#precios" class="btn btn--outline btn--lg">Ver precios desde {{ $entry }}</a>
            </div>

            <p class="btn-note">
                @if ($sameDay)
                    🕒 Si pides el plan Express antes de las 3:00 p.m., lo recibes <strong>hoy mismo</strong>.
                @else
                    🕒 Pedidos Express recibidos ahora se entregan mañana a primera hora.
                @endif
            </p>

            <div class="hero__proof">
                <div class="hero__proof-item">
                    <strong>{{ number_format($trust['documents_delivered'], 0, ',', '.') }}+</strong>
                    <span>documentos entregados</span>
                </div>
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
                    <span class="doc-preview__name">accion-de-tutela-salud.pdf</span>
                </div>
                <div class="doc-preview__body">
                    <div class="doc-preview__title">Acción de tutela</div>
                    <div class="doc-preview__line doc-preview__line--accent"></div>
                    <div class="doc-preview__line"></div>
                    <div class="doc-preview__line doc-preview__line--mid"></div>
                    <div class="doc-preview__line"></div>
                    <div class="doc-preview__line doc-preview__line--short"></div>

                    <div class="doc-preview__seal">
                        <span>✓</span>
                        <span>Auditado por abogado titulado · T.P. 284.912</span>
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

{{-- ============================================ TIRA DE CONFIANZA --}}
<div class="trust-strip">
    <div class="container">
        <p class="trust-strip__label">Por qué puedes confiarnos tu trámite</p>
        <div class="trust-strip__items">
            <span class="trust-item"><span class="trust-item__icon" aria-hidden="true">⚖️</span> <span><strong>Abogados verificables</strong> · T.P. del C.S. de la J.</span></span>
            <span class="trust-item"><span class="trust-item__icon" aria-hidden="true">✍️</span> <span><strong>Firma electrónica</strong> · Ley 527 de 1999</span></span>
            <span class="trust-item"><span class="trust-item__icon" aria-hidden="true">🔒</span> <span><strong>Datos protegidos</strong> · Ley 1581 de 2012</span></span>
            <span class="trust-item"><span class="trust-item__icon" aria-hidden="true">🛡️</span> <span><strong>Garantía por escrito</strong> · corregimos o devolvemos</span></span>
            <span class="trust-item"><span class="trust-item__icon" aria-hidden="true">💳</span> <span><strong>Pago seguro</strong> · Nequi, PSE o tarjeta</span></span>
        </div>
    </div>
</div>

{{-- ============================================ CATÁLOGO --}}
<section class="section section--white" id="catalogo">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Catálogo</span>
            <h2>¿Qué necesitas resolver hoy?</h2>
            <p>Precio fijo publicado y tiempo de entrega comprometido. Entra a tu caso y mira exactamente qué recibes.</p>
        </div>

        <div class="catalog-grid reveal">
            @foreach ($documents as $document)
                <x-doc-card
                    :document="$document"
                    :price="$documentPlan ? $pricing->formatCop($pricing->quote($document, $documentPlan)->amount) : null" />
            @endforeach
        </div>

        <p class="center mt-8 text-sm text-muted">
            ¿No ves tu caso? <a href="{{ route('cases.create') }}"><strong>Cuéntanos tu situación</strong></a>
            y un abogado te cotiza sin costo.
        </p>
    </div>
</section>

{{-- ============================================ ANCLAJE DE PRECIO / COMPARATIVA --}}
<section class="section section--sunken">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Compara antes de decidir</span>
            <h2>¿Por qué pagar si hay plantillas e IA gratis?</h2>
            <p>Porque un documento rechazado no es gratis: cuesta tiempo, términos vencidos y a veces el derecho mismo.</p>
        </div>

        <div class="compare-wrap reveal">
            <table class="compare">
                <caption class="visually-hidden">Comparación entre Hazlo Legal, plantillas de internet, inteligencia artificial gratuita y un abogado tradicional</caption>
                <thead>
                    <tr>
                        <th scope="col">&nbsp;</th>
                        <th scope="col" class="is-us">Hazlo Legal</th>
                        <th scope="col">Plantilla de internet</th>
                        <th scope="col">IA gratuita</th>
                        <th scope="col">Abogado tradicional</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">Precio</th>
                        <td class="is-us">Desde {{ $entry }}</td>
                        <td>Gratis</td>
                        <td>Gratis</td>
                        <td>$300.000 &ndash; $800.000</td>
                    </tr>
                    <tr>
                        <th scope="row">Revisado por abogado titulado</th>
                        <td class="is-us"><span class="yes">Sí</span>, con T.P. verificable</td>
                        <td><span class="no">No</span></td>
                        <td><span class="no">No</span></td>
                        <td><span class="yes">Sí</span></td>
                    </tr>
                    <tr>
                        <th scope="row">Normas colombianas vigentes</th>
                        <td class="is-us"><span class="yes">Sí</span></td>
                        <td>Normalmente desactualizadas</td>
                        <td>Mezcla leyes de otros países</td>
                        <td><span class="yes">Sí</span></td>
                    </tr>
                    <tr>
                        <th scope="row">Adaptado a tus hechos</th>
                        <td class="is-us"><span class="yes">Sí</span></td>
                        <td><span class="no">No</span></td>
                        <td>Parcial, sin criterio jurídico</td>
                        <td><span class="yes">Sí</span></td>
                    </tr>
                    <tr>
                        <th scope="row">Tiempo de entrega</th>
                        <td class="is-us">4 a 24 horas</td>
                        <td>Inmediato</td>
                        <td>Inmediato</td>
                        <td>3 a 15 días</td>
                    </tr>
                    <tr>
                        <th scope="row">Garantía si lo rechazan</th>
                        <td class="is-us"><span class="yes">Sí</span>, por escrito</td>
                        <td><span class="no">Ninguna</span></td>
                        <td><span class="no">Ninguna</span></td>
                        <td>Depende del acuerdo</td>
                    </tr>
                    <tr>
                        <th scope="row">Responsable si algo falla</th>
                        <td class="is-us">Nosotros</td>
                        <td>Tú</td>
                        <td>Tú</td>
                        <td>El abogado</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="center mt-6 text-sm text-muted">
            Una tutela mal redactada se puede rechazar por defectos de forma. Rehacerla cuesta semanas &mdash;
            y en salud, semanas son lo único que no sobra.
        </p>
    </div>
</section>

{{-- ============================================ PRECIOS --}}
<section class="section section--white" id="precios">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Precios</span>
            <h2>Un precio claro antes de empezar</h2>
            <p>Sin anticipos, sin «depende». Eliges el nivel de acompañamiento que tu caso necesita.</p>
        </div>

        <div class="pricing-grid reveal">
            @foreach ($plans as $plan)
                @php
                    $isComplex = $plan->is_quote_only;
                    $isFeatured = $plan->is_highlighted;
                    $href = $isComplex ? route('cases.create') : route('checkout.documents', $plan->key->value);
                    $upgrade = $pricing->expressUpgradeCost($plan);
                @endphp

                <div @class(['plan', 'plan--featured' => $isFeatured, 'plan--dark' => $isComplex])>
                    @if ($plan->badge)
                        <span class="badge-float">{{ $plan->badge }}</span>
                    @endif

                    <div class="plan__head">
                        <h3>{{ $plan->name }}</h3>
                        <p class="plan__tagline">{{ $plan->tagline }}</p>
                    </div>

                    <div class="plan__price">
                        @if ($plan->price === null)
                            Cotizar
                        @else
                            @if ($plan->key === \App\Domain\Catalog\Enums\PlanKey::Document)
                                <span style="font-size: var(--t-lg); font-weight: 600; color: var(--text-subtle);">desde</span>
                            @endif
                            {{ $pricing->formatCop($plan->price) }}
                            <small>COP</small>
                        @endif
                    </div>

                    <p class="plan__anchor">
                        @if ($isComplex)
                            La cotización no cuesta nada y no te compromete.
                        @elseif ($plan->delivery_hours)
                            Entrega en {{ $plan->delivery_hours }} horas hábiles · pago único
                        @endif
                    </p>

                    <ul class="plan__features">
                        @foreach ($plan->includes ?? [] as $feature)
                            <li>{!! \Illuminate\Support\Str::inlineMarkdown($feature) !!}</li>
                        @endforeach
                    </ul>

                    {{-- El Express no es una tarjeta aparte: es una opción dentro de este plan. --}}
                    @if ($plan->hasSpeedChoice() && $upgrade)
                        <div class="speed-note">
                            <strong>¿Lo necesitas hoy?</strong> Al elegir tu trámite puedes subir a
                            <strong>Express (menos de 4 horas)</strong> por {{ $pricing->formatCop($upgrade) }} más.
                        </div>
                    @endif

                    <a href="{{ $href }}"
                       @class(['btn', 'btn--block', 'mt-6',
                               'btn--primary' => $isFeatured,
                               'btn--white' => $isComplex,
                               'btn--outline' => ! $isFeatured && ! $isComplex])>
                        {{ $plan->cta_label }}
                    </a>

                    <p class="plan__foot">
                        @if ($isComplex)
                            Te responde un abogado, no un bot.
                        @else
                            Cubierto por el Certificado de Garantía
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <p class="center mt-8 text-sm text-muted">
            Todos los planes incluyen revisión de un abogado titulado y están cubiertos por el
            <a href="{{ route('pages.guarantee') }}"><strong>Certificado de Garantía Hazlo Legal</strong></a>.
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
                    <h2 style="margin-bottom: var(--s3);">Certificado de Garantía Hazlo Legal</h2>
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
            <h2>De tu problema al documento firmado, en 4 pasos</h2>
            <p>Sin desplazamientos, sin salas de espera y sin cobrar la hora de consulta.</p>
        </div>

        <div class="steps reveal">
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">1</div>
                <h3>Eliges tu trámite</h3>
                <p>Precio y plazo visibles antes de pagar. Si tienes afán, subes a Express.</p>
            </div>
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">2</div>
                <h3>Nos cuentas tu caso</h3>
                <p>Un formulario guiado que valida cada dato antes de pasarlo al abogado.</p>
            </div>
            <div class="step step--center">
                <div class="step__num" aria-hidden="true">3</div>
                <h3>Un abogado lo audita</h3>
                <p>Revisa hechos, normas citadas, ortografía y el respeto formal ante la entidad.</p>
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
            <h2>Un flujo diseñado para que no te rechacen el documento</h2>
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
                <h3>Auditoría jurídica</h3>
                <p>Un abogado titulado revisa que las normas citadas estén vigentes y que el tono ante la entidad sea el correcto.</p>
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
            <h2>Lo que resolvieron nuestros clientes</h2>
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
            <span class="eyebrow">Quién redacta</span>
            <h2>Abogados reales, con tarjeta profesional verificable</h2>
            <p>Puedes consultar cada tarjeta en el registro del Consejo Superior de la Judicatura.</p>
        </div>

        <div class="grid grid--4 reveal">
            @foreach ($team as $lawyer)
                <x-lawyer-card :lawyer="$lawyer" />
            @endforeach
        </div>

        <div class="center mt-10">
            <a href="{{ route('pages.about') }}" class="btn btn--outline">Conocer al equipo</a>
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
            <h2>Aprende antes de pagar</h2>
            <p>Explicamos cada trámite paso a paso. Si después de leer lo quieres hacer solo, nos parece bien.</p>
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
                                <span>Revisado por {{ $post->author->name }}</span>
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
            <h2>Lo que todos nos preguntan antes de pagar</h2>
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
