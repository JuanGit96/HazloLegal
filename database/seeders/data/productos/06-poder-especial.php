<?php

/*
|--------------------------------------------------------------------------
| Poder especial por mensaje de datos
|--------------------------------------------------------------------------
| Existe porque los juzgados ya lo aceptan sin autenticación cuando se
| envía desde el correo del poderdante. Eso le ahorra a la gente la ida a
| la notaría, que es casi todo el costo y toda la demora del trámite.
|
| Los dos cuidados que los despachos están exigiendo —el correo del
| apoderado dentro del texto y que el envío salga del poderdante— son el
| producto: un poder que falle en eso lo devuelven.
*/

return [
    'template_heading' => 'SEÑOR JUEZ / SEÑORES',

    'template_body' => <<<'TXT'
{{ cliente.ciudad }}, {{ fecha.larga }}

Respetados señores:

Yo, {{ cliente.nombre }}, mayor de edad, identificado con {{ cliente.documento }}, domiciliado en {{ cliente.ciudad }}, actuando en nombre propio, manifiesto que confiero PODER ESPECIAL, amplio y suficiente, a {{ campo.apoderado_nombre }}, mayor de edad, identificado con cédula de ciudadanía número {{ campo.apoderado_cedula }} y portador de la tarjeta profesional de abogado número {{ campo.apoderado_tp }} del Consejo Superior de la Judicatura, con correo electrónico {{ campo.apoderado_correo }}, para que me represente en el siguiente asunto:

{{ campo.asunto }}

Ante: {{ campo.autoridad }}

FACULTADES

El apoderado queda facultado para adelantar todas las actuaciones propias del mandato judicial, en especial para presentar y contestar memoriales, aportar y solicitar pruebas, asistir a audiencias y diligencias, interponer y sustentar recursos, y recibir notificaciones.

{{ campo.facultades_especiales }}

NOTIFICACIONES

El apoderado recibirá notificaciones en {{ campo.apoderado_correo }}.

Yo recibiré notificaciones en {{ cliente.correo }}.

Este poder se remite por mensaje de datos desde la dirección de correo electrónico de mi propiedad, {{ cliente.correo }}, conforme a las normas que reconocen plena validez y fuerza probatoria a los mensajes de datos.

Atentamente,

{{ cliente.nombre }}
{{ cliente.documento }}
{{ cliente.telefono }}
TXT,

    'fields' => [
        ['key' => 'apoderado_nombre', 'label' => 'Nombre completo del abogado', 'type' => 'text', 'required' => true],
        ['key' => 'apoderado_cedula', 'label' => 'Cédula del abogado', 'type' => 'text', 'required' => true],
        ['key' => 'apoderado_tp', 'label' => 'Tarjeta profesional del abogado', 'type' => 'text', 'required' => true,
         'help' => 'Pídasela: el despacho la verifica.'],
        ['key' => 'apoderado_correo', 'label' => 'Correo electrónico del abogado', 'type' => 'text', 'required' => true,
         'help' => 'Tiene que ir dentro del texto del poder. Si falta, el despacho lo devuelve.'],
        ['key' => 'asunto', 'label' => '¿Para qué asunto le da el poder?', 'type' => 'textarea', 'required' => true,
         'help' => 'Por ejemplo: «proceso ejecutivo que adelanta el Banco X en mi contra, radicado 2026-00123».'],
        ['key' => 'autoridad', 'label' => '¿Ante qué juzgado o autoridad?', 'type' => 'text', 'required' => true],
        ['key' => 'contraparte', 'label' => 'Contraparte, si la conoce', 'type' => 'text'],
        ['key' => 'facultades_especiales', 'label' => 'Facultades especiales', 'type' => 'textarea',
         'help' => 'Conciliar, transigir, desistir, recibir y sustituir el poder. Estas hay que nombrarlas expresamente o no se entienden dadas.'],
    ],

    'filing_entity' => 'el despacho',
    'response_deadline_days' => null,

    'instructions_intro' => 'Un poder especial es el documento con el que usted autoriza a un abogado '
        .'a representarlo. Este se envía por correo electrónico y no necesita notaría, pero solo vale '
        .'si lo manda bien. Esta guía le dice exactamente cómo.',

    'instructions_attachments' => [
        'El poder en PDF, sin modificar.',
        'Copia de su documento de identidad.',
    ],

    'filing_steps' => [
        [
            'title' => 'Envíelo desde SU correo, no desde otro',
            'body' => 'Esta es la parte que más se falla. El poder vale porque sale de la dirección de '
                .'correo del que lo firma. Si lo manda desde el correo de su hijo, de su secretaria o '
                .'del abogado, el despacho lo devuelve.',
            'phrase' => 'Tiene que ser el mismo correo que aparece escrito dentro del poder.',
        ],
        [
            'title' => 'Mándelo al abogado y cópiese a usted mismo',
            'body' => 'Ponga al abogado en «Para» y su propio correo en «CC». Esa copia que le llega '
                .'a usted es su prueba de que lo envió, desde dónde y cuándo.',
        ],
        [
            'title' => 'Guarde el correo enviado',
            'body' => 'No lo borre. Guarde el correo con su fecha y su hora, y el PDF adjunto. Si '
                .'alguna vez se discute el poder, eso es lo que lo respalda.',
        ],
        [
            'title' => 'Si el despacho lo rechaza',
            'body' => 'Casi siempre es por una de dos razones, y las dos se arreglan reenviando:',
            'items' => [
                'Falta el correo del abogado dentro del texto del poder. Revise que esté escrito, no solo en el encabezado del mensaje.',
                'El envío no salió del correo del poderdante. Reenvíelo usted mismo desde su cuenta.',
            ],
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Las facultades que no se nombran, no se dan',
            'body' => 'Conciliar, transigir, desistir, recibir dinero y sustituir el poder son '
                .'facultades que tienen que estar escritas expresamente. Si su abogado va a necesitar '
                .'alguna, confirme con él antes de enviar que esté incluida.',
        ],
        [
            'title' => 'Este poder es para un asunto concreto',
            'body' => 'Un poder especial sirve para el proceso que usted describió, no para todo. Si '
                .'después necesita representación en otro asunto, hay que hacer otro poder.',
        ],
    ],

    'glossary' => [
        ['term' => 'Poderdante', 'definition' => 'Usted: quien da el poder.'],
        ['term' => 'Apoderado', 'definition' => 'El abogado que lo va a representar.'],
        ['term' => 'Mensaje de datos', 'definition' => 'Un documento enviado por medios electrónicos. La ley le reconoce el mismo valor que a uno en papel.'],
        ['term' => 'Sustituir el poder', 'definition' => 'Que su abogado pueda pasarle el caso a otro abogado. Si no lo autoriza, no puede.'],
        ['term' => 'Transigir', 'definition' => 'Llegar a un acuerdo que termina el pleito. Es una facultad que hay que dar expresamente.'],
    ],

    'instructions_footer' => 'HazloLegal no envía el poder por usted.',
];
