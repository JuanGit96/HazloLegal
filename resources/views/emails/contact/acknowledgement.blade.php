@extends('emails.layout')

@php $esEliminacion = $message->kind->value === 'data_deletion'; @endphp

@section('subject', $esEliminacion ? 'Recibimos tu solicitud' : 'Recibimos tu mensaje')
@section('accent', '#059669')
@section('preheader', "Tu radicado es {$message->reference}.")

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        {{ $esEliminacion ? 'Recibimos tu solicitud' : 'Recibimos tu mensaje' }}
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($message->name, ' ') }}, quedó registrada con el
        radicado <strong>{{ $message->reference }}</strong>. Guárdalo: es tu constancia.
    </p>

    @if ($esEliminacion)
        <x-email.note tone="info" icon="⏳">
            Tenemos <strong>{{ $message->kind->responseDays() }} días hábiles</strong> para responderte,
            como manda la Ley 1581 de 2012. Te escribiremos antes del
            <strong>{{ $message->dueAt()?->translatedFormat('j \d\e F') }}</strong>.
        </x-email.note>

        <p style="margin:0 0 22px 0;">
            Si tienes trámites en curso, algunos datos pueden conservarse el tiempo que exige la
            ley contable y tributaria. Te lo explicaremos en la respuesta, con detalle y sin
            tecnicismos.
        </p>
    @else
        <x-email.note tone="success" icon="💬">
            Te respondemos en <strong>menos de 24 horas hábiles</strong>. Responde a este correo si
            quieres añadir algo.
        </x-email.note>
    @endif

    <p style="margin:0; font-size:14px; line-height:22px; color:#64748B;">
        Si prefieres hablar, escríbenos por WhatsApp a {{ config('hazlolegal.contact.whatsapp_display') }}.
    </p>
@endsection
