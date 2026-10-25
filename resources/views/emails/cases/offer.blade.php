@extends('emails.layout')

@section('subject', "Caso nuevo {$case->reference}")
@section('accent', $case->urgency === \App\Domain\LegalCases\Enums\CaseUrgency::Critical ? '#DC2626' : '#EA580C')
@section('preheader', "{$case->practice_area->getLabel()} · {$case->title} · se lo queda el primero que acepte")
@section('footnote', 'Recibes este correo porque eres abogado de la red de Hazlo Legal y el caso corresponde a tu área. Puedes pausar las invitaciones escribiéndonos.')

@section('content')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="padding:0 0 14px 0;">
                <span style="display:inline-block; background-color:{{ $case->urgency === \App\Domain\LegalCases\Enums\CaseUrgency::Critical ? '#FEF2F2' : '#FFF7ED' }}; color:{{ $case->urgency === \App\Domain\LegalCases\Enums\CaseUrgency::Critical ? '#B91C1C' : '#C2410C' }}; border-radius:999px; padding:5px 13px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:12px; font-weight:700; letter-spacing:.3px;">
                    {{ \Illuminate\Support\Str::upper($case->urgency->shortLabel()) }}
                </span>
                <span style="display:inline-block; background-color:#EFF6FF; color:#1D4ED8; border-radius:999px; padding:5px 13px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:12px; font-weight:700; margin-left:6px;">
                    {{ $case->practice_area->getLabel() }}
                </span>
            </td>
        </tr>
    </table>

    <h1 class="hl-h1" style="margin:0 0 10px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A;">
        Caso nuevo en tu área
    </h1>

    <p style="margin:0 0 22px 0;">
        Hola {{ \Illuminate\Support\Str::before($lawyer->name, ' ') }}, entró un caso de
        <strong>{{ \Illuminate\Support\Str::lower($case->practice_area->getLabel()) }}</strong>.
        Lo enviamos a varios colegas al mismo tiempo y
        <strong style="color:#C2410C;">se lo queda el primero que lo acepte</strong>.
    </p>

    <x-email.note tone="muted" icon="🔖">
        Referencia <strong>{{ $case->reference }}</strong> ·
        Recibido {{ $case->created_at->translatedFormat('j \d\e F \a \l\a\s g:i a') }}
        @if ($case->expires_at)
            · La invitación vence el {{ $case->expires_at->translatedFormat('j \d\e F \a \l\a\s g:i a') }}
        @endif
    </x-email.note>

    {{-- ---------------- Resumen del caso ---------------- --}}
    <h2 style="margin:0 0 6px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:19px; line-height:26px; font-weight:700; color:#0F172A;">
        {{ $case->title }}
    </h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:16px 0 22px 0;">
        <x-email.row label="Urgencia" :value="$case->urgency->getLabel()" />
        @if ($case->budget_range)
            <x-email.row label="Presupuesto indicado" :value="$case->budget_range->getLabel()" />
        @endif
        @if ($case->deadline_note)
            <x-email.row label="Término o fecha límite" :value="$case->deadline_note" />
        @endif
        <x-email.row label="Documentos" :value="$case->has_documents ? 'El cliente dice que los tiene' : 'No indicó'" />
        @if ($case->client_city)
            <x-email.row label="Ciudad" :value="$case->client_city" />
        @endif
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr>
            <td style="background-color:#F8FAFC; border-left:3px solid #2563EB; border-radius:0 8px 8px 0; padding:16px 18px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:24px; color:#334155;">
                <strong style="display:block; margin-bottom:8px; font-size:13px; letter-spacing:.4px; text-transform:uppercase; color:#64748B;">Lo que cuenta el cliente</strong>
                {!! nl2br(e($case->description)) !!}
            </td>
        </tr>
    </table>

    {{-- ---------------- Los dos botones de decisión ---------------- --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px 0;">
        <tr>
            <td class="hl-btn-cell" align="center" style="padding:0 8px 0 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center" style="background-color:#059669; border-radius:8px;">
                            <a href="{{ $offer->acceptUrl() }}"
                               style="display:block; padding:17px 20px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:16px; font-weight:800; line-height:20px; color:#FFFFFF; text-decoration:none;">
                                ✓ Acepto este caso
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="hl-btn-cell" align="center" style="padding:0 0 0 8px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center" style="background-color:#FFFFFF; border:2px solid #CBD5E1; border-radius:8px;">
                            <a href="{{ $offer->declineUrl() }}"
                               style="display:block; padding:15px 20px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:16px; font-weight:700; line-height:20px; color:#475569; text-decoration:none;">
                                No puedo tomarlo
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 22px 0; font-size:13px; line-height:20px; color:#64748B; text-align:center;">
        Al aceptar recibes de inmediato los datos de contacto del cliente y el compromiso
        de escribirle dentro de las siguientes 24 horas.
    </p>

    <x-email.note tone="warn" icon="⏱️">
        <strong>Responde rápido incluso si vas a rechazarlo.</strong>
        Así el caso llega antes a otro colega y el cliente no queda esperando.
    </x-email.note>

    <p style="margin:0; font-size:13px; line-height:20px; color:#94A3B8;">
        Si los botones no funcionan, copia este enlace en tu navegador:<br>
        <span style="word-break:break-all; color:#64748B;">{{ $offer->acceptUrl() }}</span>
    </p>
@endsection
