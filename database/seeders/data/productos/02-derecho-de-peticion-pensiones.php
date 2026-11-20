<?php

/*
|--------------------------------------------------------------------------
| Derecho de petición a fondos de pensiones
|--------------------------------------------------------------------------
| Mismo escrito y mismo formulario que el de EPS. Lo que cambia es el
| directorio, el fundamento, el semáforo y —sobre todo— el contador: en
| materia pensional los plazos son de meses, no de días. Avisar que venció
| un plazo que no ha vencido manda al usuario a insistir sin razón.
*/

return [
    'clauses' => [
        [
            'key' => 'historia_laboral',
            'label' => 'Que me entreguen mi historia laboral',
            'body' => 'Entregar mi historia laboral completa y actualizada, con el detalle de los periodos cotizados y los empleadores reportados.',
            'legal_basis' => 'Normas sobre historia laboral y deber de información de las administradoras del Sistema General de Pensiones.',
            'response_deadline_days' => 10,
        ],
        [
            'key' => 'correccion_historia',
            'label' => 'Que corrijan mi historia laboral',
            'body' => 'Corregir mi historia laboral, incluyendo los periodos cotizados con el empleador {empleador} entre el {fecha inicial} y el {fecha final}, conforme a los soportes anexos.',
            'legal_basis' => 'Normas sobre historia laboral y deber de información de las administradoras del Sistema General de Pensiones.',
            'variables' => [
                ['key' => 'empleador', 'label' => '¿Con qué empleador?', 'type' => 'text'],
                ['key' => 'fecha inicial', 'label' => '¿Desde cuándo trabajó ahí?', 'type' => 'date'],
                ['key' => 'fecha final', 'label' => '¿Hasta cuándo?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'certificacion_semanas',
            'label' => 'Que me certifiquen cuántas semanas llevo cotizadas',
            'body' => 'Expedir certificación del total de semanas cotizadas a la fecha de esta solicitud.',
            'legal_basis' => 'Normas sobre historia laboral y deber de información de las administradoras del Sistema General de Pensiones.',
            'response_deadline_days' => 10,
        ],
        [
            'key' => 'estado_pension',
            'label' => 'Que me digan cómo va mi solicitud de pensión',
            'body' => 'Informar el estado del trámite de reconocimiento de pensión de {modalidad} radicado el {fecha}, con el número de radicado {radicado}.',
            'legal_basis' => 'Ley 100 de 1993 y Ley 797 de 2003, en lo pertinente al reconocimiento de la pensión.',
            // Reconocimiento: cuatro meses, no quince días.
            'response_deadline_days' => 120,
            'variables' => [
                ['key' => 'modalidad', 'label' => '¿Pensión de qué?', 'type' => 'text', 'help' => 'Vejez, invalidez o sobrevivientes.'],
                ['key' => 'fecha', 'label' => '¿Cuándo la radicó?', 'type' => 'date'],
                ['key' => 'radicado', 'label' => 'Número de radicado', 'type' => 'text'],
            ],
        ],
        [
            'key' => 'mesadas',
            'label' => 'Que me paguen mesadas que no me han pagado',
            'body' => 'Pagar las mesadas pensionales causadas y no pagadas correspondientes al periodo entre el {fecha inicial} y el {fecha final}.',
            'legal_basis' => 'Normas sobre pago de mesadas pensionales causadas.',
            'variables' => [
                ['key' => 'fecha inicial', 'label' => '¿Desde qué mes?', 'type' => 'date'],
                ['key' => 'fecha final', 'label' => '¿Hasta qué mes?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'indemnizacion_sustitutiva',
            'label' => 'Que me reconozcan la indemnización sustitutiva',
            'body' => 'Reconocer y pagar la indemnización sustitutiva de la pensión de vejez, conforme a los requisitos que acredito.',
            'legal_basis' => 'Ley 100 de 1993 y Ley 797 de 2003, en lo pertinente.',
            'response_deadline_days' => 120,
        ],
        [
            'key' => 'devolucion_saldos',
            'label' => 'Que me devuelvan los saldos de mi cuenta',
            'body' => 'Reconocer y pagar la devolución de saldos de mi cuenta de ahorro individual.',
            'legal_basis' => 'Ley 100 de 1993 y Ley 797 de 2003, en lo pertinente.',
            'response_deadline_days' => 120,
        ],
        [
            'key' => 'bono_pensional',
            'label' => 'Que me digan cómo va mi bono pensional',
            'body' => 'Informar el estado del trámite de emisión o redención de mi bono pensional.',
            'legal_basis' => 'Normas sobre emisión y redención de bonos pensionales.',
        ],
        [
            'key' => 'expediente',
            'label' => 'Que me entreguen copia de mi expediente',
            'body' => 'Entregar copia íntegra del expediente administrativo de mi solicitud de pensión.',
            'legal_basis' => 'Derecho de acceso al expediente administrativo.',
            'response_deadline_days' => 10,
        ],
        [
            'key' => 'libre',
            'label' => 'Otra petición (la escribo yo)',
            'body' => '{peticion}',
            'is_free_text' => true,
            'max_chars' => 400,
            'variables' => [
                ['key' => 'peticion', 'label' => '¿Qué quiere pedir?', 'type' => 'textarea'],
            ],
        ],
    ],

    'common_basis' => 'Artículo 23 de la Constitución Política. Artículos 13 a 33 de la Ley 1755 de 2015. '
        .'Artículo 33 de la Ley 1755 de 2015, que somete a las entidades del Sistema de Seguridad Social '
        .'Integral a las mismas reglas del derecho de petición ante autoridades.',

    'closing' => 'Solicito que la respuesta se me notifique a la dirección y al correo señalados en el '
        .'acápite de notificaciones, dentro del término legal.',

    'risk' => [
        ['kind' => 'question', 'level' => 'red', 'key' => 'traslado', 'prompt' => '¿Su petición tiene que ver con un traslado entre régimen público y privado, o con la ineficacia de ese traslado?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'reliquidacion', 'prompt' => '¿Está pidiendo reliquidación o discutiendo el ingreso base de liquidación?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'ya_negaron', 'prompt' => '¿Ya le negaron la pensión mediante un acto que le fue notificado?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'muchos_empleadores', 'prompt' => '¿Necesita que le cuenten semanas de más de tres empleadores que no aparecen reportados?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'empleador_liquidado', 'prompt' => '¿Alguno de esos empleadores ya no existe o fue liquidado?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'edad_espera', 'prompt' => '¿Tiene más de 60 años y lleva más de un año esperando respuesta?'],

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'red', 'prompt' => $p], [
            'traslado', 'me trasladaron', 'ineficacia', 'régimen de prima media',
            'régimen de ahorro individual', 'reliquidación', 'IBL', 'me negaron la pensión',
            'resolución que niega', 'demanda', 'proceso ordinario laboral',
        ]),

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'amber', 'prompt' => $p], [
            'empleador liquidado', 'no me reportaron', 'semanas perdidas', 'hace años',
            'ya reclamé', 'segunda vez',
        ]),
    ],

    'fields' => [
        ['key' => 'entidad', 'label' => '¿A qué fondo o administradora va dirigido?', 'type' => 'text', 'required' => true,
         'help' => 'Colpensiones, Porvenir, Protección, Colfondos, Skandia, Fiduprevisora o la UGPP.'],
        ['key' => 'cedula_afiliado', 'label' => 'Número de afiliación o cédula con la que cotiza', 'type' => 'text'],
    ],

    'filing_entity' => 'su fondo de pensiones',
    'response_deadline_days' => 15,

    'instructions_intro' => 'Un derecho de petición obliga a su fondo de pensiones a responderle por '
        .'escrito. Ojo con los plazos: en pensiones no siempre son quince días. Esta guía le dice qué '
        .'hacer con el documento y cuánto tiene que esperar según lo que pidió.',

    'instructions_attachments' => [
        'El escrito impreso y firmado (dos copias).',
        'Copia de su documento de identidad.',
        'Si pide corrección de historia laboral: certificados laborales, planillas de pago, contratos o cartas de los empleadores.',
        'Si pide reconocimiento de pensión: registro civil cuando aplique y los soportes que el fondo exija.',
        'Si es pensión de sobrevivientes: registro civil de defunción y lo que acredite su calidad de beneficiario.',
    ],

    'filing_steps' => [
        [
            'title' => 'Imprima y firme',
            'body' => 'Dos copias, firmadas a mano. Revise que su nombre y su cédula estén exactos.',
        ],
        [
            'title' => 'Preséntelo en su fondo',
            'body' => 'Puede hacerlo de tres maneras:',
            'items' => [
                'En una oficina de atención del fondo. Le sellan la copia ahí mismo.',
                'Por el canal de PQRS de la página web. Guarde el número que le dé el sistema.',
                'Por correo electrónico al buzón de atención. Pida confirmación de recibido.',
            ],
            'phrase' => 'Pida siempre la copia sellada o el número de radicado. Es lo único que prueba que presentó la solicitud.',
        ],
        [
            'title' => 'Sepa cuánto tiene que esperar de verdad',
            'body' => 'Esto es lo que más se confunde. El plazo depende de lo que pidió:',
            'items' => [
                'Respuesta preliminar a cualquier petición: 15 días hábiles.',
                'Historia laboral, certificaciones y copias: 10 días hábiles.',
                'Pensión de vejez, invalidez, reliquidación o reajuste: 4 meses desde que radicó con documentos completos.',
                'Pensión de sobrevivientes: 2 meses.',
                'Pago de las mesadas una vez reconocida la pensión: 6 meses.',
            ],
            'phrase' => 'Si pidió el reconocimiento de una pensión, el fondo tiene meses, no días. Reclamar antes de tiempo no acelera nada.',
        ],
        [
            'title' => 'Cuando le respondan, revise',
            'body' => 'Mire si le responden todo lo que pidió, si llegó dentro del plazo y si resuelve '
                .'de fondo. Una carta que dice «su solicitud está en estudio» no es una respuesta.',
        ],
        [
            'title' => 'Si no le responden',
            'body' => 'En pensiones el camino no suele ser la tutela de entrada:',
            'items' => [
                'Insista por escrito, adjuntando la copia radicada de la primera petición.',
                'Presente una queja ante la Superintendencia Financiera, que vigila a los fondos.',
                'Vaya gratis a la Defensoría del Pueblo, a la personería o a un consultorio jurídico.',
                'La tutela cabe cuando hay una persona de edad avanzada, una situación de salud de por medio, o una demora que ya pasó largamente el plazo.',
            ],
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si le negaron la pensión por escrito, esto no es lo que corresponde',
            'body' => 'Contra una negativa ya notificada lo que proceden son los recursos, y tienen '
                .'plazos propios que se pueden vencer. Presentar una petición nueva en vez del recurso '
                .'puede costarle el derecho a discutir la decisión. Busque orientación antes.',
        ],
        [
            'title' => 'Los traslados de régimen y las reliquidaciones no se resuelven con una petición',
            'body' => 'Pasar de Colpensiones a un fondo privado o al revés, y discutir el ingreso base '
                .'de liquidación, son discusiones que tienen su propio camino. Pedirlo por derecho de '
                .'petición le hace perder meses. Aun así, puede usar este documento para pedir su '
                .'historia laboral o el estado de su trámite: eso sí sirve y le arma el expediente.',
        ],
    ],

    'glossary' => [
        ['term' => 'Historia laboral', 'definition' => 'El registro de todo lo que ha cotizado: cuántas semanas, con qué empleadores y en qué fechas.'],
        ['term' => 'Semanas cotizadas', 'definition' => 'El tiempo que ha aportado al sistema. Es lo que determina si tiene derecho a pensión.'],
        ['term' => 'Indemnización sustitutiva', 'definition' => 'Un pago único para quien llegó a la edad pero no alcanzó las semanas. Reemplaza la pensión.'],
        ['term' => 'Devolución de saldos', 'definition' => 'Lo equivalente en los fondos privados: le devuelven lo ahorrado más los rendimientos.'],
        ['term' => 'Bono pensional', 'definition' => 'Un título que representa lo que cotizó antes de entrar al sistema actual.'],
        ['term' => 'Días hábiles', 'definition' => 'De lunes a viernes, sin contar festivos.'],
    ],

    'instructions_footer' => 'HazloLegal no radica por usted.',
];
