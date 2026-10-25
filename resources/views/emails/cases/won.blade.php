@extends('emails.layout')

@section('subject', "El caso {$case->reference} es tuyo")
@section('accent', '#059669')
@section('preheader', "Llegaste primero. Aquí están los datos de contacto de {$case->client_name}.")
@section('footnote', 'La información de este caso está sujeta a reserva profesional. No la compartas fuera de la red de Hazlo Legal.')

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        🎉 El caso es tuyo
    </h1>

    <p style="margin:0 0 22px 0;">
        Llegaste primero, {{ \Illuminate\Support\Str::before($lawyer->name, ' ') }}.
        El caso <strong>{{ $case->reference }}</strong> quedó asignado a ti y el cliente
        ya recibió un correo con tu nombre y tus credenciales.
    </p>

    <x-email.note tone="success" icon="⏱️">
        <strong>Contacta al cliente dentro de las próximas 24 horas.</strong>
        Es el compromiso que la red le promete a quien nos escribe.
    </x-email.note>

    <h2 style="margin:0 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        Datos de contacto
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Nombre" :value="$case->client_name" />
        <x-email.row label="Correo" :value="$case->client_email" />
        <x-email.row label="Celular" :value="$case->client_phone" />
        @if ($case->client_city)
            <x-email.row label="Ciudad" :value="$case->client_city" />
        @endif
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr>
            <td class="hl-btn-cell" align="center" style="padding:0 8px 0 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center" style="background-color:#25D366; border-radius:8px;">
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $case->client_phone) }}"
                               style="display:block; padding:15px 20px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; font-weight:800; color:#06331A; text-decoration:none;">
                                Escribir por WhatsApp
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="hl-btn-cell" align="center" style="padding:0 0 0 8px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center" style="background-color:#2563EB; border-radius:8px;">
                            <a href="mailto:{{ $case->client_email }}?subject=Su%20caso%20{{ $case->reference }}%20-%20Hazlo%20Legal"
                               style="display:block; padding:15px 20px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; color:#FFFFFF; text-decoration:none;">
                                Enviar correo
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <h2 style="margin:0 0 10px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        {{ $case->title }}
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;">
        <tr>
            <td style="background-color:#F8FAFC; border-left:3px solid #059669; border-radius:0 8px 8px 0; padding:16px 18px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:24px; color:#334155;">
                {!! nl2br(e($case->description)) !!}
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <x-email.row label="Urgencia" :value="$case->urgency->getLabel()" />
        @if ($case->budget_range)
            <x-email.row label="Presupuesto indicado" :value="$case->budget_range->getLabel()" />
        @endif
        @if ($case->deadline_note)
            <x-email.row label="Término" :value="$case->deadline_note" />
        @endif
        <x-email.row label="Documentos" :value="$case->has_documents ? 'El cliente dice que los tiene' : 'No indicó'" />
    </table>
@endsection
