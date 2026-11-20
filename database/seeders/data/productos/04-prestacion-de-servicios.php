<?php

/*
|--------------------------------------------------------------------------
| Contrato de prestación de servicios
|--------------------------------------------------------------------------
| Cuatro riesgos que este producto tiene que manejar: que en realidad sea
| un contrato de trabajo disfrazado, la seguridad social del contratista,
| la propiedad intelectual de lo que se entregue, y la forma de pago.
|
| El más delicado es el primero. Si hay horario, subordinación y
| exclusividad, lo que hay es un contrato laboral, y llamarlo de otra
| manera no cambia nada ante un juez.
*/

return [
    'template_heading' => 'CONTRATO DE PRESTACIÓN DE SERVICIOS',

    'template_body' => <<<'TXT'
Entre {{ cliente.nombre }}, identificado con {{ cliente.documento }}, quien obra como CONTRATANTE, y {{ campo.contratista_nombre }}, identificado con {{ campo.contratista_documento }}, quien obra como CONTRATISTA, se celebra el presente contrato de prestación de servicios, que se regirá por las siguientes cláusulas:

PRIMERA. OBJETO. El CONTRATISTA se obliga a prestar, con plena autonomía técnica, administrativa y directiva, los siguientes servicios: {{ campo.objeto }}

SEGUNDA. AUTONOMÍA. El CONTRATISTA desarrollará el objeto con sus propios medios y bajo su propia dirección. No habrá subordinación ni dependencia, ni cumplimiento de horario, ni exclusividad. El CONTRATISTA podrá prestar servicios a terceros durante la vigencia de este contrato.

TERCERA. ENTREGABLES Y PLAZOS. Los entregables, sus fechas y sus criterios de aceptación constan en el anexo que hace parte integral de este contrato.

CUARTA. VALOR Y FORMA DE PAGO. El valor del contrato es de {{ campo.valor }}. {{ campo.forma_pago }}

QUINTA. DURACIÓN. El contrato estará vigente desde {{ campo.fecha_inicio }} hasta {{ campo.fecha_fin }}.

SEXTA. SEGURIDAD SOCIAL. El CONTRATISTA declara que se encuentra afiliado al Sistema de Seguridad Social Integral y se obliga a acreditar el pago de sus aportes como condición para cada desembolso, en los términos que exige la ley.

SÉPTIMA. PROPIEDAD INTELECTUAL. {{ campo.propiedad_intelectual }}

OCTAVA. CONFIDENCIALIDAD. Las partes guardarán reserva sobre la información que conozcan con ocasión de este contrato y no la usarán para fines distintos a su ejecución.

NOVENA. TERMINACIÓN. Cualquiera de las partes podrá terminar el contrato avisando por escrito con {{ campo.preaviso }} de antelación. Se pagará lo efectivamente ejecutado hasta la fecha de terminación.

DÉCIMA. INDEPENDENCIA. Este contrato no genera relación laboral entre las partes. El CONTRATISTA asume la totalidad de las obligaciones derivadas de su condición de independiente.

UNDÉCIMA. LEY Y JURISDICCIÓN. Este contrato se rige por la ley colombiana. Las controversias se someterán a los jueces de {{ campo.jurisdiccion }}.

En constancia se firma el {{ fecha.larga }}.


_______________________________        _______________________________
CONTRATANTE                            CONTRATISTA
{{ cliente.nombre }}                   {{ campo.contratista_nombre }}
{{ cliente.documento }}                {{ campo.contratista_documento }}
TXT,

    'fields' => [
        ['key' => 'contratista_nombre', 'label' => 'Nombre o razón social del contratista', 'type' => 'text', 'required' => true],
        ['key' => 'contratista_documento', 'label' => 'Documento o NIT del contratista', 'type' => 'text', 'required' => true],
        ['key' => 'objeto', 'label' => '¿Qué servicios se van a prestar?', 'type' => 'textarea', 'required' => true,
         'help' => 'Descríbalo por resultados, no por horas: «entregar el diseño del sitio web», no «trabajar de 8 a 5».'],
        ['key' => 'valor', 'label' => 'Valor total del contrato', 'type' => 'text', 'required' => true],
        ['key' => 'forma_pago', 'label' => '¿Cómo se paga?', 'type' => 'textarea', 'required' => true,
         'help' => 'Por entregables, mensual, un anticipo y un saldo… Y en cuántos días se paga cada cuenta de cobro.'],
        ['key' => 'fecha_inicio', 'label' => 'Fecha de inicio', 'type' => 'date', 'required' => true],
        ['key' => 'fecha_fin', 'label' => 'Fecha de terminación', 'type' => 'date', 'required' => true],
        ['key' => 'propiedad_intelectual', 'label' => '¿Quién se queda con lo que se produzca?', 'type' => 'textarea',
         'help' => 'Si el contratante necesita los derechos, hay que cederlos por escrito y decir para qué usos y por cuánto tiempo. Sin eso, los conserva el autor.'],
        ['key' => 'preaviso', 'label' => 'Preaviso para terminar', 'type' => 'text', 'required' => true,
         'help' => 'Por ejemplo: «quince (15) días calendario».'],
        ['key' => 'jurisdiccion', 'label' => '¿Jueces de qué ciudad?', 'type' => 'text', 'required' => true],
        ['key' => 'riesgo_arl', 'label' => 'Nivel de riesgo de la actividad', 'type' => 'text',
         'help' => 'I a III lo paga el contratista; IV y V los paga el contratante. Si no sabe, déjelo vacío.'],
    ],

    'risk' => [
        ['kind' => 'question', 'level' => 'red', 'key' => 'horario', 'prompt' => '¿La persona tiene que cumplir un horario fijo?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'subordinacion', 'prompt' => '¿Recibe órdenes sobre cómo hacer el trabajo, no solo sobre qué entregar?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'exclusividad', 'prompt' => '¿Se le exige exclusividad?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'larga_duracion', 'prompt' => '¿El contrato dura más de un año y se renueva igual cada vez?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'riesgo_alto', 'prompt' => '¿La actividad es de riesgo IV o V?'],

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'red', 'prompt' => $p], [
            'horario', 'de 8 a 5', 'jefe', 'subordinación', 'exclusividad', 'planta',
        ]),
    ],

    'filing_entity' => null,
    'response_deadline_days' => null,

    'instructions_intro' => 'Este es su contrato de prestación de servicios y el anexo de '
        .'entregables. Esta guía le dice qué revisar antes de firmar y qué tiene que hacer cada '
        .'parte desde el primer día, sobre todo con la seguridad social y la facturación.',

    'instructions_attachments' => [
        'El contrato impreso, un ejemplar por parte.',
        'El anexo de entregables, firmado igual que el contrato.',
        'Copia del documento de identidad de cada firmante.',
        'Si el contratista es una empresa: certificado de existencia y representación legal.',
        'La planilla de seguridad social del contratista, si ya está afiliado.',
    ],

    'filing_steps' => [
        [
            'title' => 'Antes de firmar, verifique',
            'body' => 'Nombres, documentos, NIT y representación legal de cada parte. Si el '
                .'contratista es persona jurídica, revise el certificado de existencia y que quien '
                .'firma sea el representante legal.',
        ],
        [
            'title' => 'Firmen también el anexo de entregables',
            'body' => 'Va aparte y con su propia firma porque es lo que más cambia durante la '
                .'ejecución. Debe tener, por cada entregable: qué es, para cuándo, con qué criterio '
                .'se acepta, cuánto vale si el pago es por entregable, y un espacio con fecha y '
                .'firma para dejar constancia de que se recibió.',
            'phrase' => 'Ese espacio de aceptación es lo que después prueba que el trabajo se entregó y se recibió a satisfacción.',
        ],
        [
            'title' => 'Seguridad social: esto le toca al contratista',
            'body' => 'Es la obligación que más se incumple y la que más problemas trae después:',
            'items' => [
                'La obligación de cotizar nace cuando los ingresos netos mensuales igualan o superan un salario mínimo.',
                'La base mínima de cotización es el 40% del valor mensualizado del contrato, sin IVA.',
                'Si el contrato dura más de un mes, la afiliación a riesgos laborales es obligatoria.',
                'En riesgos I a III la ARL la paga el contratista; en riesgos IV y V la paga el contratante, incluso en contratos de menos de un mes.',
            ],
            'phrase' => 'El contratante debe exigir la planilla de pago antes de cada desembolso. No es trámite: es su respaldo si después hay una reclamación.',
        ],
        [
            'title' => 'Facturación',
            'body' => 'La cuenta de cobro o la factura electrónica debe identificar a las dos '
                .'partes, describir el servicio prestado, indicar el periodo y el valor, y señalar '
                .'si hay retenciones. El plazo de pago es el que pactaron en el contrato.',
        ],
        [
            'title' => 'Propiedad intelectual',
            'body' => 'Si lo que se contrata produce algo creativo —un diseño, un texto, un '
                .'software—, los derechos patrimoniales se quedan con el autor salvo que se cedan '
                .'por escrito. Revise que la cláusula diga qué se cedió, para qué usos, por cuánto '
                .'tiempo y para qué territorio.',
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si hay horario, órdenes y exclusividad, esto es un contrato de trabajo',
            'body' => 'Llamarlo «prestación de servicios» no cambia nada ante un juez. Si la persona '
                .'cumple horario, recibe instrucciones sobre cómo hacer el trabajo y no puede '
                .'trabajar para otros, lo que hay es una relación laboral, con prestaciones, '
                .'cesantías y aportes. Usar este contrato en ese caso puede costarle mucho más de '
                .'lo que se ahorra.',
        ],
        [
            'title' => 'Si lo que necesita es un contrato de trabajo',
            'body' => 'Por ahora no lo tenemos automatizado. Puede ajustar la forma de ejecución '
                .'para que sea de verdad autónoma, o consultar con un abogado de los que aparecen '
                .'al final de esta guía.',
        ],
    ],

    'glossary' => [
        ['term' => 'Subordinación', 'definition' => 'Que alguien le diga no solo qué hacer, sino cómo, cuándo y dónde. Es la marca de una relación laboral.'],
        ['term' => 'Entregable', 'definition' => 'Un resultado concreto que se puede recibir y aprobar: un informe, un diseño, una obra terminada.'],
        ['term' => 'IBC', 'definition' => 'Ingreso base de cotización. La cifra sobre la que se calculan los aportes a salud y pensión.'],
        ['term' => 'ARL', 'definition' => 'Administradora de riesgos laborales. Cubre accidentes y enfermedades del trabajo.'],
        ['term' => 'Derechos patrimoniales', 'definition' => 'El derecho a explotar económicamente una obra. Se pueden ceder; los morales no.'],
    ],

    'instructions_footer' => 'HazloLegal no interviene en la relación entre contratante y contratista.',
];
