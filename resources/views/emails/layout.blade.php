{{--
    Layout de correo. Tablas y estilos en línea a propósito: Outlook y Gmail
    descartan buena parte del CSS moderno, así que nada aquí depende de flexbox,
    grid ni variables.
--}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('subject', 'HazloLegal')</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #F1F5F9; }
        a { color: #2563EB; }
        .hl-sm-full { width: 100%; }
        @media screen and (max-width: 620px) {
            .hl-container { width: 100% !important; }
            .hl-pad { padding-left: 22px !important; padding-right: 22px !important; }
            .hl-h1 { font-size: 23px !important; line-height: 31px !important; }
            .hl-btn { display: block !important; width: 100% !important; box-sizing: border-box; }
            .hl-btn-cell { display: block !important; width: 100% !important; padding: 0 0 12px 0 !important; }
            .hl-stack { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#F1F5F9;">
    {{-- Preheader: lo que se lee en la bandeja antes de abrir --}}
    <div style="display:none; font-size:1px; color:#F1F5F9; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
        @yield('preheader', 'HazloLegal · trámites legales con abogados titulados en Colombia')
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F1F5F9;">
        <tr>
            <td align="center" style="padding:28px 12px;">

                <table role="presentation" class="hl-container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">

                    {{-- ---------------- Cabecera ---------------- --}}
                    <tr>
                        <td align="center" style="padding:0 0 22px 0;">
                            <a href="{{ url('/') }}" style="text-decoration:none;">
                                <span style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:22px; font-weight:800; letter-spacing:-.6px; color:#1E3A8A;">
                                    Hazlo<span style="color:#2563EB;">Legal</span>
                                </span>
                            </a>
                        </td>
                    </tr>

                    {{-- ---------------- Barra de color ---------------- --}}
                    <tr>
                        <td style="background-color:@yield('accent', '#2563EB'); height:5px; line-height:5px; font-size:0; border-radius:12px 12px 0 0;">&nbsp;</td>
                    </tr>

                    {{-- ---------------- Cuerpo ---------------- --}}
                    <tr>
                        <td class="hl-pad" style="background-color:#FFFFFF; padding:34px 40px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:16px; line-height:25px; color:#334155;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- ---------------- Pie ---------------- --}}
                    <tr>
                        <td class="hl-pad" style="background-color:#F8FAFC; padding:26px 40px; border-top:1px solid #E2E8F0; border-radius:0 0 12px 12px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:13px; line-height:20px; color:#64748B;">
                            <p style="margin:0 0 10px 0;">
                                <strong style="color:#334155;">{{ config('hazlolegal.brand.name') }}</strong><br>
                                {{ config('hazlolegal.contact.address') }} · {{ config('hazlolegal.contact.city') }}, Colombia<br>
                                WhatsApp {{ config('hazlolegal.contact.whatsapp_display') }} ·
                                <a href="mailto:{{ config('hazlolegal.contact.email') }}" style="color:#2563EB;">{{ config('hazlolegal.contact.email') }}</a>
                            </p>
                            <p style="margin:0 0 10px 0;">
                                <a href="{{ url('/') }}" style="color:#64748B;">Inicio</a> ·
                                <a href="{{ route('documents.index') }}" style="color:#64748B;">Trámites</a> ·
                                <a href="{{ route('pages.guarantee') }}" style="color:#64748B;">Garantía</a> ·
                                <a href="{{ route('pages.privacy') }}" style="color:#64748B;">Privacidad</a>
                            </p>
                            <p style="margin:0; font-size:12px; line-height:18px; color:#94A3B8;">
                                @yield('footnote', 'Este mensaje se envió porque usaste nuestro servicio. El uso del sitio no constituye una relación abogado-cliente hasta la aceptación formal y el pago del servicio.')
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
