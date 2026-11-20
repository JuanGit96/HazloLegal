<?php

/*
|--------------------------------------------------------------------------
| Contrato de arrendamiento de vivienda urbana
|--------------------------------------------------------------------------
| El producto insignia: el de más búsqueda, el que más gente necesita cada
| año y el que tiene más regulación encima. También es donde un error de
| plantilla le cuesta plata real al usuario.
|
| La Ley 820 de 2003 no deja negociar varias cosas. El motor las aplica
| aunque el usuario pida otra cosa, y el instructivo le explica por qué.
*/

return [
    'template_heading' => 'CONTRATO DE ARRENDAMIENTO DE VIVIENDA URBANA',

    'template_body' => <<<'TXT'
Entre {{ cliente.nombre }}, identificado con {{ cliente.documento }}, quien obra como ARRENDADOR, y {{ campo.arrendatario_nombre }}, identificado con {{ campo.arrendatario_documento }}, quien obra como ARRENDATARIO, se celebra el presente contrato de arrendamiento de vivienda urbana, regido por la Ley 820 de 2003 y por las siguientes cláusulas:

PRIMERA. OBJETO. El ARRENDADOR entrega al ARRENDATARIO, a título de arrendamiento, el inmueble ubicado en {{ campo.direccion_inmueble }}, en la ciudad de {{ campo.ciudad_inmueble }}, destinado exclusivamente a vivienda. {{ campo.anexidades }}

SEGUNDA. CANON. El canon mensual es de {{ campo.canon }}, que el ARRENDATARIO pagará por mensualidades anticipadas, dentro de los primeros {{ campo.dia_pago }} días de cada mes, mediante {{ campo.medio_pago }}.

TERCERA. REAJUSTE ANUAL. Al vencimiento de cada año de ejecución del contrato, el canon podrá reajustarse en un porcentaje que no exceda el cien por ciento (100%) del incremento que haya tenido el índice de precios al consumidor en el año calendario inmediatamente anterior, conforme al artículo 20 de la Ley 820 de 2003.

CUARTA. TÉRMINO. El contrato tendrá una duración de {{ campo.termino_meses }} meses, contados desde {{ campo.fecha_inicio }}. Se entenderá prorrogado automáticamente por términos iguales al inicialmente pactado, siempre que las partes cumplan sus obligaciones y el ARRENDATARIO se allane a los reajustes.

QUINTA. TERMINACIÓN POR EL ARRENDADOR EN LA PRÓRROGA. Si el ARRENDADOR desea terminar el contrato a la expiración del plazo inicial o de sus prórrogas, deberá avisar al ARRENDATARIO por escrito, a través del servicio postal autorizado, con una antelación no menor a tres (3) meses, e indemnizarlo con una suma equivalente a tres (3) meses de arrendamiento, salvo en las causales especiales previstas en la ley.

SEXTA. SERVICIOS PÚBLICOS. {{ campo.servicios_publicos }} Al terminar el contrato, el ARRENDATARIO entregará los recibos cancelados o el paz y salvo correspondiente.

SÉPTIMA. GARANTÍA. {{ campo.garantia }}

Las partes dejan constancia de que, conforme al artículo 16 de la Ley 820 de 2003, no se exigen depósitos en dinero ni cauciones reales para garantizar el cumplimiento de este contrato.

OCTAVA. OBLIGACIONES DEL ARRENDATARIO. Pagar oportunamente el canon y lo que le corresponda de servicios y administración; cuidar el inmueble y devolverlo en el mismo estado en que lo recibió, salvo el deterioro natural por el uso legítimo; realizar las reparaciones locativas a su cargo; y permitir las visitas de inspección acordadas previamente.

NOVENA. REGLAS DE USO. {{ campo.reglas_convivencia }}

DÉCIMA. CESIÓN Y SUBARRIENDO. El ARRENDATARIO no podrá ceder el contrato ni subarrendar, total o parcialmente, sin autorización previa y escrita del ARRENDADOR.

UNDÉCIMA. ENTREGA. El inmueble se entrega conforme al inventario que las partes suscriben como anexo de este contrato y que hace parte integral del mismo.

DUODÉCIMA. NOTIFICACIONES. Las partes recibirán notificaciones en las direcciones y correos señalados al final de este documento.

En constancia se firma en {{ campo.ciudad_inmueble }}, el {{ fecha.larga }}.


_______________________________        _______________________________
ARRENDADOR                             ARRENDATARIO
{{ cliente.nombre }}                   {{ campo.arrendatario_nombre }}
{{ cliente.documento }}                {{ campo.arrendatario_documento }}
{{ cliente.correo }}                   {{ campo.arrendatario_correo }}
TXT,

    'fields' => [
        ['key' => 'arrendatario_nombre', 'label' => 'Nombre completo del arrendatario', 'type' => 'text', 'required' => true],
        ['key' => 'arrendatario_documento', 'label' => 'Documento del arrendatario', 'type' => 'text', 'required' => true],
        ['key' => 'arrendatario_correo', 'label' => 'Correo del arrendatario', 'type' => 'text', 'required' => true],
        ['key' => 'direccion_inmueble', 'label' => 'Dirección completa del inmueble', 'type' => 'text', 'required' => true],
        ['key' => 'ciudad_inmueble', 'label' => 'Ciudad del inmueble', 'type' => 'text', 'required' => true],
        ['key' => 'anexidades', 'label' => '¿Incluye parqueadero, depósito u otros?', 'type' => 'textarea',
         'help' => 'Escríbalo con el número: «Incluye el parqueadero número 14 y el depósito número 7.»'],
        ['key' => 'canon', 'label' => 'Canon mensual', 'type' => 'text', 'required' => true],
        ['key' => 'avaluo_catastral', 'label' => 'Avalúo catastral del inmueble', 'type' => 'number',
         'help' => 'Si lo pone, comprobamos que el canon no pase del tope que fija la ley.'],
        ['key' => 'dia_pago', 'label' => '¿Dentro de cuántos días del mes se paga?', 'type' => 'number', 'required' => true],
        ['key' => 'medio_pago', 'label' => '¿Cómo se paga?', 'type' => 'text', 'required' => true,
         'help' => 'Transferencia a tal cuenta, consignación, efectivo con recibo…'],
        ['key' => 'termino_meses', 'label' => 'Duración en meses', 'type' => 'number', 'required' => true,
         'help' => 'Si no pactan nada, la ley entiende doce meses.'],
        ['key' => 'fecha_inicio', 'label' => 'Fecha de inicio', 'type' => 'date', 'required' => true],
        ['key' => 'servicios_publicos', 'label' => '¿Quién paga los servicios y la administración?', 'type' => 'textarea', 'required' => true],
        ['key' => 'garantia', 'label' => 'Garantía pactada', 'type' => 'textarea',
         'help' => 'Deudor solidario, póliza de arrendamiento o afianzamiento. Los depósitos en dinero están prohibidos.'],
        ['key' => 'reglas_convivencia', 'label' => 'Reglas de uso', 'type' => 'textarea',
         'help' => '¿Mascotas? ¿Cuántas personas pueden vivir ahí? ¿Quién asume las reparaciones locativas?'],
    ],

    'risk' => [
        ['kind' => 'question', 'level' => 'red', 'key' => 'local_comercial', 'prompt' => '¿El inmueble se va a usar para un negocio y no para vivienda?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'habitacion', 'prompt' => '¿Es una habitación dentro de una vivienda, o vivienda compartida?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'deposito', 'prompt' => '¿Piensa pedir un depósito en dinero como garantía?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'sin_garantia', 'prompt' => '¿Va a arrendar sin ninguna garantía?'],

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'amber', 'prompt' => $p], [
            'depósito', 'dos meses de depósito', 'local', 'negocio', 'habitación',
        ]),
    ],

    'filing_entity' => null,
    'response_deadline_days' => null,

    'instructions_intro' => 'Este es su contrato de arrendamiento y el inventario de entrega. Esta '
        .'guía le dice qué revisar antes de firmar, cómo hacer la entrega del inmueble y cuáles son '
        .'las fechas que no puede dejar pasar.',

    'instructions_attachments' => [
        'El contrato impreso: un ejemplar para cada parte y uno para el deudor solidario, si lo hay.',
        'El inventario de entrega, firmado por las dos partes.',
        'Copia del documento de identidad de cada firmante.',
        'El certificado de tradición del inmueble, si el arrendador lo tiene.',
        'La póliza o el documento de la garantía pactada.',
    ],

    'filing_steps' => [
        [
            'title' => 'Antes de firmar, revise',
            'body' => 'Tres cosas, y vale la pena hacerlo con calma:',
            'items' => [
                'Que los nombres y los documentos coincidan con las cédulas, letra por letra.',
                'Que la dirección del inmueble esté completa: torre, apartamento, interior, lo que aplique.',
                'Que el canon y el día de pago sean exactamente los que acordaron de palabra.',
            ],
        ],
        [
            'title' => 'Cuántos ejemplares y quién firma',
            'body' => 'Un ejemplar por cada parte y uno más para el deudor solidario si lo hay. '
                .'Firman arrendador, arrendatario y deudor solidario. Cada página va rubricada con '
                .'las iniciales.',
        ],
        [
            'title' => 'El día de la entrega',
            'body' => 'Esto es lo que después evita la discusión:',
            'items' => [
                'Diligencie el inventario espacio por espacio, sin saltarse ninguno.',
                'Tome fotos de todo, incluidos los daños que ya existan.',
                'Anote la lectura de los contadores de agua, luz y gas.',
                'Cuente las llaves y escriba cuántas se entregaron.',
            ],
            'phrase' => 'El inventario es la única prueba de cómo se entregó el inmueble. Sin él, la discusión al final es palabra contra palabra.',
        ],
        [
            'title' => 'Anote sus tres fechas clave',
            'body' => 'En la página siguiente las tiene calculadas:',
            'items' => [
                'Cuándo vence el contrato.',
                'El último día para enviar el preaviso de terminación, que es tres meses antes del vencimiento.',
                'La fecha del incremento anual.',
            ],
            'phrase' => 'Si el arrendador quiere terminar el contrato en la prórroga, el preaviso debe enviarse por servicio postal autorizado con no menos de tres meses de antelación, e implica una indemnización de tres cánones. Fuera de ese plazo, el contrato se prorroga solo.',
        ],
        [
            'title' => 'Lo que no se puede pactar, aunque lo acuerden',
            'body' => 'La Ley 820 de 2003 lo prohíbe, y un acuerdo en contrario no vale:',
            'items' => [
                'Depósitos en dinero para garantizar el cumplimiento. Están prohibidos.',
                'Cauciones reales sobre el inmueble o sobre otros bienes.',
                'Un canon que supere el 1% del valor comercial del inmueble, y ese valor no puede pasar del doble del avalúo catastral.',
                'Un incremento anual superior al IPC del año anterior.',
            ],
            'phrase' => 'Si alguien le pide dos meses de depósito, puede negarse: la ley no lo permite. Lo que sí se admite es deudor solidario, póliza o afianzamiento.',
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si el inmueble es para un negocio, este contrato no sirve',
            'body' => 'El arrendamiento de local comercial se rige por el Código de Comercio, no por '
                .'la Ley 820, y tiene reglas muy distintas sobre renovación y desalojo. Usar este '
                .'contrato para un local puede costarle el derecho de renovación.',
        ],
        [
            'title' => 'Si es una habitación o vivienda compartida, tampoco',
            'body' => 'El arrendamiento de habitación dentro de una vivienda tiene reglas propias. '
                .'Este contrato está hecho para el arriendo de una vivienda completa.',
        ],
    ],

    'glossary' => [
        ['term' => 'Canon', 'definition' => 'El valor del arriendo mensual.'],
        ['term' => 'Prórroga automática', 'definition' => 'Que el contrato se renueva solo al vencerse, si nadie avisa lo contrario a tiempo.'],
        ['term' => 'Preaviso', 'definition' => 'El aviso escrito de que no se va a renovar. Tiene plazo y forma: tres meses antes y por servicio postal autorizado.'],
        ['term' => 'Deudor solidario', 'definition' => 'Alguien que responde por las deudas del arrendatario como si fueran suyas.'],
        ['term' => 'Reparaciones locativas', 'definition' => 'Los arreglos pequeños del uso diario: una chapa, un empaque, una pintura. Las asume el arrendatario.'],
        ['term' => 'Avalúo catastral', 'definition' => 'El valor que le asigna el municipio al inmueble. Aparece en el impuesto predial.'],
        ['term' => 'IPC', 'definition' => 'Índice de precios al consumidor. Es el tope legal del incremento anual del arriendo.'],
    ],

    'instructions_footer' => 'HazloLegal no interviene en la relación entre arrendador y arrendatario.',
];
