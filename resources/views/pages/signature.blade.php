@extends('layouts.site')

@section('content')
<section class="section section--white">
    <div class="container container--prose">
        <x-breadcrumbs :items="$meta->breadcrumbs" />

        <h1>¿Es válida la firma electrónica en Colombia?</h1>
        <p class="text-muted" style="font-size: var(--t-lg);">
            Sí, y tiene plena fuerza probatoria. Pero no sirve para todo.
            Te explicamos cuándo basta, cuándo necesitas firma digital certificada
            y cuándo la ley exige escritura pública.
        </p>

        <div class="prose">
            <h2>La respuesta corta</h2>
            <p>
                La <strong>Ley 527 de 1999</strong> equiparó el mensaje de datos al documento escrito y la firma
                electrónica a la firma manuscrita. El <strong>Decreto 2364 de 2012</strong> precisó los requisitos:
                una firma electrónica es válida cuando es <em>confiable y apropiada</em> para el propósito del
                mensaje, lo que exige que permita <strong>identificar al firmante</strong> y
                <strong>vincular su voluntad</strong> con el contenido.
            </p>
            <p>
                Un código de un solo uso (OTP) enviado al celular, junto con la verificación del correo
                electrónico y el registro de la fecha, la hora y la dirección IP, cumple esos requisitos.
            </p>

            <h2>Firma electrónica vs. firma digital: no son lo mismo</h2>
            <table>
                <thead>
                    <tr><th>&nbsp;</th><th>Firma electrónica</th><th>Firma digital</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Qué es</th>
                        <td>Cualquier mecanismo que identifique al firmante: OTP, clave, trazo biométrico.</td>
                        <td>Una especie más robusta, basada en certificados emitidos por una entidad de certificación acreditada por la ONAC.</td>
                    </tr>
                    <tr>
                        <th>Validez</th>
                        <td>Válida, con fuerza probatoria.</td>
                        <td>Válida, con <strong>presunción legal</strong> de autenticidad e integridad.</td>
                    </tr>
                    <tr>
                        <th>Costo</th>
                        <td>Bajo o nulo.</td>
                        <td>Requiere pagar el certificado.</td>
                    </tr>
                    <tr>
                        <th>Cuándo usarla</th>
                        <td>Contratos privados: arrendamientos, NDA, promesas de compraventa, prestación de servicios.</td>
                        <td>Trámites que la exigen expresamente: factura electrónica, algunos trámites ante entidades públicas.</td>
                    </tr>
                </tbody>
            </table>

            <h2>Lo que de verdad decide una disputa: el rastro de auditoría</h2>
            <p>
                Que la firma sea válida en abstracto no sirve de mucho si no puedes probar quién firmó.
                Por eso lo importante es la evidencia que queda guardada:
            </p>
            <ul>
                <li>La fecha y hora exactas de cada firma.</li>
                <li>La dirección IP desde la que se firmó.</li>
                <li>El correo electrónico al que se envió el enlace.</li>
                <li>El número de celular al que llegó el OTP y el momento de su validación.</li>
                <li>El hash del documento, para demostrar que no se modificó después.</li>
            </ul>
            <p>
                Sin ese conjunto de datos, una firma electrónica es mucho más fácil de atacar en un proceso.
                Con él, sostener la autenticidad es sencillo.
            </p>

            <h2>Cuándo la firma electrónica NO sirve</h2>
            <p>
                Hay actos que la ley exige que consten en <strong>escritura pública</strong> ante notario.
                En esos casos ninguna firma electrónica, por robusta que sea, los reemplaza:
            </p>
            <ul>
                <li>Compraventa de bienes inmuebles.</li>
                <li>Constitución de hipotecas y de usufructo.</li>
                <li>Capitulaciones matrimoniales.</li>
                <li>Testamentos.</li>
                <li>Constitución de sociedades cuando se aportan inmuebles al capital.</li>
            </ul>
            <p>
                También existen trámites que requieren presencia física ante una autoridad.
                Cuando tu caso sea uno de estos, te lo advertimos <strong>antes</strong> de que pagues.
            </p>

            <h2>Cómo funciona en HazloLegal</h2>
            <p>
                En los documentos que admiten firma electrónica, cada parte recibe un enlace por correo.
                Al abrirlo, verifica su identidad con un código OTP que llega a su celular, revisa el
                documento y firma. Al terminar, todas las partes reciben el PDF firmado junto con el
                certificado de auditoría que registra toda la evidencia descrita arriba.
            </p>
        </div>
    </div>
</section>

@if ($faqs->isNotEmpty())
<section class="section section--sunken">
    <div class="container container--narrow">
        <div class="section-head">
            <span class="eyebrow">Preguntas frecuentes</span>
            <h2>Sobre la firma electrónica</h2>
        </div>
        <x-faq-list :faqs="$faqs" :open="1" />
    </div>
</section>
@endif

<x-cta-band
    title="Firma tu contrato desde el celular"
    text="Arrendamientos, NDA, promesas de compraventa y contratos de servicios con firma electrónica incluida, sin costo adicional."
    primary-label="Ver documentos con firma"
    :primary-href="route('documents.index')"
    secondary-label="Leer la garantía"
    :secondary-href="route('pages.guarantee')" />
@endsection
