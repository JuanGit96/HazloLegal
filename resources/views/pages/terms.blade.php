@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--prose">
        <h1>Términos y condiciones</h1>
        <p class="text-muted">Última actualización: {{ now()->translatedFormat('j \d\e F \d\e Y') }}</p>

        @include('pages._draft-notice')

        <div class="prose">
            <h2>1. Quién presta el servicio</h2>
            <p>
                Este sitio es operado por {{ config('hazlolegal.brand.legal_name') }}, NIT
                {{ config('hazlolegal.brand.nit') }}, con domicilio en {{ config('hazlolegal.contact.address') }},
                {{ config('hazlolegal.contact.city') }}, Colombia (en adelante, «Hazlo Legal»).
            </p>

            <h2>2. Qué es y qué no es este servicio</h2>
            <p>
                Hazlo Legal presta servicios de <strong>elaboración de documentos jurídicos</strong> y, en la
                modalidad de caso complejo, facilita el contacto con abogados independientes inscritos en su red.
            </p>
            <p>
                <strong>El uso de este sitio no crea una relación abogado-cliente.</strong> Dicha relación surge
                únicamente cuando se confirma el pago del servicio y Hazlo Legal acepta expresamente el encargo,
                o cuando un abogado de la red acepta un caso complejo y celebra directamente con el usuario el
                correspondiente contrato de prestación de servicios profesionales.
            </p>
            <p>
                La información publicada en el blog y en las fichas de producto es de carácter general y
                orientativo. No constituye asesoría jurídica particular y no debe usarse como único fundamento
                para tomar decisiones.
            </p>

            <h2>3. Alcance de cada plan</h2>
            <ul>
                <li>
                    <strong>Documento listo para usar.</strong> Entrega de un documento jurídico en formato PDF
                    (y Word cuando aplique), redactado o ensamblado con base en la información que suministre el
                    usuario y auditado por un abogado titulado. Incluye una ronda de ajustes.
                </li>
                <li>
                    <strong>Documento + asesoría.</strong> Lo anterior, más una videollamada de treinta (30)
                    minutos previa a la redacción y dos rondas de ajustes.
                </li>
                <li>
                    <strong>Caso complejo.</strong> Hazlo Legal recibe la descripción del caso y la remite a los
                    abogados de su red. La cotización no tiene costo. Los honorarios, el alcance y la
                    responsabilidad profesional se acuerdan directamente entre el usuario y el abogado que acepte
                    el caso.
                </li>
            </ul>

            <h2>4. Obligaciones del usuario</h2>
            <p>El usuario se obliga a:</p>
            <ul>
                <li>Suministrar información veraz, completa y oportuna.</li>
                <li>Verificar sus datos y los de la contraparte antes de confirmar el pedido.</li>
                <li>Revisar el documento entregado antes de firmarlo o radicarlo.</li>
                <li>No utilizar el servicio para fines ilícitos o contrarios al orden público.</li>
            </ul>
            <p>
                Hazlo Legal no responde por errores derivados de información inexacta, incompleta o
                extemporánea suministrada por el usuario.
            </p>

            <h2>5. Tiempos de entrega</h2>
            <p>
                Los plazos publicados (24 horas hábiles en el plan estándar y menos de 4 horas en el plan
                Express) se cuentan desde que concurren dos condiciones: (i) la confirmación del pago y
                (ii) la recepción completa de la información necesaria. Los días hábiles excluyen domingos
                y festivos en Colombia.
            </p>

            <h2>6. Precios y pago</h2>
            <p>
                Los precios están expresados en pesos colombianos (COP) e incluyen los impuestos aplicables.
                El pago es previo a la prestación del servicio. Hazlo Legal no solicita datos de tarjetas de
                crédito o débito por WhatsApp, por teléfono ni por ningún canal distinto de la pasarela de pago
                del sitio.
            </p>

            <h2>7. Garantía y devoluciones</h2>
            <p>
                Las condiciones de corrección y devolución se rigen por el
                <a href="{{ route('pages.guarantee') }}">Certificado de Garantía Hazlo Legal</a>,
                que forma parte integral de estos términos. En particular, Hazlo Legal
                <strong>no garantiza el resultado</strong> de la decisión de un juez, de una entidad pública o
                de un particular, por tratarse de hechos ajenos a su control.
            </p>
            <p>
                Lo anterior sin perjuicio de los derechos que el Estatuto del Consumidor
                (Ley 1480 de 2011) reconoce al usuario.
            </p>

            <h2>8. Firma electrónica</h2>
            <p>
                Cuando el servicio incluya firma electrónica, esta se presta conforme a la Ley 527 de 1999 y al
                Decreto 2364 de 2012. El usuario reconoce que ciertos actos requieren escritura pública o firma
                digital certificada y que, en esos casos, la firma electrónica no es suficiente. Ver
                <a href="{{ route('pages.signature') }}">validez de la firma electrónica</a>.
            </p>

            <h2>9. Propiedad intelectual</h2>
            <p>
                Los documentos entregados al usuario son para su uso particular en el caso descrito.
                El contenido del sitio, las plantillas maestras, las marcas y el software son propiedad de
                Hazlo Legal y no pueden reproducirse ni explotarse comercialmente sin autorización escrita.
            </p>

            <h2>10. Confidencialidad</h2>
            <p>
                La información del usuario se trata de forma confidencial y solo accede a ella el personal y
                los abogados necesarios para prestar el servicio, quienes están sujetos a reserva profesional.
            </p>

            <h2>11. Limitación de responsabilidad</h2>
            <p>
                La responsabilidad de Hazlo Legal se limita al valor pagado por el servicio.
                En la modalidad de caso complejo, Hazlo Legal actúa como intermediario tecnológico:
                la responsabilidad profesional por la atención del caso recae en el abogado que lo acepte.
            </p>

            <h2>12. Modificaciones</h2>
            <p>
                Hazlo Legal puede modificar estos términos. La versión vigente es la publicada en esta página,
                con su fecha de actualización. Los pedidos ya confirmados se rigen por la versión vigente al
                momento del pago.
            </p>

            <h2>13. Ley aplicable y solución de conflictos</h2>
            <p>
                Estos términos se rigen por la ley colombiana. Las controversias se intentarán resolver de
                buena fe y de forma directa; en su defecto, se someterán a los jueces competentes de
                {{ config('hazlolegal.contact.city') }}.
            </p>

            <h2>14. Contacto</h2>
            <p>
                Para cualquier solicitud, queja o reclamo:
                <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>
                o WhatsApp {{ config('hazlolegal.contact.whatsapp_display') }}.
            </p>
        </div>
    </div>
</section>
@endsection
