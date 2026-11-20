@extends('emails.layout')

@section('subject', 'Ya hay abogados interesados en tu caso')
@section('accent', '#2563EB')
@section('preheader', 'Entra y elige con quién quieres trabajar.')

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Hay abogados interesados en tu caso
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($case->client_name, ' ') }}, enviamos tu caso a los
        abogados de tu área y <strong>varios quieren tomarlo</strong>. Ahora decides tú.
    </p>

    <x-email.note tone="info" icon="👀">
        <strong>Ninguno tiene tus datos de contacto todavía.</strong> Solo verán tu nombre y tu
        teléfono cuando elijas a uno.
    </x-email.note>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Tu caso" :value="$case->title" />
        <x-email.row label="Referencia" :value="$case->reference" />
        <x-email.row label="Interesados" :value="$case->acceptedOffers->count().' abogados'" />
    </table>

    <x-email.button :href="$case->clientUrl()" label="Ver los abogados y elegir" color="#2563EB" />

    <p style="margin:22px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
        Tómate el tiempo que necesites. Si en unas horas se postulan más abogados, los verás en la
        misma página; no te mandamos un correo por cada uno.
    </p>
@endsection
