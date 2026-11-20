<?php

/*
|--------------------------------------------------------------------------
| Paquete de cumplimiento para pymes
|--------------------------------------------------------------------------
| No es un documento: son cuatro, y lo que se compra es dejar de estar
| expuesto. Una pyme con tienda en línea necesita política de datos, aviso
| de privacidad, formatos de autorización y términos del sitio, y casi
| ninguna los tiene.
|
| Lo que define el producto es que los cuatro documentos sean coherentes
| entre sí: las finalidades que declara la política tienen que ser las
| mismas de los formatos de autorización.
*/

return [
    'template_heading' => 'POLÍTICA DE TRATAMIENTO DE DATOS PERSONALES',

    'template_body' => <<<'TXT'
{{ campo.razon_social }}, identificada con NIT {{ campo.nit }}, con domicilio en {{ campo.domicilio }}, en cumplimiento de la Ley 1581 de 2012 y sus decretos reglamentarios, adopta la presente política de tratamiento de datos personales.

1. RESPONSABLE DEL TRATAMIENTO

{{ campo.razon_social }}. NIT {{ campo.nit }}. Dirección: {{ campo.domicilio }}. Correo de contacto para asuntos de datos personales: {{ campo.correo_datos }}. Teléfono: {{ campo.telefono_datos }}.

2. A QUIÉNES SE APLICA

Esta política se aplica al tratamiento de los datos personales de: {{ campo.titulares }}

3. QUÉ DATOS TRATAMOS

{{ campo.categorias_datos }}

{{ campo.datos_sensibles }}

4. PARA QUÉ LOS TRATAMOS

{{ campo.finalidades }}

Los datos no se usarán para finalidades distintas a las aquí declaradas sin obtener una nueva autorización del titular.

5. DERECHOS DEL TITULAR

Conocer, actualizar y rectificar sus datos. Solicitar prueba de la autorización otorgada. Ser informado sobre el uso que se ha dado a sus datos. Presentar quejas ante la Superintendencia de Industria y Comercio. Revocar la autorización y solicitar la supresión de sus datos, cuando no exista un deber legal o contractual de conservarlos. Acceder de forma gratuita a sus datos.

6. CÓMO EJERCERLOS

Las consultas y reclamos se presentan al correo {{ campo.correo_datos }}. Las consultas se atienden en un término máximo de diez (10) días hábiles y los reclamos en un término máximo de quince (15) días hábiles, en los términos de la Ley 1581 de 2012.

7. SEGURIDAD

{{ campo.razon_social }} adopta las medidas técnicas, humanas y administrativas razonables para proteger los datos y evitar su adulteración, pérdida, consulta, uso o acceso no autorizado.

8. TRANSFERENCIAS Y ENCARGADOS

{{ campo.encargados }}

9. VIGENCIA

Esta política rige desde {{ campo.fecha_vigencia }}. Los datos se conservarán mientras subsista la finalidad que justificó su recolección o mientras exista un deber legal de conservarlos.
TXT,

    'fields' => [
        ['key' => 'razon_social', 'label' => 'Razón social de la empresa', 'type' => 'text', 'required' => true],
        ['key' => 'nit', 'label' => 'NIT', 'type' => 'text', 'required' => true],
        ['key' => 'domicilio', 'label' => 'Dirección de la empresa', 'type' => 'text', 'required' => true],
        ['key' => 'representante', 'label' => 'Representante legal', 'type' => 'text', 'required' => true],
        ['key' => 'correo_datos', 'label' => 'Correo para asuntos de datos personales', 'type' => 'text', 'required' => true,
         'help' => 'Tiene que ser un correo que alguien revise: por ahí llegan las peticiones de los titulares y corren plazos.'],
        ['key' => 'telefono_datos', 'label' => 'Teléfono de contacto', 'type' => 'text', 'required' => true],
        ['key' => 'empleados', 'label' => '¿Cuántos empleados tiene?', 'type' => 'number',
         'help' => 'Junto con los activos define si tiene que inscribirse en el Registro Nacional de Bases de Datos.'],
        ['key' => 'activos', 'label' => 'Rango de activos totales', 'type' => 'text'],
        ['key' => 'actividad', 'label' => '¿A qué se dedica la empresa?', 'type' => 'textarea', 'required' => true],
        ['key' => 'titulares', 'label' => '¿De quiénes trata datos?', 'type' => 'textarea', 'required' => true,
         'help' => 'Clientes, empleados, proveedores, aspirantes, visitantes del sitio web…'],
        ['key' => 'categorias_datos', 'label' => '¿Qué datos recoge?', 'type' => 'textarea', 'required' => true,
         'help' => 'Nombre, cédula, correo, teléfono, dirección, datos de pago, historial de compras…'],
        ['key' => 'datos_sensibles', 'label' => '¿Recoge datos sensibles?', 'type' => 'textarea',
         'help' => 'Salud, huella, foto, origen étnico, datos de menores. Estos exigen autorización reforzada y se declaran aparte.'],
        ['key' => 'finalidades', 'label' => '¿Para qué usa los datos?', 'type' => 'textarea', 'required' => true,
         'help' => 'Una finalidad por línea. De aquí salen los formatos de autorización, así que sea concreto.'],
        ['key' => 'encargados', 'label' => '¿Con quién comparte datos?', 'type' => 'textarea',
         'help' => 'Pasarelas de pago, transportadoras, plataformas de correo, contadores…'],
        ['key' => 'fecha_vigencia', 'label' => '¿Desde cuándo rige?', 'type' => 'date', 'required' => true],
    ],

    'risk' => [
        ['kind' => 'question', 'level' => 'amber', 'key' => 'sensibles', 'prompt' => '¿Trata datos de salud, biométricos o de menores de edad?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'rnbd', 'prompt' => '¿La empresa tiene más de 100.000 UVT en activos o más de 50 empleados?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'internacional', 'prompt' => '¿Envía datos a servidores o empresas fuera de Colombia?'],
        ['kind' => 'question', 'level' => 'red', 'key' => 'incidente', 'prompt' => '¿Ya tuvo una filtración o pérdida de datos?'],
    ],

    'filing_entity' => null,
    'response_deadline_days' => null,

    'instructions_intro' => 'Este paquete trae cuatro documentos: la política de tratamiento de '
        .'datos, el aviso de privacidad, los formatos de autorización y los términos y condiciones. '
        .'Tenerlos no sirve de nada si no se publican donde deben. Esta guía le dice dónde va cada '
        .'uno y qué hacer después.',

    'instructions_attachments' => [
        'Los cuatro documentos en PDF y en texto editable.',
        'El acta o constancia de adopción de la política, firmada por el representante legal.',
    ],

    'filing_steps' => [
        [
            'title' => 'Adopte la política formalmente',
            'body' => 'No basta con tener el archivo. El representante legal debe aprobarla, con '
                .'fecha. Si hay junta o asamblea, déjelo en un acta. Esa fecha es la que aparece en '
                .'la política y la que le van a pedir si alguna vez se la revisan.',
        ],
        [
            'title' => 'Publique cada documento donde corresponde',
            'body' => 'Cada uno tiene su sitio y no son intercambiables:',
            'items' => [
                'La política completa: en el pie de su sitio web, con un enlace visible desde todas las páginas.',
                'El aviso de privacidad, versión corta: junto a cada formulario donde pida datos.',
                'Los formatos de autorización: en el momento en que recoge los datos, no después.',
                'Los términos y condiciones: en el pie del sitio y antes de confirmar cualquier compra.',
            ],
        ],
        [
            'title' => 'Ponga la casilla donde pide datos',
            'body' => 'En cada formulario —contacto, registro, compra, hoja de vida— tiene que '
                .'haber una casilla que el usuario marque, con el enlace a la política. No puede '
                .'venir marcada por defecto.',
            'phrase' => 'Una autorización que el usuario no marcó activamente no es una autorización.',
        ],
        [
            'title' => 'Guarde la prueba de cada autorización',
            'body' => 'Tiene que poder demostrar quién autorizó, qué autorizó y cuándo. Guarde la '
                .'fecha, la hora y la versión de la política que estaba vigente en ese momento. Si '
                .'es en papel, archive el formato firmado.',
            'phrase' => 'Si no puede probar la autorización, ante la Superintendencia es como si no existiera.',
        ],
        [
            'title' => 'Asigne a alguien el correo de datos personales',
            'body' => 'Por ese correo llegan las consultas y los reclamos de los titulares, y '
                .'corren plazos: diez días hábiles para una consulta, quince para un reclamo. Un '
                .'buzón que nadie revisa es un incumplimiento esperando a pasar.',
        ],
        [
            'title' => 'Revise si le toca el Registro Nacional de Bases de Datos',
            'body' => 'El RNBD es un registro ante la Superintendencia de Industria y Comercio. '
                .'Aplica según el tamaño de la empresa, medido por activos y número de empleados. '
                .'Si su empresa está cerca del umbral, confírmelo antes de darlo por descartado.',
        ],
        [
            'title' => 'Vuelva a mirarlo cuando cambie algo',
            'body' => 'Si empieza a recoger datos nuevos, a usarlos para algo distinto o a '
                .'compartirlos con un proveedor nuevo, hay que actualizar la política y, muchas '
                .'veces, pedir una autorización nueva.',
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si ya tuvo una filtración de datos',
            'body' => 'Un incidente de seguridad tiene que reportarse a la Superintendencia de '
                .'Industria y Comercio, y hay que informar a los titulares afectados. Adoptar la '
                .'política ahora es correcto, pero no reemplaza el reporte. Busque asesoría cuanto '
                .'antes: los plazos corren.',
        ],
        [
            'title' => 'Los datos sensibles tienen reglas aparte',
            'body' => 'Salud, huella, fotografía, origen étnico y datos de menores exigen una '
                .'autorización reforzada y explícita, y el titular puede negarse sin que eso le '
                .'impida recibir el servicio. Si su empresa los trata, revise los formatos con un '
                .'abogado antes de usarlos.',
        ],
    ],

    'glossary' => [
        ['term' => 'Titular', 'definition' => 'La persona dueña de los datos: su cliente, su empleado, quien llena un formulario.'],
        ['term' => 'Responsable', 'definition' => 'Quien decide para qué se usan los datos. En este caso, su empresa.'],
        ['term' => 'Encargado', 'definition' => 'Quien trata los datos por cuenta del responsable: una pasarela de pagos, un proveedor de correo.'],
        ['term' => 'Dato sensible', 'definition' => 'El que puede generar discriminación: salud, origen étnico, biometría, orientación sexual, datos de menores.'],
        ['term' => 'RNBD', 'definition' => 'Registro Nacional de Bases de Datos. Un registro ante la Superintendencia que aplica según el tamaño de la empresa.'],
        ['term' => 'Aviso de privacidad', 'definition' => 'La versión corta de la política, la que se muestra junto al formulario cuando se recogen los datos.'],
    ],

    'instructions_footer' => 'HazloLegal no publica ni implementa los documentos por usted.',
];
