{{--
    El documento que el cliente radica.

    Sobrio a propósito: esto se entrega en una ventanilla o se sube a un
    portal público, así que va con tipografía de oficio, márgenes amplios y
    la marca discreta al pie. Un documento legal con aire de folleto resta
    credibilidad a quien lo presenta.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->name }} · {{ $order->reference }}</title>
    <style>
        @page { margin: 2.5cm 2.5cm 3cm 2.5cm; }

        body {
            margin: 0;
            font-family: "DejaVu Serif", serif;
            font-size: 11.5pt;
            line-height: 1.7;
            color: #111827;
            text-align: justify;
        }

        .heading {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: left;
            margin-bottom: 4px;
        }

        .subject {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5pt;
            margin: 22px 0 26px;
            text-align: left;
        }

        p { margin: 0 0 14px; }

        /* Los apartados de un escrito jurídico van destacados: una entidad
           los busca con la vista antes de leer. */
        .apartado {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 24px 0 10px;
            text-align: left;
        }

        .plazo {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5pt;
            color: #4B5563;
            background-color: #F9FAFB;
            border-left: 3px solid #9CA3AF;
            padding: 8px 12px;
            margin-top: 20px;
            text-align: left;
        }

        .footer {
            position: fixed;
            bottom: -2cm;
            left: 0;
            right: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 7.5pt;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 6px;
        }
        .footer td { font-size: 7.5pt; color: #9CA3AF; }
        .footer .right { text-align: right; }

        /* ---------------- Firma ---------------- */
        .sign-block { margin-top: 48px; page-break-inside: avoid; }
        .sign-img { height: 70px; }
        .sign-rule { width: 260px; border-bottom: 1px solid #111827; margin-top: 2px; }
        .sign-name { font-family: "DejaVu Sans", sans-serif; font-size: 10.5pt; font-weight: bold; margin-top: 6px; }
        .sign-doc { font-family: "DejaVu Sans", sans-serif; font-size: 9.5pt; color: #6B7280; }

        .audit {
            width: 100%;
            margin-top: 34px;
            border: 1px solid #E5E7EB;
            border-collapse: collapse;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8pt;
            color: #374151;
            page-break-inside: avoid;
        }
        .audit td { padding: 6px 10px; border-bottom: 1px solid #F3F4F6; text-align: left; }
        .audit-title {
            background-color: #F9FAFB;
            font-weight: bold;
            font-size: 8.5pt;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #111827;
        }
        .audit-label { width: 30%; color: #6B7280; }
        .audit-hash { font-family: "DejaVu Sans Mono", monospace; font-size: 6.5pt; word-break: break-all; }
        .audit-note { font-size: 7pt; color: #9CA3AF; line-height: 1.4; border-bottom: 0; }
    </style>
</head>
<body>

<div class="heading">{{ $heading }}</div>

<div class="subject">
    <strong>Asunto:</strong> {{ $document->short_name ?: $document->name }}
</div>

@if ($escrito)
    {{-- Escrito armado con las casillas que marcó el usuario. La estructura
         es la que una entidad espera leer: quién, qué pasó, qué se pide y
         con qué norma. Un párrafo corrido se responde peor. --}}
    <p>{{ $escrito['entidad'] ? 'Respetados señores de '.$escrito['entidad'].':' : 'Respetados señores:' }}</p>

    <p>
        Yo, <strong>{{ $order->customer_name }}</strong>, identificado con
        {{ trim($order->customer_document_type.' '.$order->customer_document_number) }},
        en ejercicio del derecho de petición consagrado en el artículo 23 de la Constitución
        Política, me permito presentar la siguiente solicitud.
    </p>

    @if ($escrito['hechos'])
        <p class="apartado">HECHOS</p>

        @foreach ($escrito['hechos'] as $i => $hecho)
            <p>
                <strong>{{ $i + 1 }}.</strong>
                @if (! empty($hecho['fecha']))
                    El {{ \Carbon\CarbonImmutable::parse($hecho['fecha'])->translatedFormat('j \d\e F \d\e Y') }},
                @endif
                {{ $hecho['hecho'] }}
            </p>
        @endforeach
    @endif

    <p class="apartado">PETICIONES</p>

    <p>Con fundamento en los hechos anteriores, solicito respetuosamente:</p>

    @foreach ($escrito['peticiones'] as $i => $peticion)
        <p><strong>{{ $i + 1 }}.</strong> {!! $peticion !!}</p>
    @endforeach

    @if ($escrito['fundamento'])
        <p class="apartado">FUNDAMENTO DE DERECHO</p>
        <p>{{ $escrito['fundamento'] }}</p>
    @endif

    <p class="apartado">NOTIFICACIONES</p>

    <p>
        Recibiré notificaciones en {{ $order->customer_email }}
        @if ($order->customer_city), en la ciudad de {{ $order->customer_city }}@endif.
        @if ($escrito['cierre'])
            {{ $escrito['cierre'] }}
        @endif
    </p>

    @if ($escrito['plazo_dias'])
        <p class="plazo">
            Término legal para responder: {{ $escrito['plazo_dias'] }} días hábiles
            contados desde el día siguiente a la radicación.
        </p>
    @endif

    <p>Atentamente,</p>
@else
    @foreach (preg_split('/\n{2,}/', trim($body)) as $paragraph)
        <p>{!! nl2br($paragraph) !!}</p>
    @endforeach
@endif

@if ($signature && $signatureUri)
    {{-- La firma y, debajo, lo que la hace oponible: quién, cuándo, desde
         dónde y la huella del propio archivo. La Ley 527 de 1999 no pide un
         formato, pide poder identificar al firmante y comprobar que el
         documento no cambió. --}}
    <div class="sign-block">
        <img src="{{ $signatureUri }}" alt="Firma" class="sign-img">
        <div class="sign-rule"></div>
        <div class="sign-name">{{ $signature->signer_name }}</div>
        @if ($signature->signer_document)
            <div class="sign-doc">{{ $signature->signer_document }}</div>
        @endif
    </div>

    <table class="audit">
        <tr>
            <td colspan="2" class="audit-title">Certificado de firma electrónica</td>
        </tr>
        <tr>
            <td class="audit-label">Firmado por</td>
            <td>{{ $signature->signer_name }}{{ $signature->signer_document ? ' · '.$signature->signer_document : '' }}</td>
        </tr>
        <tr>
            <td class="audit-label">Fecha y hora</td>
            <td>{{ $signature->signed_at?->translatedFormat('j \d\e F \d\e Y, g:i:s a') }} (hora de Colombia)</td>
        </tr>
        <tr>
            <td class="audit-label">Dirección IP</td>
            <td>{{ $signature->signed_ip ?: '—' }}</td>
        </tr>
        <tr>
            <td class="audit-label">Dispositivo</td>
            <td>{{ \Illuminate\Support\Str::limit($signature->signed_user_agent, 90) }}</td>
        </tr>
        <tr>
            <td class="audit-label">Referencia</td>
            <td>{{ $order->reference }}</td>
        </tr>
        @if ($signature->document_hash)
        <tr>
            <td class="audit-label">Huella SHA-256</td>
            <td class="audit-hash">{{ $signature->document_hash }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="2" class="audit-note">
                Firma electrónica con plena validez probatoria según la Ley 527 de 1999 y el
                Decreto 2364 de 2012. La huella permite comprobar que este documento no fue
                modificado después de firmarse.
            </td>
        </tr>
    </table>
@endif

<table class="footer" width="100%">
    <tr>
        <td>Documento preparado con {{ $brand['name'] }} · Referencia {{ $order->reference }}</td>
        <td class="right">{{ preg_replace('#^https?://#', '', $brand['site']) }}</td>
    </tr>
</table>

</body>
</html>
