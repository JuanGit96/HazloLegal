@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--prose">
        <h1>Política de tratamiento de datos personales</h1>
        <p class="text-muted">Última actualización: {{ now()->translatedFormat('j \d\e F \d\e Y') }}</p>

        @include('pages._draft-notice')

        <div class="prose">
            <h2>1. Responsable del tratamiento</h2>
            <p>
                {{ config('hazlolegal.brand.legal_name') }}, NIT {{ config('hazlolegal.brand.nit') }},
                con domicilio en {{ config('hazlolegal.contact.address') }},
                {{ config('hazlolegal.contact.city') }}, Colombia.
                Correo para el ejercicio de derechos:
                <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>.
            </p>

            <h2>2. Marco legal</h2>
            <p>
                Esta política se adopta conforme a la Ley 1581 de 2012, el Decreto 1377 de 2013 y las demás
                normas que regulan la protección de datos personales en Colombia.
            </p>

            <h2>3. Datos que recolectamos</h2>
            <ul>
                <li><strong>De identificación y contacto:</strong> nombre, tipo y número de documento, correo, celular y ciudad.</li>
                <li><strong>Del caso:</strong> la descripción de los hechos y los documentos que el usuario adjunte o describa.</li>
                <li><strong>De la transacción:</strong> referencia del pedido, medio de pago y valor. No almacenamos números completos de tarjetas.</li>
                <li><strong>Técnicos:</strong> dirección IP, fecha y hora de las interacciones, y datos de navegación necesarios para la seguridad del servicio.</li>
            </ul>

            <h2>4. Datos sensibles</h2>
            <p>
                Algunos trámites &mdash;en particular los de salud&mdash; pueden implicar el tratamiento de
                <strong>datos sensibles</strong>, como información sobre el estado de salud. El suministro de
                estos datos es <strong>facultativo</strong> y el usuario no está obligado a entregarlos; sin
                embargo, sin ellos puede no ser posible prestar el servicio solicitado.
                Al enviarlos, el usuario autoriza expresamente su tratamiento para esa única finalidad.
            </p>

            <h2>5. Finalidades</h2>
            <ul>
                <li>Prestar el servicio de elaboración de documentos y gestionar el pedido.</li>
                <li>Remitir los casos complejos a los abogados de la red para su evaluación y aceptación.</li>
                <li>Comunicar el estado del trámite por correo electrónico y WhatsApp.</li>
                <li>Atender solicitudes, quejas, reclamos y reclamaciones de garantía.</li>
                <li>Cumplir obligaciones legales, contables y tributarias.</li>
                <li>Enviar contenido informativo, únicamente cuando el usuario lo haya autorizado, con opción de baja en cada envío.</li>
            </ul>

            <h2>6. Con quién compartimos los datos</h2>
            <p>
                Compartimos únicamente lo necesario, y solo con:
            </p>
            <ul>
                <li><strong>Abogados de nuestra red</strong>, cuando el usuario envía un caso complejo. Están sujetos a reserva profesional.</li>
                <li><strong>Proveedores de infraestructura</strong> (alojamiento, correo transaccional, pasarela de pago), en calidad de encargados y bajo acuerdos de confidencialidad.</li>
                <li><strong>Autoridades</strong>, cuando exista requerimiento legal.</li>
            </ul>
            <p><strong>No vendemos datos personales ni los cedemos con fines publicitarios de terceros.</strong></p>

            <h2>7. Derechos del titular</h2>
            <p>Como titular de tus datos tienes derecho a:</p>
            <ul>
                <li>Conocer, actualizar y rectificar tus datos.</li>
                <li>Solicitar prueba de la autorización otorgada.</li>
                <li>Ser informado sobre el uso que se ha dado a tus datos.</li>
                <li>Presentar quejas ante la Superintendencia de Industria y Comercio.</li>
                <li>Revocar la autorización y solicitar la supresión, cuando no exista un deber legal o contractual de conservarlos.</li>
                <li>Acceder de forma gratuita a tus datos.</li>
            </ul>

            <h2>8. Cómo ejercer tus derechos</h2>
            <p>
                Escribe a <a href="mailto:{{ config('hazlolegal.contact.email') }}">{{ config('hazlolegal.contact.email') }}</a>
                indicando tu nombre, documento, la solicitud concreta y un canal de respuesta.
                Atendemos las consultas en un máximo de <strong>diez (10) días hábiles</strong> y los reclamos
                en un máximo de <strong>quince (15) días hábiles</strong>, prorrogables conforme a la ley.
            </p>

            <h2>9. Conservación</h2>
            <p>
                Conservamos los datos mientras exista una relación contractual y, después, por el término
                necesario para atender reclamaciones de garantía y cumplir obligaciones legales de conservación
                documental. Vencidos esos plazos, se suprimen o anonimizan.
            </p>

            <h2>10. Seguridad</h2>
            <p>
                Aplicamos medidas técnicas, humanas y administrativas razonables para proteger los datos:
                cifrado en tránsito, control de acceso por roles y registro de actividad. Ningún sistema es
                infalible; si ocurriera un incidente que afecte datos personales, lo informaremos a los
                titulares y a la autoridad conforme a la ley.
            </p>

            <h2>11. Cookies</h2>
            <p>
                Usamos las cookies estrictamente necesarias para el funcionamiento del sitio y la sesión.
                No instalamos cookies de publicidad de terceros sin consentimiento previo.
            </p>

            <h2>12. Vigencia</h2>
            <p>
                Esta política rige desde su publicación. Las modificaciones se informarán en esta misma página.
            </p>
        </div>
    </div>
</section>
@endsection
