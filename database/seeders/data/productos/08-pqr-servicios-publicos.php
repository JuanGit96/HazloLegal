<?php

/*
|--------------------------------------------------------------------------
| PQR y recursos de servicios públicos domiciliarios
|--------------------------------------------------------------------------
| Lo que define este producto es el silencio administrativo positivo: si la
| empresa no responde en quince días hábiles, la ley entiende que le dio la
| razón al usuario. Casi nadie lo sabe, y es la parte del instructivo que
| más valor entrega.
*/

return [
    'clauses' => [
        [
            'key' => 'revision_factura',
            'label' => 'Que revisen y corrijan mi factura',
            'body' => 'Revisar y corregir la factura del periodo {periodo}, por valor de {valor cobrado}, cuyo cobro no corresponde al consumo real del inmueble.',
            'legal_basis' => 'Ley 142 de 1994, régimen de los servicios públicos domiciliarios, en lo relativo al derecho de petición, quejas y recursos del usuario.',
            'variables' => [
                ['key' => 'periodo', 'label' => '¿De qué periodo es la factura?', 'type' => 'text'],
                ['key' => 'valor cobrado', 'label' => '¿Cuánto le cobraron?', 'type' => 'number'],
            ],
        ],
        [
            'key' => 'reliquidacion',
            'label' => 'Que me reliquiden el cobro',
            'body' => 'Reliquidar el cobro del periodo {periodo}, ajustándolo al consumo promedio del inmueble, y abonar la diferencia a mi cuenta.',
            'legal_basis' => 'Ley 142 de 1994, en lo relativo a la liquidación del consumo y al cobro de los servicios.',
            'variables' => [
                ['key' => 'periodo', 'label' => '¿De qué periodo?', 'type' => 'text'],
            ],
        ],
        [
            'key' => 'revision_medidor',
            'label' => 'Que revisen el medidor',
            'body' => 'Realizar la revisión técnica del medidor instalado en el inmueble y entregarme copia del acta de la visita con sus resultados.',
            'legal_basis' => 'Ley 142 de 1994, en lo relativo a los instrumentos de medición del consumo.',
        ],
        [
            'key' => 'restablecer',
            'label' => 'Que me reconecten el servicio',
            'body' => 'Restablecer de inmediato el servicio de {servicio}, suspendido el {fecha de suspension}, e informarme el fundamento de la suspensión.',
            'legal_basis' => 'Ley 142 de 1994, en lo relativo a la suspensión y el restablecimiento del servicio.',
            'variables' => [
                ['key' => 'servicio', 'label' => '¿Qué servicio le suspendieron?', 'type' => 'text'],
                ['key' => 'fecha de suspension', 'label' => '¿Cuándo se lo suspendieron?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'calidad',
            'label' => 'Que corrijan fallas en la prestación',
            'body' => 'Corregir las fallas en la prestación del servicio descritas en los hechos, e informarme las medidas adoptadas y el plazo en que quedarán ejecutadas.',
            'legal_basis' => 'Ley 142 de 1994, en lo relativo a la calidad y continuidad en la prestación del servicio.',
        ],
        [
            'key' => 'libre',
            'label' => 'Otra cosa (la escribo yo)',
            'body' => '{peticion}',
            'is_free_text' => true,
            'max_chars' => 400,
            'variables' => [
                ['key' => 'peticion', 'label' => '¿Qué quiere pedir?', 'type' => 'textarea'],
            ],
        ],
    ],

    'common_basis' => 'Artículo 23 de la Constitución Política. Ley 142 de 1994, régimen de los '
        .'servicios públicos domiciliarios, en lo relativo al derecho de petición, quejas y recursos '
        .'del usuario y al término para resolverlos.',

    'closing' => 'Solicito que la respuesta se me notifique por escrito dentro del término legal. '
        .'De no obtenerse respuesta dentro de dicho término, solicito que se reconozcan los efectos '
        .'del silencio administrativo positivo.',

    'risk' => [
        ['kind' => 'question', 'level' => 'red', 'key' => 'ya_negaron', 'prompt' => '¿Ya le respondieron negando y está dentro de los 5 días siguientes?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'suspension', 'prompt' => '¿Le suspendieron el servicio?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'acumulado', 'prompt' => '¿Es el tercer periodo o más con el mismo problema?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'salud', 'prompt' => '¿En el inmueble vive alguien cuya salud depende del servicio?'],

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'amber', 'prompt' => $p], [
            'me cortaron', 'suspendieron', 'sin agua', 'sin luz', 'tercera vez',
            'ya reclamé', 'me negaron',
        ]),
    ],

    'fields' => [
        ['key' => 'empresa', 'label' => '¿Qué empresa le presta el servicio?', 'type' => 'text', 'required' => true],
        ['key' => 'servicio', 'label' => '¿Qué servicio?', 'type' => 'text', 'required' => true,
         'help' => 'Energía, gas, acueducto, alcantarillado o aseo.'],
        ['key' => 'cuenta', 'label' => 'Número de cuenta o contrato', 'type' => 'text', 'required' => true,
         'help' => 'Aparece en la parte de arriba de su factura.'],
        ['key' => 'direccion_inmueble', 'label' => 'Dirección del inmueble', 'type' => 'text', 'required' => true],
        ['key' => 'periodo', 'label' => 'Periodo de la factura que reclama', 'type' => 'text'],
        ['key' => 'valor_correcto', 'label' => '¿Cuánto cree que debería pagar?', 'type' => 'number'],
    ],

    'filing_entity' => 'la empresa de servicios públicos',
    'response_deadline_days' => 15,

    'instructions_intro' => 'Esta reclamación obliga a la empresa a responderle en quince días '
        .'hábiles. Y hay algo que casi nadie sabe: si no le responden a tiempo, la ley entiende que '
        .'le dieron la razón. Esta guía le explica cómo aprovecharlo.',

    'instructions_attachments' => [
        'La reclamación impresa y firmada (dos copias).',
        'Copia de la factura que está reclamando.',
        'Fotos del medidor, si el reclamo es por consumo.',
        'Cualquier soporte que mencionó en los hechos.',
    ],

    'filing_steps' => [
        [
            'title' => 'Preséntelo por uno de estos canales',
            'body' => 'Los tres sirven igual:',
            'items' => [
                'La oficina de atención al usuario de la empresa.',
                'El canal web de la empresa, si lo tiene.',
                'El correo de atención al usuario.',
            ],
        ],
        [
            'title' => 'Pida constancia de radicación',
            'body' => 'Con fecha y hora. Si va presencial, lleve dos copias y pida que le sellen una.',
            'phrase' => 'Sin constancia no puede reclamar el silencio positivo después.',
        ],
        [
            'title' => 'No pague lo que está reclamando, pero sí lo demás',
            'body' => 'Mientras la empresa resuelve, usted puede pagar la parte de la factura que no '
                .'está discutiendo. Pagar completo no significa que renuncia al reclamo, pero dejar '
                .'de pagar todo sí puede traerle una suspensión.',
        ],
        [
            'title' => 'Cuente quince días hábiles',
            'body' => 'En la página siguiente está la fecha exacta si lo radica hoy. Anótela en algún '
                .'lado visible: de esa fecha depende lo que viene.',
        ],
        [
            'title' => 'Si no le responden a tiempo, ganó',
            'body' => 'Esto es lo más importante de esta guía:',
            'items' => [
                'Si pasan los quince días hábiles sin respuesta, la ley entiende que la empresa le dio la razón. Se llama silencio administrativo positivo.',
                'La empresa debe reconocerlo dentro de las 72 horas siguientes al vencimiento.',
                'Si no lo hace, usted puede exigirlo: presente un escrito pidiendo que se reconozcan los efectos del silencio positivo, con la copia radicada y la fecha.',
            ],
            'phrase' => 'Si no le responden dentro del plazo, la ley entiende que le dieron la razón. No deje pasar esa fecha.',
        ],
        [
            'title' => 'Si le responden negando',
            'body' => 'Tiene cinco días hábiles para presentar los recursos de reposición y, en '
                .'subsidio, de apelación. El de apelación lo resuelve la Superintendencia de Servicios '
                .'Públicos Domiciliarios, no la empresa.',
            'phrase' => 'Cinco días hábiles es poco. Cuéntelos desde el día siguiente a la notificación y no lo deje para el final.',
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si ya le respondieron negando, el plazo corre',
            'body' => 'Lo que procede es el recurso dentro de los cinco días hábiles siguientes, no '
                .'una reclamación nueva. Presentar otra reclamación mientras corre ese plazo puede '
                .'costarle la oportunidad de recurrir.',
        ],
        [
            'title' => 'Si le suspendieron el servicio y hay alguien enfermo en casa',
            'body' => 'Cuando la salud de una persona depende del servicio —oxígeno, diálisis, '
                .'refrigeración de medicamentos—, lo que corresponde es una acción de tutela, que es '
                .'gratuita y se resuelve en diez días. No espere los quince días de la reclamación.',
        ],
    ],

    'glossary' => [
        ['term' => 'Silencio administrativo positivo', 'definition' => 'Si la empresa no le responde en el plazo, la ley entiende que aceptó lo que usted pidió. Es a su favor.'],
        ['term' => 'Recurso de reposición', 'definition' => 'Pedirle a la misma empresa que reconsidere su decisión.'],
        ['term' => 'Recurso de apelación', 'definition' => 'Pedirle a la Superintendencia que revise lo que decidió la empresa. Se presenta junto con el de reposición.'],
        ['term' => 'Superservicios', 'definition' => 'Superintendencia de Servicios Públicos Domiciliarios. Vigila a las empresas y resuelve las apelaciones.'],
        ['term' => 'Días hábiles', 'definition' => 'De lunes a viernes, sin contar festivos.'],
    ],

    'instructions_footer' => 'HazloLegal no radica por usted.',
];
