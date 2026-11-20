@extends('emails.layout')

@section('subject', "Tu plan {$subscription->plan?->name} está activo")
@section('accent', '#059669')
@section('preheader', 'Tu perfil ya está publicado en el directorio.')

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Tu plan está activo
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($subscription->lawyer?->name ?? '', ' ') }}, ya estás
        dentro con el plan <strong>{{ $subscription->plan?->name }}</strong>.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Plan" :value="$subscription->plan?->name" />
        <x-email.row label="Periodo" :value="$subscription->billing_period?->getLabel()" />
        <x-email.row label="Valor" :value="'$'.number_format($subscription->price, 0, ',', '.').' COP'" />
        <x-email.row label="Renueva" :value="$subscription->renews_at?->translatedFormat('j \d\e F \d\e Y')" />
        @if ($transaction?->gateway_transaction_id)
            <x-email.row label="Transacción" :value="$transaction->gateway_transaction_id" />
        @endif
    </table>

    <x-email.button :href="url('/portal')" label="Entrar a mi portal" color="#059669" />

    <h2 style="margin:26px 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        Lo primero que deberías hacer
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr><td style="padding:0 0 12px 0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">1.</strong> Completa tu perfil: foto, biografía y áreas. Es lo que ve quien busca abogado.
        </td></tr>
        <tr><td style="padding:0 0 12px 0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">2.</strong> Revisa que tus áreas estén bien: deciden qué casos te llegan.
        </td></tr>
        <tr><td style="padding:0; font-size:15px; line-height:24px; color:#334155; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <strong style="color:#2563EB;">3.</strong> Cuando llegue un caso de tu área, responde rápido: el primero que acepta se lo queda.
        </td></tr>
    </table>
@endsection
