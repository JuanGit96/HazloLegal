@php
    $seVa = $subscription->isEnding();
    $cuando = $subscription->changesOn()?->translatedFormat('j \d\e F \d\e Y');
    $nuevo = $subscription->scheduledPlan;
    $nombre = \Illuminate\Support\Str::before($subscription->lawyer?->name ?? '', ' ');
@endphp

@extends('emails.layout')

@section('subject', $seVa ? "Tu plan termina el {$cuando}" : "Desde el {$cuando} pasas a {$nuevo?->name}")
@section('accent', $seVa ? '#B45309' : '#2563EB')
@section('preheader', 'Nada cambia hoy: lo que pagaste lo conservas completo.')

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        {{ $seVa ? 'Recibimos tu cancelación' : 'Recibimos tu cambio de plan' }}
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ $nombre }}. {{ $seVa
            ? 'No te cobraremos de nuevo.'
            : "Desde el {$cuando} pasas al plan {$nuevo?->name}." }}
        <strong>Hoy no cambia nada</strong>: conservas todo lo del plan
        {{ $subscription->plan?->name }} hasta el {{ $cuando }}, que es hasta donde llega lo que pagaste.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Tu plan hoy" :value="$subscription->plan?->name" />
        @if ($seVa)
            <x-email.row label="Termina" :value="$cuando" />
            <x-email.row label="Próximo cobro" value="Ninguno" />
        @else
            <x-email.row label="Desde el {{ $cuando }}" :value="$nuevo?->name" />
            <x-email.row
                label="Pasarás a pagar"
                :value="'$'.number_format($nuevo?->priceFor($subscription->billing_period) ?? 0, 0, ',', '.').' COP · '.($subscription->billing_period?->getLabel() ?? '')" />
        @endif
    </table>

    {{-- El argumento honesto para volver: lo que escribió no se borra. Es
         además lo que más tarda en construirse y lo que seguirá trayéndole
         gente mientras el artículo siga posicionado. --}}
    <x-email.note tone="muted">
        <strong>Tus guías se quedan publicadas, con tu firma.</strong>
        Lo que escribiste para HazloLegal no se baja del sitio: sigue indexado en Google
        y sigue llevando tu nombre.
        {{ $seVa
            ? 'Lo que desaparece el '.$cuando.' es tu ficha del directorio y el enlace para escribirte desde esas guías.'
            : 'Si el plan nuevo no incluye ficha pública, lo que desaparece es el enlace a tu ficha desde esas guías.' }}
    </x-email.note>

    <p style="margin:22px 0;">
        Si te arrepientes, {{ $seVa ? 'puedes seguir con tu plan' : 'puedes deshacer el cambio' }}
        desde tu portal en cualquier momento antes del {{ $cuando }}. No hay que escribirle a nadie.
    </p>

    <x-email.button
        :href="url('/portal/my-subscription')"
        label="{{ $seVa ? 'Seguir con mi plan' : 'Ver mi suscripción' }}"
        color="{{ $seVa ? '#B45309' : '#2563EB' }}" />
@endsection
