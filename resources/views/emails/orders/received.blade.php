@extends('emails.layout')

@section('subject', "Tu pedido {$order->reference}")
@section('accent', '#EA580C')
@section('preheader', "Registramos tu pedido de {$order->document->name}. Falta confirmar el pago.")

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Tu pedido quedó registrado
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($order->customer_name, ' ') }}, recibimos tu solicitud de
        <strong>{{ $order->document->name }}</strong>.
        Falta un paso: confirmar el pago para que tu trámite entre a la fila de redacción.
    </p>

    <x-email.note tone="warn" icon="🕒">
        <strong>El reloj de la entrega arranca cuando confirmamos el pago</strong>, no al hacer el pedido.
    </x-email.note>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Referencia" :value="$order->reference" />
        <x-email.row label="Trámite" :value="$order->document->name" />
        <x-email.row label="Plan" :value="$order->plan->name" />
        <x-email.row label="Velocidad" :value="$order->speed->getLabel()" />
        <x-email.row label="Total" :value="$order->formattedAmount().' COP'" />
    </table>

    <x-email.button :href="route('checkout.payment', $order->reference)" label="Confirmar mi pago" color="#EA580C" />

    <p style="margin:24px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
        🔒 Nunca te pediremos datos de tarjeta por WhatsApp ni por teléfono.
        Si alguien lo hace en nuestro nombre, no somos nosotros.
    </p>

    <p style="margin:14px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
        ¿Dudas antes de pagar? Escríbenos a {{ config('hazlolegal.contact.whatsapp_display') }}
        o responde este correo.
    </p>
@endsection
