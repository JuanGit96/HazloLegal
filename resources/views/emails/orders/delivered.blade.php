@extends('emails.layout')

@section('subject', "Ya está listo: {$order->document->name}")
@section('accent', '#059669')
@section('preheader', "Adjuntamos tu {$order->document->name}, listo para firmar y radicar.")

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Tu documento está listo
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($order->customer_name, ' ') }}, aquí tienes tu
        <strong>{{ $order->document->name }}</strong>. Va adjunto a este correo y también lo
        puedes descargar desde el enlace de abajo.
    </p>

    <x-email.note tone="warn" icon="🔎">
        <strong>Revisa tus datos antes de firmar.</strong> Comprueba nombre, número de documento
        y los datos de la entidad: un dígito mal es la causa más común de rechazo en ventanilla.
    </x-email.note>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Referencia" :value="$order->reference" />
        <x-email.row label="Trámite" :value="$order->document->name" />
        @foreach ($order->addons as $addon)
            <x-email.row :label="$addon->name" :value="$addon->formattedPrice()" />
        @endforeach
    </table>

    @php $firma = $order->signatureRequest()->latest()->first(); @endphp

    @if ($firma && ! $firma->isSigned() && ! $firma->hasExpired())
        <x-email.note tone="warn" icon="✍️">
            <strong>Falta tu firma.</strong> Ábrelo desde el celular y trázala con el dedo:
            queda estampada en el documento con validez legal (Ley 527 de 1999).
        </x-email.note>

        <x-email.button :href="$firma->url()" label="Firmar desde mi celular" color="#EA580C" />

        <p style="margin:14px 0 26px 0; font-size:14px; line-height:22px; color:#64748B;">
            Si estás en el computador, abre tu pedido y escanea el código con la cámara.
            El enlace vence el {{ $firma->expires_at?->translatedFormat('j \d\e F') }}.
        </p>
    @endif

    <x-email.button :href="$order->trackingUrl()" label="Ver y descargar mis archivos" color="#059669" />

    <h2 style="margin:26px 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        Qué sigue
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr><td style="padding:0 0 12px 0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">1.</strong> Revisa que tu nombre, documento y los datos de la entidad estén exactos.
        </td></tr>
        <tr><td style="padding:0 0 12px 0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">2.</strong> Fírmalo e imprímelo, o usa la firma electrónica si la contrataste.
        </td></tr>
        <tr><td style="padding:0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">3.</strong> Radícalo y exige el número de radicado: sin él no puedes probar que lo presentaste.
        </td></tr>
    </table>

    <x-email.note tone="success" icon="🛡️">
        Si algo salió mal por nuestra culpa, lo corregimos sin costo.
        <a href="{{ route('pages.guarantee') }}" style="color:#047857;">Ver el Certificado de Garantía</a>.
    </x-email.note>
@endsection
