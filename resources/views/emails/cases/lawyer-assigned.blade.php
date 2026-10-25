@extends('emails.layout')

@section('subject', "Ya tienes abogado asignado")
@section('accent', '#059669')
@section('preheader', "{$lawyer->name} tomó tu caso {$case->reference} y te contactará pronto.")

@section('content')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px 0;">
        <tr>
            <td align="center" style="padding:0 0 8px 0;">
                <span style="display:inline-block; width:62px; height:62px; line-height:62px; background-color:#ECFDF5; border:3px solid #059669; border-radius:50%; font-size:28px; text-align:center;">✓</span>
            </td>
        </tr>
    </table>

    <h1 class="hl-h1" style="margin:0 0 12px 0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:26px; line-height:33px; font-weight:800; letter-spacing:-.5px; color:#0F172A; text-align:center;">
        Ya tienes abogado para tu caso
    </h1>

    <p style="margin:0 0 26px 0; text-align:center;">
        <strong>{{ $lawyer->name }}</strong> aceptó tu caso
        <strong>{{ $case->reference }}</strong> y se pondrá en contacto contigo
        dentro de las próximas 24 horas.
    </p>

    {{-- ---------------- Perfil del abogado ---------------- --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px 0;">
        <tr>
            <td style="background-color:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="64" valign="top" style="padding-right:16px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="width:60px; height:60px; background-color:#DBEAFE; border-radius:50%; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:21px; font-weight:800; color:#1E3A8A;">
                                        {{ $lawyer->initials() }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top" style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                            <p style="margin:0 0 3px 0; font-size:18px; font-weight:800; color:#0F172A;">{{ $lawyer->name }}</p>
                            <p style="margin:0 0 8px 0; font-size:14px; color:#2563EB; font-weight:600;">
                                {{ $lawyer->headline ?: 'Abogado titulado' }}
                            </p>
                            @if ($lawyer->university)
                                <p style="margin:0 0 8px 0; font-size:13px; color:#64748B;">{{ $lawyer->university }}</p>
                            @endif
                        </td>
                    </tr>
                </table>

                @if ($lawyer->bio)
                    <p style="margin:18px 0 0 0; padding-top:16px; border-top:1px solid #E2E8F0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14px; line-height:23px; color:#475569;">
                        {{ $lawyer->bio }}
                    </p>
                @endif

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:16px;">
                    <x-email.row label="Experiencia" :value="$lawyer->years_experience.' años'" />
                    <x-email.row label="Casos resueltos" :value="number_format($lawyer->cases_resolved, 0, ',', '.')" />
                    <x-email.row label="Ciudad" :value="$lawyer->city" />
                </table>
            </td>
        </tr>
    </table>

    <x-email.note tone="success" icon="📞">
        <strong>{{ \Illuminate\Support\Str::before($lawyer->name, ' ') }} te escribirá a {{ $case->client_email }}</strong>
        y a tu celular registrado. Si en 24 horas no te ha contactado, avísanos y lo reasignamos.
    </x-email.note>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <x-email.row label="Tu caso" :value="$case->title" />
        <x-email.row label="Referencia" :value="$case->reference" />
        <x-email.row label="Área" :value="$case->practice_area->getLabel()" />
    </table>

    <p style="margin:26px 0 0 0; font-size:14px; line-height:22px; color:#64748B;">
        Pronto también te avisaremos por WhatsApp. Cualquier cosa, respóndenos este correo
        o escríbenos a {{ config('hazlolegal.contact.whatsapp_display') }}.
    </p>
@endsection
