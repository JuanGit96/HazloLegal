@extends('emails.layout')

@section('subject', "Pago confirmado · {$order->reference}")
@section('accent', '#059669')
@section('preheader', "Tu {$order->document->name} ya está en manos de un abogado.")

@section('content')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px 0;">
        <tr>
            <td align="center" style="padding:0 0 8px 0;">
                <span style="display:inline-block; width:62px; height:62px; line-height:62px; background-color:#ECFDF5; border:3px solid #059669; border-radius:50%; font-size:28px; text-align:center;">✓</span>
            </td>
        </tr>
    </table>

    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A; text-align:center;">
        Pago confirmado. Ya estamos trabajando
    </h1>

    <p style="margin:0 0 26px 0; text-align:center;">
        Tu <strong>{{ $order->document->name }}</strong> ya entró a la fila de preparación.
    </p>

    @if ($order->promised_at)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
            <tr>
                <td align="center" style="background-color:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; padding:22px;">
                    <p style="margin:0 0 4px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:13px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:#1D4ED8;">
                        Fecha comprometida de entrega
                    </p>
                    <p style="margin:0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:20px; font-weight:800; color:#1E3A8A;">
                        {{ $order->promised_at->translatedFormat('l j \d\e F, g:i a') }}
                    </p>
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Referencia" :value="$order->reference" />
        <x-email.row label="Trámite" :value="$order->document->name" />
        <x-email.row label="Plan" :value="$order->plan->name.' · '.$order->speed->shortLabel()" />
        <x-email.row label="Pagado" :value="$order->formattedAmount().' COP'" />
        @if ($order->payment_method)
            <x-email.row label="Medio de pago" :value="$order->payment_method->getLabel()" />
        @endif
    </table>

    <h2 style="margin:0 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        Qué pasa ahora
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr><td style="padding:0 0 12px 0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">1.</strong> Un abogado del área lee tu caso. Si falta algún dato, te escribe por WhatsApp.
        </td></tr>
        <tr><td style="padding:0 0 12px 0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">2.</strong> Se redacta el documento y se audita ortografía, datos y normas citadas.
        </td></tr>
        <tr><td style="padding:0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">3.</strong> Recibes el PDF aquí y en tu WhatsApp, con la guía de radicación.
        </td></tr>
    </table>

    <x-email.button :href="route('checkout.confirmation', $order->reference)" label="Ver el estado de mi pedido" color="#059669" />

    <x-email.note tone="success" icon="🛡️">
        Tu pedido está cubierto por el <strong>Certificado de Garantía HazloLegal</strong>:
        si el documento sale con errores nuestros lo corregimos sin costo, y si una entidad
        lo rechaza por un defecto de forma nuestro te devolvemos el 100 %.
        <a href="{{ route('pages.guarantee') }}" style="color:#047857;">Ver el certificado</a>.
    </x-email.note>
@endsection
