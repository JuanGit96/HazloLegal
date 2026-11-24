@extends('emails.layout')

@section('subject', "Recibimos tu caso {$case->reference}")
@section('accent', '#2563EB')
@section('preheader', "Tu caso ya está con los abogados especializados en {$case->practice_area->getLabel()}. Tú eliges.")

@section('content')
    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Recibimos tu caso, {{ \Illuminate\Support\Str::before($case->client_name, ' ') }}
    </h1>

    <p style="margin:0 0 22px 0;">
        Ya lo enviamos a los abogados de nuestra red especializados en
        <strong>{{ \Illuminate\Support\Str::lower($case->practice_area->getLabel()) }}</strong>.
        Los que quieran tomarlo se postulan, y en cuanto haya al menos uno te escribimos con
        un enlace para que compares sus perfiles y elijas tú.
    </p>

    <x-email.note tone="info" icon="🔖">
        Tu referencia es <strong>{{ $case->reference }}</strong>. Guárdala para cualquier consulta.
    </x-email.note>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <x-email.row label="Área" :value="$case->practice_area->getLabel()" />
        <x-email.row label="Resumen" :value="$case->title" />
        <x-email.row label="Urgencia" :value="$case->urgency->shortLabel()" />
        <x-email.row label="Recibido" :value="$case->created_at->translatedFormat('j \d\e F, g:i a')" />
    </table>

    <h2 style="margin:0 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        Qué sigue
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr>
            <td style="padding:0 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:24px; color:#334155;">
                <strong style="color:#2563EB;">1.</strong> Los abogados del área revisan tu caso.
            </td>
        </tr>
        <tr>
            <td style="padding:0 0 14px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:24px; color:#334155;">
                <strong style="color:#2563EB;">2.</strong> Te avisamos por correo en cuanto alguno se
                postule, con su perfil completo, y eliges tú. Plazo máximo: <strong>{{ $ttlHours }} horas</strong>.
            </td>
        </tr>
        <tr>
            <td style="padding:0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:24px; color:#334155;">
                <strong style="color:#2563EB;">3.</strong> El abogado que elijas te contacta, evalúa el caso
                y te propone honorarios claros. Sin compromiso hasta que tú aceptes.
            </td>
        </tr>
    </table>

    <x-email.button :href="route('cases.received', $case->reference)" label="Ver el estado de mi caso" />

    <p style="margin:24px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
        Mientras esperas: si tu caso también necesita un documento con precio fijo
        &mdash;una tutela, un derecho de petición, un contrato&mdash; puedes verlos en
        <a href="{{ route('documents.index') }}" style="color:#2563EB;">nuestro catálogo</a>.
    </p>
@endsection
