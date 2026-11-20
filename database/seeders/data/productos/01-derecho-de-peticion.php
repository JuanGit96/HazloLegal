<?php

/*
|--------------------------------------------------------------------------
| Derecho de petición a EPS · casillas y semáforo
|--------------------------------------------------------------------------
| Transcrito de la especificación de producto 01. El texto de cada casilla
| es exacto: lo que entra al escrito palabra por palabra, con las variables
| entre llaves.
|
| Las marcadas con `needs_lawyer_review` esperan que un abogado apruebe o
| ajuste la redacción antes de publicarlas. Hasta entonces el panel las
| muestra en ámbar.
*/

return [
    'clauses' => [
        [
            'key' => 'procedimiento',
            'label' => 'Que autoricen un procedimiento ordenado por mi médico',
            'body' => 'Autorizar el procedimiento {procedimiento} ordenado por el médico {nombre del médico} el {fecha de la orden}, e informarme la fecha, la hora y el lugar en que se practicará.',
            'legal_basis' => 'Ley 1751 de 2015, estatutaria de salud, en lo relativo a la oportunidad, la continuidad y la integralidad del servicio.',
            'variables' => [
                ['key' => 'procedimiento', 'label' => '¿Qué procedimiento?', 'type' => 'text'],
                ['key' => 'nombre del médico', 'label' => '¿Qué médico lo ordenó?', 'type' => 'text'],
                ['key' => 'fecha de la orden', 'label' => '¿Cuándo lo ordenó?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'medicamentos',
            'label' => 'Que entreguen medicamentos formulados',
            'body' => 'Entregar los medicamentos {medicamentos} formulados el {fecha de la fórmula}, e indicarme el punto de dispensación y la fecha de entrega.',
            'legal_basis' => 'Ley 1751 de 2015, estatutaria de salud, en lo relativo a la oportunidad, la continuidad y la integralidad del servicio.',
            'variables' => [
                ['key' => 'medicamentos', 'label' => '¿Qué medicamentos?', 'type' => 'text'],
                ['key' => 'fecha de la fórmula', 'label' => '¿Cuándo se los formularon?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'cita_especialista',
            'label' => 'Que asignen cita con un especialista',
            'body' => 'Asignar cita con especialista en {especialidad}, ordenada el {fecha de la orden}, indicando fecha, hora, profesional y dirección.',
            'legal_basis' => 'Ley 1751 de 2015, estatutaria de salud, en lo relativo a la oportunidad, la continuidad y la integralidad del servicio.',
            'variables' => [
                ['key' => 'especialidad', 'label' => '¿Qué especialidad?', 'type' => 'text'],
                ['key' => 'fecha de la orden', 'label' => '¿Cuándo se ordenó la cita?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'transporte',
            'label' => 'Que cubran transporte y, si hace falta, alojamiento',
            'body' => 'Autorizar el transporte, y de ser necesario el alojamiento y la alimentación, para el paciente y un acompañante, con destino a {ciudad o IPS}.',
            'legal_basis' => 'Ley 1751 de 2015, estatutaria de salud, en lo relativo a la oportunidad, la continuidad y la integralidad del servicio.',
            'variables' => [
                ['key' => 'ciudad o IPS', 'label' => '¿A qué ciudad o IPS?', 'type' => 'text'],
            ],
        ],
        [
            'key' => 'historia_clinica',
            'label' => 'Que me entreguen copia de mi historia clínica',
            'body' => 'Entregar copia íntegra de mi historia clínica, incluyendo {periodo o servicio}, por el medio que resulte más expedito.',
            'legal_basis' => 'Régimen de la historia clínica. Al tratarse de una petición de documentos, el término es de diez (10) días hábiles.',
            // Pedir documentos corre a 10 días, no a 15.
            'response_deadline_days' => 10,
            'variables' => [
                ['key' => 'periodo o servicio', 'label' => '¿De qué periodo o servicio?', 'type' => 'text'],
            ],
        ],
        [
            'key' => 'incapacidades',
            'label' => 'Que me paguen incapacidades',
            'body' => 'Reconocer y pagar las incapacidades correspondientes al periodo comprendido entre el {fecha inicial} y el {fecha final}, e informarme el estado del trámite.',
            'legal_basis' => 'Normas sobre reconocimiento y pago de prestaciones económicas del Sistema General de Seguridad Social en Salud.',
            'variables' => [
                ['key' => 'fecha inicial', 'label' => '¿Desde cuándo?', 'type' => 'date'],
                ['key' => 'fecha final', 'label' => '¿Hasta cuándo?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'licencia',
            'label' => 'Que me paguen la licencia de maternidad o paternidad',
            'body' => 'Reconocer y pagar la licencia de {maternidad o paternidad} correspondiente al nacimiento ocurrido el {fecha}.',
            'legal_basis' => 'Normas sobre reconocimiento y pago de prestaciones económicas del Sistema General de Seguridad Social en Salud.',
            'variables' => [
                ['key' => 'maternidad o paternidad', 'label' => '¿Maternidad o paternidad?', 'type' => 'text'],
                ['key' => 'fecha', 'label' => '¿Cuándo nació?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'reembolso',
            'label' => 'Que me reembolsen un gasto que asumí',
            'body' => 'Reembolsar la suma de {valor} que asumí directamente por {concepto} el {fecha}, conforme a los soportes anexos.',
            'legal_basis' => 'Normas sobre reconocimiento y pago de prestaciones económicas del Sistema General de Seguridad Social en Salud.',
            'variables' => [
                ['key' => 'valor', 'label' => '¿Cuánto pagó?', 'type' => 'number'],
                ['key' => 'concepto', 'label' => '¿Por qué concepto?', 'type' => 'text'],
                ['key' => 'fecha', 'label' => '¿Cuándo lo pagó?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'calificacion_pcl',
            'label' => 'Que me informen el estado de la calificación de pérdida de capacidad laboral',
            'body' => 'Informar el estado del trámite de calificación de pérdida de capacidad laboral iniciado el {fecha}, e indicar los pasos y términos que siguen.',
            'legal_basis' => 'Normas sobre calificación de la pérdida de capacidad laboral.',
            'variables' => [
                ['key' => 'fecha', 'label' => '¿Cuándo se inició?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'traslado',
            'label' => 'Que autoricen mi traslado de EPS o la portabilidad',
            'body' => 'Autorizar el traslado a la EPS {EPS destino} o autorizar la portabilidad del servicio en el municipio de {municipio}.',
            'legal_basis' => 'Normas sobre libre elección, traslado y portabilidad en el Sistema General de Seguridad Social en Salud.',
            'variables' => [
                ['key' => 'EPS destino', 'label' => '¿A qué EPS quiere trasladarse?', 'type' => 'text'],
                ['key' => 'municipio', 'label' => '¿En qué municipio necesita el servicio?', 'type' => 'text'],
            ],
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

    /*
    | El fundamento que va en todas, se marque lo que se marque. El motor lo
    | concatena con los adicionales de cada casilla, sin repetir normas.
    */
    'common_basis' => 'Artículo 23 de la Constitución Política. Artículos 13 a 33 de la Ley 1755 de 2015. '
        .'Artículo 33 de la Ley 1755 de 2015, que somete a las entidades del Sistema de Seguridad Social '
        .'Integral a las mismas reglas del derecho de petición ante autoridades.',

    'closing' => 'Solicito que la respuesta se me notifique a la dirección y al correo señalados en el '
        .'acápite de notificaciones, dentro del término legal.',

    /*
    | El semáforo. Las preguntas son la capa que manda; las palabras son la
    | red de seguridad sobre el texto libre y nunca bloquean solas.
    */
    'risk' => [
        ['kind' => 'question', 'level' => 'red', 'key' => 'hospitalizado', 'prompt' => '¿El paciente está hospitalizado o en urgencias en este momento?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'urgente_48h', 'prompt' => '¿El servicio que le negaron lo necesita en menos de 48 horas?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'diagnostico_grave', 'prompt' => '¿Tiene diagnóstico de cáncer, enfermedad huérfana, o requiere diálisis o trasplante?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'menor', 'prompt' => '¿El paciente es menor de 18 años?', 'escalates_with_others' => true],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'embarazo', 'prompt' => '¿Está embarazada o en posparto?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'incapacidad_180', 'prompt' => '¿Lleva más de 180 días de incapacidad acumulada?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'ya_pidio', 'prompt' => '¿Ya presentó un derecho de petición por estos mismos hechos y no le respondieron?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'reembolso_alto', 'prompt' => '¿El valor del reembolso supera $2.000.000?'],

        // Palabras rojas: disparan la pregunta, no la decisión.
        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'red', 'prompt' => $p], [
            'urgencias', 'UCI', 'hospitalizado', 'cáncer', 'tumor', 'quimioterapia',
            'radioterapia', 'trasplante', 'diálisis', 'cuidados paliativos',
            'riesgo de muerte', 'se está muriendo', 'no puede respirar',
        ]),

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'amber', 'prompt' => $p], [
            'embarazo', 'parto', 'recaída', '180 días', 'junta de calificación',
            'tutela', 'desacato', 'segunda vez', 'ya reclamé',
        ]),
    ],

    /*
    |----------------------------------------------------------------------
    | El formulario
    |----------------------------------------------------------------------
    | Lo que se le pregunta además del relato de los hechos. Son los datos
    | que el escrito necesita y que no se pueden deducir.
    */
    'fields' => [
        ['key' => 'entidad', 'label' => '¿A qué EPS va dirigido?', 'type' => 'text', 'required' => true,
         'help' => 'El nombre completo, como aparece en su carné.'],
        ['key' => 'afiliacion', 'label' => 'Número de afiliación', 'type' => 'text',
         'help' => 'Si no lo tiene a la mano, déjelo vacío: el escrito funciona igual.'],
        ['key' => 'regimen', 'label' => 'Régimen', 'type' => 'text',
         'help' => 'Contributivo, subsidiado, o déjelo vacío si no sabe.'],
    ],

    /*
    |----------------------------------------------------------------------
    | El instructivo
    |----------------------------------------------------------------------
    | Lo que se cobra. Tiene que servirle a alguien que nunca ha radicado
    | nada: a dónde ir, qué llevar, qué pedir y qué hacer si no responden.
    */
    'filing_entity' => 'su EPS',
    'response_deadline_days' => 15,

    'instructions_intro' => 'Un derecho de petición es una carta que obliga a su EPS a responderle '
        .'por escrito y dentro de un plazo. Esta guía le dice qué hacer con el documento que acaba '
        .'de recibir, paso por paso.',

    'instructions_attachments' => [
        'El escrito impreso y firmado por usted (dos copias: una se la quedan y otra se la devuelven sellada).',
        'Copia de su documento de identidad.',
        'Las órdenes médicas, fórmulas o soportes que mencionó en los hechos.',
        'Si está pidiendo por otra persona: el poder o el documento que acredite el parentesco.',
    ],

    'filing_steps' => [
        [
            'title' => 'Imprima y firme',
            'body' => 'Imprima dos copias del escrito. Fírmelas de su puño y letra, igual que firma '
                .'su cédula. Revise antes que su nombre y su número de documento estén exactos: un '
                .'dígito mal es la causa más común de que devuelvan un trámite.',
        ],
        [
            'title' => 'Llévelo o envíelo a su EPS',
            'body' => 'Tiene tres formas, y todas valen igual:',
            'items' => [
                'En una oficina de atención al usuario de su EPS. Es la más segura porque le sellan la copia ahí mismo.',
                'Por el canal de PQRS de la página web de su EPS. Guarde el número que le dé el sistema.',
                'Por correo electrónico al buzón de PQRS. Pida confirmación de recibido.',
            ],
            'phrase' => 'Si va presencial, lleve las dos copias: una la entrega y la otra se la devuelven sellada. Esa copia sellada es su prueba.',
        ],
        [
            'title' => 'Pida el número de radicado',
            'body' => 'Antes de irse, pida el número de radicado y anótelo junto con la fecha y la '
                .'hora. Si fue por internet, guarde el correo de confirmación o tome una foto de la '
                .'pantalla.',
            'phrase' => 'Sin el número de radicado no hay cómo probar que lo presentó.',
        ],
        [
            'title' => 'Cuente los días y espere',
            'body' => 'La EPS tiene 15 días hábiles para responderle. Si lo único que pidió fue copia '
                .'de su historia clínica, el plazo es de 10 días hábiles. Los sábados, domingos y '
                .'festivos no cuentan. En la página siguiente está la fecha exacta si lo radica hoy.',
        ],
        [
            'title' => 'Cuando le respondan, revise tres cosas',
            'body' => 'No toda respuesta es una respuesta de verdad. Mire si cumple las tres:',
            'items' => [
                '¿Responde a todo lo que usted pidió, punto por punto?',
                '¿Llegó dentro del plazo?',
                '¿Resuelve de fondo, o solo dice que «está en trámite» y lo deja igual que antes?',
            ],
            'phrase' => 'Una respuesta que no resuelve nada se trata igual que si no hubieran respondido.',
        ],
        [
            'title' => 'Si no le responden, o le responden mal',
            'body' => 'Tiene tres caminos, y el primero no cuesta nada:',
            'items' => [
                'Presente una acción de tutela usted mismo. Es gratuita, no necesita abogado, puede presentarla por escrito o de palabra, y el juez tiene que fallar en 10 días.',
                'Vaya gratis a la Defensoría del Pueblo, a la personería de su municipio o a un consultorio jurídico de universidad. Lo ayudan sin cobrarle.',
                'Si prefiere que alguien la redacte y haga el seguimiento, en la última página de esta guía hay abogados de su ciudad.',
            ],
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si hay riesgo para la vida o la salud, no espere',
            'body' => 'Si el paciente está hospitalizado, en urgencias, o necesita el servicio en '
                .'menos de 48 horas, un derecho de petición llega tarde. Lo que corresponde es una '
                .'acción de tutela, que es gratuita y el juez debe fallarla en 10 días.',
        ],
        [
            'title' => 'Si ya pidió esto mismo y no le respondieron',
            'body' => 'Volver a pedir lo mismo no suele cambiar nada. Con la copia de la primera '
                .'petición y la constancia de que no hubo respuesta, ya tiene lo que necesita para '
                .'una tutela.',
        ],
    ],

    'glossary' => [
        ['term' => 'Radicar', 'definition' => 'Entregar un documento de forma oficial y que le den constancia. No es lo mismo que mandarlo por WhatsApp.'],
        ['term' => 'Radicado', 'definition' => 'El número que le asignan al recibir su documento. Es la prueba de que lo presentó y de qué día.'],
        ['term' => 'Días hábiles', 'definition' => 'Los días de lunes a viernes que no son festivos. Los fines de semana y festivos no se cuentan.'],
        ['term' => 'Respuesta de fondo', 'definition' => 'Una respuesta que resuelve lo que usted pidió. Decir «su caso está en estudio» no lo es.'],
        ['term' => 'Tutela', 'definition' => 'Una acción para proteger derechos fundamentales. Es gratuita, no necesita abogado y el juez debe decidir en 10 días.'],
        ['term' => 'Agencia oficiosa', 'definition' => 'Actuar por otra persona que no puede hacerlo ella misma. Hay que explicar por qué no puede.'],
    ],

    'instructions_footer' => 'HazloLegal no radica por usted.',
];
