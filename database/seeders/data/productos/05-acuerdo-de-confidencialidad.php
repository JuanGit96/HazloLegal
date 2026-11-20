<?php

/*
|--------------------------------------------------------------------------
| Acuerdo de confidencialidad (NDA)
|--------------------------------------------------------------------------
| El trámite más sencillo del catálogo y el que más se firma sin leer. Lo
| que lo hace útil no es la cláusula bonita: es que defina con precisión
| qué información se protege y por cuánto tiempo. Un NDA que dice «toda la
| información» no protege nada, porque no se puede probar qué se entregó.
*/

return [
    'template_heading' => 'ACUERDO DE CONFIDENCIALIDAD',

    'template_body' => <<<'TXT'
Entre los suscritos, {{ cliente.nombre }}, identificado con {{ cliente.documento }}, domiciliado en {{ cliente.ciudad }} (en adelante, la Parte Reveladora), y {{ campo.contraparte_nombre }}, identificado con {{ campo.contraparte_documento }}, domiciliado en {{ campo.contraparte_ciudad }} (en adelante, la Parte Receptora), se celebra el presente acuerdo de confidencialidad, que se regirá por las siguientes cláusulas:

PRIMERA. OBJETO. Las partes van a intercambiar información con ocasión de {{ campo.contexto }}. Este acuerdo regula el uso y la protección de esa información.

SEGUNDA. INFORMACIÓN CONFIDENCIAL. Se entiende por información confidencial la siguiente: {{ campo.que_se_protege }}. También la que se entregue marcada como confidencial, en cualquier soporte.

No es confidencial la información que ya fuera pública, la que la Parte Receptora ya conociera antes y pueda probarlo, la que reciba legítimamente de un tercero sin deber de reserva, ni la que deba revelarse por orden de autoridad competente, caso en el cual se avisará a la otra parte.

TERCERA. OBLIGACIONES DE LA PARTE RECEPTORA. Guardar la información con la misma diligencia con que protege la propia, usarla únicamente para el objeto de este acuerdo, no divulgarla a terceros sin autorización escrita, limitar el acceso a quienes necesiten conocerla, y devolverla o destruirla cuando la otra parte lo pida.

CUARTA. {{ campo.modalidad_clausula }}

QUINTA. VIGENCIA. Este acuerdo rige desde {{ campo.fecha_inicio }} y estará vigente hasta {{ campo.fecha_fin }}. El deber de confidencialidad se mantendrá por {{ campo.duracion_deber }} contados desde la terminación del acuerdo.

SEXTA. INCUMPLIMIENTO. {{ campo.clausula_penal }}

SÉPTIMA. LEY Y JURISDICCIÓN. Este acuerdo se rige por la ley colombiana. Cualquier controversia se someterá a los jueces de {{ campo.jurisdiccion }}.

En constancia se firma en {{ cliente.ciudad }}, el {{ fecha.larga }}.


_______________________________        _______________________________
{{ cliente.nombre }}                   {{ campo.contraparte_nombre }}
{{ cliente.documento }}                {{ campo.contraparte_documento }}
TXT,

    'fields' => [
        ['key' => 'contraparte_nombre', 'label' => 'Nombre o razón social de la otra parte', 'type' => 'text', 'required' => true],
        ['key' => 'contraparte_documento', 'label' => 'Documento o NIT de la otra parte', 'type' => 'text', 'required' => true],
        ['key' => 'contraparte_ciudad', 'label' => 'Ciudad de la otra parte', 'type' => 'text', 'required' => true],
        ['key' => 'contexto', 'label' => '¿Para qué van a compartir información?', 'type' => 'textarea', 'required' => true,
         'help' => 'Por ejemplo: «evaluar una posible inversión» o «desarrollar una aplicación móvil».'],
        ['key' => 'que_se_protege', 'label' => '¿Qué información concreta se protege?', 'type' => 'textarea', 'required' => true,
         'help' => 'Sea específico. «Toda la información» no protege nada porque después no se puede probar qué se entregó.'],
        ['key' => 'modalidad_clausula', 'label' => 'Modalidad', 'type' => 'textarea', 'required' => true,
         'help' => 'Unilateral (solo una parte revela), recíproco (las dos) o con un tercero.'],
        ['key' => 'fecha_inicio', 'label' => '¿Desde cuándo rige?', 'type' => 'date', 'required' => true],
        ['key' => 'fecha_fin', 'label' => '¿Hasta cuándo rige el acuerdo?', 'type' => 'date', 'required' => true],
        ['key' => 'duracion_deber', 'label' => '¿Cuánto dura el deber después de terminado?', 'type' => 'text', 'required' => true,
         'help' => 'Por ejemplo: «dos (2) años». Un plazo indefinido es difícil de hacer valer.'],
        ['key' => 'clausula_penal', 'label' => 'Consecuencia del incumplimiento', 'type' => 'textarea',
         'help' => 'Si pactan una suma, escríbala. Si no, el incumplimiento se reclama por los daños que se puedan probar.'],
        ['key' => 'jurisdiccion', 'label' => '¿Jueces de qué ciudad?', 'type' => 'text', 'required' => true],
    ],

    'risk' => [
        ['kind' => 'question', 'level' => 'amber', 'key' => 'indefinido', 'prompt' => '¿Quiere que el deber de confidencialidad sea indefinido?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'penal_alta', 'prompt' => '¿La cláusula penal supera el valor del negocio?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'ya_incumplieron', 'prompt' => '¿Ya cree que alguien usó su información sin permiso?'],
    ],

    'filing_entity' => null,
    'response_deadline_days' => null,

    'instructions_intro' => 'Un acuerdo de confidencialidad protege la información que usted va a '
        .'compartir. Pero protege poco si después nadie puede probar qué entregó ni cuándo. Esta guía '
        .'le dice cómo firmarlo y, sobre todo, qué hacer desde el día siguiente.',

    'instructions_attachments' => [
        'El acuerdo impreso, tantos ejemplares como partes haya.',
        'Copia del documento de identidad de cada firmante.',
        'Si alguna parte es una empresa: el certificado de existencia y representación legal.',
    ],

    'filing_steps' => [
        [
            'title' => 'Antes de firmar, verifique tres cosas',
            'body' => 'Son las que más se pasan por alto:',
            'items' => [
                'Que los nombres y documentos coincidan exactamente con las cédulas.',
                'Si firma una empresa, que quien firma sea el representante legal y aparezca así en el certificado de existencia.',
                'Que lo que dice «qué información se protege» describa de verdad lo que usted va a entregar.',
            ],
        ],
        [
            'title' => 'Cuántos ejemplares y quién firma',
            'body' => 'Un ejemplar por cada parte, todos firmados. Cada hoja va rubricada con las '
                .'iniciales, para que nadie pueda cambiar una página después.',
        ],
        [
            'title' => 'Lo que tiene que hacer desde ya',
            'body' => 'Esta es la parte que decide si el acuerdo sirve de algo:',
            'items' => [
                'Marque la información como confidencial cuando la envíe. Un sello, una marca de agua o una línea en el correo bastan.',
                'Use canales controlados: un correo, una carpeta compartida. No por chats personales.',
                'Deje registro de qué entregó y cuándo. Una lista con fechas es suficiente.',
            ],
            'phrase' => 'Un acuerdo de confidencialidad protege poco si después nadie puede probar qué información se entregó ni cuándo.',
        ],
        [
            'title' => 'Sus fechas',
            'body' => 'Anote dos: cuándo vence el acuerdo y hasta cuándo dura el deber de '
                .'confidencialidad después de terminado. Son distintas, y la segunda es la que '
                .'importa cuando el negocio ya terminó.',
        ],
        [
            'title' => 'Si cree que lo incumplieron',
            'body' => 'En ese orden:',
            'items' => [
                'Reúna pruebas antes de decir nada: capturas, correos, fechas, testigos.',
                'Requiera por escrito a la otra parte, dejando constancia del envío.',
                'De ahí en adelante conviene un abogado. Reclamar mal un incumplimiento puede dejarle sin pruebas para después.',
            ],
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si ya cree que usaron su información',
            'body' => 'Un acuerdo que se firma después de la filtración no arregla lo que ya pasó. '
                .'Si sospecha que alguien ya usó su información, lo primero es reunir pruebas y '
                .'hablar con un abogado, no firmar un documento.',
        ],
    ],

    'glossary' => [
        ['term' => 'Parte Reveladora', 'definition' => 'Quien entrega la información.'],
        ['term' => 'Parte Receptora', 'definition' => 'Quien la recibe y se compromete a guardarla.'],
        ['term' => 'Unilateral / recíproco', 'definition' => 'Unilateral: solo una parte revela. Recíproco: las dos se entregan información.'],
        ['term' => 'Cláusula penal', 'definition' => 'Una suma fijada de antemano que se paga si se incumple, sin tener que probar el daño.'],
        ['term' => 'Rubricar', 'definition' => 'Poner las iniciales en cada hoja, para que no se puedan cambiar páginas después.'],
    ],

    'instructions_footer' => 'HazloLegal no firma ni custodia el acuerdo por usted.',
];
