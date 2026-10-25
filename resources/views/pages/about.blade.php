@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--narrow">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <div class="section-head">
            <span class="eyebrow">Quiénes somos</span>
            <h1>Abogados que se cansaron de cobrar por lo repetitivo</h1>
            <p>
                Un trámite estándar no debería costar lo mismo que un litigio.
                Separamos lo que una máquina puede ensamblar de lo que exige criterio jurídico,
                y cobramos cada cosa por lo que vale.
            </p>
        </div>

        <div class="prose">
            <h2>El problema que vimos</h2>
            <p>
                Un trámite estándar cuesta en una oficina de abogados entre $300.000 y $800.000, aunque su
                estructura sea la misma del 80 % de los casos. Hay que pedir cita, desplazarse en horario
                de oficina y esperar días para saber cuánto se va a pagar.
            </p>
            <p>
                El resultado es que miles de personas con un problema legal real y un término corriendo
                terminan sintiendo que pedir ayuda es un lujo. Y un término que se vence no se recupera.
            </p>

            <h2>Qué hicimos</h2>
            <p>
                Clasificamos los trámites más frecuentes del país en dos grupos.
                Los que tienen una estructura estable &mdash;un NDA, una promesa de compraventa,
                un derecho de petición de habeas data&mdash; los convertimos en plantillas maestras
                que nuestros abogados escribieron, validaron y mantienen actualizadas. El sistema
                las ensambla con tus datos y un abogado audita el resultado antes de enviarlo.
            </p>
            <p>
                Los que exigen narrar hechos, hacer cálculos o escribir cláusulas condicionales
                &mdash;una tutela, una liquidación laboral, unos estatutos de SAS&mdash;
                los sigue redactando un abogado desde cero, porque automatizarlos produciría
                documentos inservibles. Y lo decimos en cada ficha de producto.
            </p>

            <h2>Lo que no hacemos</h2>
            <ul>
                <li><strong>No prometemos resultados.</strong> Nadie puede garantizar la decisión de un juez.</li>
                <li><strong>No escondemos el precio.</strong> Está publicado antes de que des tus datos.</li>
                <li><strong>No cobramos por orientarte.</strong> Si tu caso no necesita nuestro servicio, te lo decimos.</li>
                <li><strong>No inventamos credenciales.</strong> No publicamos números de tarjeta ni sellos que no signifiquen nada: preferimos explicarte cómo se hace cada documento.</li>
            </ul>

            <h2>Quién hace qué, sin letra menuda</h2>
            <p>
                En los trámites de <strong>Fase 1</strong> el documento se arma solo, sobre un formato que
                escribió un abogado de nuestro equipo y que mantenemos actualizado. Nadie lo revisa caso por
                caso, y por eso cuesta $90.000 y no $400.000. Lo decimos en la ficha de cada trámite.
            </p>
            <p>
                En los de <strong>Fase 2</strong> y en los casos complejos sí hay un abogado leyendo tu caso
                y escribiendo para ti. Ahí el precio es otro, y también lo decimos antes de cobrar.
            </p>
        </div>
    </div>
</section>

@if ($team->isNotEmpty())
<section class="section section--sunken">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">El equipo</span>
            <h2>Quién escribe los formatos</h2>
            <p>Abogados colombianos en ejercicio. Ellos escriben los formatos del catálogo y toman los casos complejos.</p>
        </div>

        <div class="grid grid--3">
            @foreach ($team as $lawyer)
                <x-lawyer-card :lawyer="$lawyer" />
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section section--white section--tight">
    <div class="container container--narrow">
        <div class="card card--pad-lg">
            <h2 style="font-size: var(--t-lg);">Datos de la empresa</h2>
            <dl class="summary__rows" style="margin-bottom: 0; margin-top: var(--s5);">
                <div class="summary__row"><dt>Razón social</dt><dd>{{ config('hazlolegal.brand.legal_name') }}</dd></div>
                <div class="summary__row"><dt>NIT</dt><dd>{{ config('hazlolegal.brand.nit') }}</dd></div>
                <div class="summary__row"><dt>Dirección</dt><dd>{{ config('hazlolegal.contact.address') }}</dd></div>
                <div class="summary__row"><dt>Ciudad</dt><dd>{{ config('hazlolegal.contact.city') }}, Colombia</dd></div>
                <div class="summary__row"><dt>Correo</dt><dd>{{ config('hazlolegal.contact.email') }}</dd></div>
                <div class="summary__row"><dt>WhatsApp</dt><dd>{{ config('hazlolegal.contact.whatsapp_display') }}</dd></div>
                <div class="summary__row"><dt>Horario</dt><dd>{{ config('hazlolegal.contact.hours') }}</dd></div>
            </dl>
        </div>
    </div>
</section>

<x-cta-band
    title="¿Quieres que trabajemos tu caso?"
    text="Empieza por el catálogo si tu trámite es estándar, o cuéntanos tu situación si es más complicada."
    primary-label="Ver trámites"
    :primary-href="route('documents.index')"
    secondary-label="Contar mi caso"
    :secondary-href="route('cases.create')" />
@endsection
