@extends('emails.layout')

@php
    $free = $order->isFree();
    $listo = filled($order->document_path);
@endphp

@section('subject', $free
    ? ($listo ? "Tu {$order->document->name} está listo" : "Estamos preparando tu {$order->document->name}")
    : "Tu pedido {$order->reference}")
@section('accent', $free ? '#059669' : '#EA580C')
@section('preheader', $free
    ? ($listo ? "Adjuntamos tu {$order->document->name}. Revísalo antes de firmar." : "Te avisamos en cuanto esté listo.")
    : "Registramos tu pedido de {$order->document->name}. Falta confirmar el pago.")

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        {{ $free ? ($listo ? 'Tu documento está listo' : 'Lo estamos preparando') : 'Tu pedido quedó registrado' }}
    </h1>

    @if ($free && $listo)
        <p style="margin:0 0 22px 0;">
            Hola {{ \Illuminate\Support\Str::before($order->customer_name, ' ') }}, aquí tienes tu
            <strong>{{ $order->document->name }}</strong>. Va adjunto a este correo y también lo
            puedes descargar desde el enlace de abajo.
        </p>

        <x-email.note tone="warn" icon="🔎">
            <strong>Revisa tus datos antes de firmar.</strong> Comprueba nombre, número de documento
            y los datos de la entidad: un dígito mal es la causa más común de rechazo en ventanilla.
        </x-email.note>
    @elseif ($free)
        <p style="margin:0 0 22px 0;">
            Hola {{ \Illuminate\Support\Str::before($order->customer_name, ' ') }}, recibimos tu
            solicitud de <strong>{{ $order->document->name }}</strong>. Lo estamos preparando y te
            llega al correo en cuanto esté.
        </p>

        @if ($order->promised_at)
            <x-email.note tone="info" icon="🕒">
                <strong>Lo tendrás antes del {{ $order->promised_at->translatedFormat('l j \d\e F, g:i a') }}.</strong>
                Si lo necesitas de una, puedes agregar la entrega inmediata desde el enlace de abajo.
            </x-email.note>
        @endif
    @else
        <p style="margin:0 0 22px 0;">
            Hola {{ \Illuminate\Support\Str::before($order->customer_name, ' ') }}, recibimos tu solicitud de
            <strong>{{ $order->document->name }}</strong>.
            Falta un paso: confirmar el pago para que tu trámite entre a la fila de preparación.
        </p>

        <x-email.note tone="warn" icon="🕒">
            <strong>El reloj de la entrega arranca cuando confirmamos el pago</strong>, no al hacer el pedido.
        </x-email.note>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Referencia" :value="$order->reference" />
        <x-email.row label="Trámite" :value="$order->document->name" />
        @foreach ($order->addons as $addon)
            <x-email.row :label="$addon->name" :value="$addon->formattedPrice()" />
        @endforeach
        <x-email.row label="Total" :value="$free ? 'Gratis' : $order->formattedAmount().' COP'" />
    </table>

    @if ($free)
        <x-email.button :href="$order->trackingUrl()" :label="$listo ? 'Ver y descargar mi documento' : 'Ver el estado de mi trámite'" color="#059669" />

        <p style="margin:24px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
            El formato lo escribieron abogados colombianos y lo actualizamos cuando cambia la norma.
            Este trámite es automatizado: no pasa por la revisión individual de un abogado.
            Si quieres que alguien lo lea antes de radicar, puedes sumar el acompañamiento desde
            el enlace de arriba.
        </p>
    @else
        <x-email.button :href="route('checkout.payment', $order->reference)" label="Confirmar mi pago" color="#EA580C" />

        <p style="margin:24px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
            🔒 Nunca te pediremos datos de tarjeta por WhatsApp ni por teléfono.
            Si alguien lo hace en nuestro nombre, no somos nosotros.
        </p>
    @endif

    <p style="margin:14px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
        ¿Dudas? Escríbenos a {{ config('hazlolegal.contact.whatsapp_display') }} o responde este correo.
    </p>
@endsection
