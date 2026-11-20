@extends('emails.layout')

@section('subject', 'Tu enlace para ver tus trámites')
@section('accent', '#2563EB')
@section('preheader', 'Entra sin contraseña. El enlace vence en 30 minutos.')

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Aquí tienes tu enlace
    </h1>

    <p style="margin:0 0 22px 0;">
        Ábrelo y verás todos los trámites que has pedido, con sus documentos listos para
        descargar otra vez. No hace falta contraseña.
    </p>

    <x-email.button :href="$link" label="Ver mis trámites" color="#2563EB" />

    <x-email.note tone="warn" icon="🕒">
        <strong>El enlace vence en 30 minutos</strong> y solo funciona una vez abierto desde este
        correo. Si se vence, pide otro: no cuesta nada.
    </x-email.note>

    <p style="margin:0; font-size:14px; line-height:22px; color:#64748B;">
        Si no pediste este enlace, ignora el correo. Nadie puede entrar sin él.
    </p>
@endsection
