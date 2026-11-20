{{--
    Instructivo de radicación.

    Esto es lo que se cobra, así que tiene que funcionar para alguien que
    nunca ha radicado nada y que puede tener setenta años: tipografía
    grande, un paso por bloque, listas con casillas de verdad para marcar
    con lápiz, y las fechas ya calculadas. Nada de «ver numeral anterior».

    dompdf no entiende flexbox ni grid: lo que parezca columnas está hecho
    con tablas a propósito. Tampoco hereda bien los estilos, así que las
    reglas van explícitas.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instructivo · {{ $document->name }}</title>
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            /* 12pt es el mínimo con el que un lector présbita no sufre. */
            font-size: 12pt;
            line-height: 1.6;
            color: #1F2937;
        }

        /* El salto va ANTES de cada sección, no después. Con
           page-break-after, una sección que termina justo en el borde del
           papel se lleva un salto extra y sale una hoja en blanco: pasaba
           en los seis instructivos. Un salto antes nunca deja una hoja
           suelta al final. */
        .page { position: relative; }
        .page + .page { page-break-before: always; }
        .pad { padding: 40px 52px; }

        /* ---------------- Portada ---------------- */
        .cover {
            background-color: #1E3A8A;
            color: #FFFFFF;
            padding: 44px 52px;
        }
        .brand { font-size: 19pt; font-weight: bold; letter-spacing: -0.5px; }
        .brand .hl { background-color: #F97316; color: #FFFFFF; padding: 1px 5px 2px; }
        .cover-kicker {
            margin-top: 30px;
            font-size: 9.5pt;
            letter-spacing: 2px;
            color: #BFDBFE;
            text-transform: uppercase;
        }
        .cover-title { font-size: 25pt; font-weight: bold; line-height: 1.2; margin-top: 8px; }
        .cover-for { margin-top: 18px; font-size: 11pt; color: #DBEAFE; }

        .intro {
            margin: 0;
            padding: 22px 52px;
            background-color: #EFF6FF;
            border-bottom: 1px solid #DBEAFE;
            font-size: 12.5pt;
            color: #1E3A8A;
        }

        /* ---------------- Bloques numerados ---------------- */
        .step { margin-bottom: 26px; page-break-inside: avoid; }

        .step-head { margin-bottom: 8px; }
        .step-num {
            display: inline-block;
            width: 30px; height: 30px;
            background-color: #1D4ED8;
            color: #FFFFFF;
            border-radius: 15px;
            text-align: center;
            line-height: 30px;
            font-size: 13pt;
            font-weight: bold;
        }
        .step-title {
            font-size: 14pt;
            font-weight: bold;
            color: #111827;
            padding-left: 8px;
        }
        .step-body { margin: 0 0 8px 38px; }

        .step-list { margin: 8px 0 0 38px; padding: 0; list-style: none; }
        .step-list li { margin-bottom: 7px; padding-left: 18px; position: relative; }
        .step-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #1D4ED8;
            font-weight: bold;
        }

        /* Casillas para marcar con lápiz: el instructivo se imprime. */
        .check-list { margin: 10px 0 0 38px; padding: 0; list-style: none; }
        .check-list li {
            margin-bottom: 9px;
            padding-left: 26px;
            position: relative;
        }
        .check-list li:before {
            content: "";
            position: absolute;
            left: 0; top: 3px;
            width: 13px; height: 13px;
            border: 2px solid #6B7280;
        }

        .fixed-phrase {
            margin: 12px 0 0 38px;
            padding: 12px 16px;
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
            font-size: 11.5pt;
            color: #78350F;
        }

        /* ---------------- Fechas ---------------- */
        .dates { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .dates td {
            padding: 12px 14px;
            border: 1px solid #E5E7EB;
            font-size: 12pt;
        }
        .dates .label { background-color: #F9FAFB; color: #4B5563; width: 52%; }
        .dates .value { font-weight: bold; color: #111827; }

        /* ---------------- Avisos ---------------- */
        .warning {
            margin-bottom: 14px;
            padding: 14px 18px;
            background-color: #FEF2F2;
            border-left: 4px solid #DC2626;
            page-break-inside: avoid;
        }
        .warning-title { font-weight: bold; color: #991B1B; margin-bottom: 4px; }
        .warning-body { font-size: 11.5pt; color: #7F1D1D; }

        /* ---------------- Glosario ---------------- */
        .glossary { width: 100%; border-collapse: collapse; }
        .glossary td { padding: 9px 0; border-bottom: 1px solid #F3F4F6; vertical-align: top; }
        .glossary .term { width: 32%; font-weight: bold; color: #111827; }
        .glossary .def { color: #4B5563; font-size: 11.5pt; }

        /* ---------------- Abogados ---------------- */
        .lawyer-card {
            margin-bottom: 14px;
            padding: 16px 18px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            page-break-inside: avoid;
        }
        .lawyer-name { font-size: 12.5pt; font-weight: bold; margin: 0; }
        .lawyer-role { font-size: 11pt; color: #1D4ED8; margin: 2px 0 6px; }
        .lawyer-bio { font-size: 11pt; color: #4B5563; margin: 0 0 6px; }
        .lawyer-contact { font-size: 11pt; color: #374151; }

        .cta-box {
            margin-top: 20px;
            padding: 18px 20px;
            background-color: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
        }
        .cta-title { font-weight: bold; color: #1E3A8A; margin: 0 0 4px; }
        .cta-text { font-size: 11.5pt; color: #1E40AF; margin: 0; }
        .cta-url { margin-top: 8px; font-weight: bold; color: #1D4ED8; }

        h2.section {
            font-size: 15pt;
            color: #111827;
            margin: 0 0 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1D4ED8;
        }

        .disclaimer {
            margin-top: 20px;
            font-size: 9.5pt;
            color: #9CA3AF;
            line-height: 1.5;
        }

        .footer-note {
            margin-top: 18px;
            padding: 14px 18px;
            background-color: #F9FAFB;
            border: 1px dashed #D1D5DB;
            font-size: 11pt;
            color: #4B5563;
        }
    </style>
</head>
<body>

{{-- ══════════════════════ Portada y pasos ══════════════════════ --}}
<div class="page">
    <div class="cover">
        <div class="brand">Hazlo<span class="hl">Legal</span></div>
        <div class="cover-kicker">Instructivo paso a paso</div>
        <div class="cover-title">{{ $document->short_name ?: $document->name }}</div>
        <div class="cover-for">
            Preparado para {{ $order->customer_name }} · {{ now()->translatedFormat('j \d\e F \d\e Y') }}
        </div>
    </div>

    @if ($document->instructions_intro)
        <p class="intro">{{ $document->instructions_intro }}</p>
    @endif

    <div class="pad">
        @if ($attachments)
            <h2 class="section">Lo que necesita tener a la mano</h2>
            <ul class="check-list">
                @foreach ($attachments as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <div style="height: 24px;"></div>
        @endif

        <h2 class="section">Qué hacer, paso por paso</h2>

        @foreach ($steps as $i => $step)
            <div class="step">
                <div class="step-head">
                    <span class="step-num">{{ $i + 1 }}</span><span class="step-title">{{ $step['title'] }}</span>
                </div>

                @if (! empty($step['body']))
                    <p class="step-body">{{ $step['body'] }}</p>
                @endif

                @if (! empty($step['items']))
                    <ul class="step-list">
                        @foreach ($step['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($step['phrase']))
                    <p class="fixed-phrase">{{ $step['phrase'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════ Fechas, avisos y glosario ══════════════════════ --}}
<div class="page pad">
    <h2 class="section">Sus fechas</h2>

    <table class="dates">
        <tr>
            <td class="label">Documento preparado el</td>
            <td class="value">{{ now()->translatedFormat('j \d\e F \d\e Y') }}</td>
        </tr>
        @if ($deadlineDays)
            <tr>
                <td class="label">Plazo que tiene {{ $document->filing_entity ?: 'la entidad' }} para responder</td>
                <td class="value">{{ $deadlineDays }} días hábiles</td>
            </tr>
            <tr>
                <td class="label">Si lo radica hoy, vence el</td>
                <td class="value">{{ $deadlineDate?->translatedFormat('l j \d\e F \d\e Y') }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Su número de referencia con nosotros</td>
            <td class="value">{{ $order->reference }}</td>
        </tr>
    </table>

    <p class="footer-note">
        El plazo empieza a contar <strong>el día hábil siguiente</strong> al que usted lo radique,
        no hoy. Los sábados, domingos y festivos no cuentan. Si radica en otra fecha, sume los
        días hábiles desde ese día.
    </p>

    @if ($warnings)
        <div style="height: 28px;"></div>
        <h2 class="section">Cuándo esto no alcanza</h2>

        @foreach ($warnings as $warning)
            <div class="warning">
                <p class="warning-title">{{ $warning['title'] }}</p>
                <p class="warning-body">{{ $warning['body'] }}</p>
            </div>
        @endforeach
    @endif

    @if ($glossary)
        <div style="height: 28px;"></div>
        <h2 class="section">Palabras que va a encontrar</h2>

        <table class="glossary">
            @foreach ($glossary as $term)
                <tr>
                    <td class="term">{{ $term['term'] }}</td>
                    <td class="def">{{ $term['definition'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>

{{-- ══════════════════════ Ayuda ══════════════════════ --}}
<div class="page pad">
    <h2 class="section">Si necesita ayuda</h2>

    <p>
        Primero, lo gratuito: la <strong>Defensoría del Pueblo</strong>, la
        <strong>personería de su municipio</strong> y los <strong>consultorios jurídicos</strong> de
        las universidades atienden sin costo y pueden orientarlo o incluso redactar por usted.
    </p>

    @if ($lawyers->isNotEmpty())
        <p style="margin-top: 18px;">
            Y si prefiere que alguien se encargue, estos abogados de nuestra red trabajan
            {{ \Illuminate\Support\Str::lower($document->practice_area->getLabel()) }}:
        </p>

        @foreach ($lawyers as $lawyer)
            <div class="lawyer-card">
                <p class="lawyer-name">{{ $lawyer->displayName() }}</p>
                <p class="lawyer-role">{{ $lawyer->headline }}</p>

                @if ($lawyer->bio)
                    <p class="lawyer-bio">{{ \Illuminate\Support\Str::limit($lawyer->bio, 190) }}</p>
                @endif

                <div class="lawyer-contact">
                    <strong>{{ $lawyer->city }}</strong>
                    @if ($lawyer->offers_virtual) · Atiende virtual @endif
                    @if ($lawyer->email) <br>{{ $lawyer->email }} @endif
                    @if ($lawyer->phone) · {{ $lawyer->phone }} @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="cta-box">
        <p class="cta-title">¿Necesita otro documento?</p>
        <p class="cta-text">
            Derechos de petición, contratos, reclamaciones y más. Varios son gratis, y todos
            vienen con un instructivo como este.
        </p>
        <div class="cta-url">{{ \Illuminate\Support\Str::after($brand['site'], '://') }}</div>
    </div>

    <p class="disclaimer">
        {{ $document->instructions_footer ?: $brand['name'].' no radica por usted.' }}
        Este instructivo es información general sobre el trámite y no constituye asesoría
        jurídica particular. {{ $brand['name'] }} no garantiza el resultado de la decisión de
        una entidad o de un juez. Los abogados que aparecen en esta página son profesionales
        independientes inscritos en nuestra red; la relación que establezca con ellos es
        directa. Instructivo generado el {{ now()->translatedFormat('j \d\e F \d\e Y') }}.
    </p>
</div>

</body>
</html>
