<?php

/*
|--------------------------------------------------------------------------
| Reclamación por garantía legal
|--------------------------------------------------------------------------
| La reclamación directa es el paso previo obligado antes de demandar ante
| la SIC. Quien va directo a la Superintendencia sin haber reclamado pierde
| tiempo. Por eso el producto existe aunque el escrito sea sencillo.
|
| El consumidor casi siempre pide «que me devuelvan la plata», pero la ley
| ofrece tres caminos y conviene que escoja sabiendo cuáles tiene.
*/

return [
    'clauses' => [
        [
            'key' => 'reparacion',
            'label' => 'Que lo reparen sin costo',
            'body' => 'Reparar sin costo el {producto}, dejándolo en condiciones de servir para lo que fue adquirido, y devolverlo en un plazo razonable informándome la fecha de entrega.',
            'legal_basis' => 'Ley 1480 de 2011, Estatuto del Consumidor, en lo relativo a la garantía legal.',
            'variables' => [
                ['key' => 'producto', 'label' => '¿Qué producto o servicio?', 'type' => 'text'],
            ],
        ],
        [
            'key' => 'cambio',
            'label' => 'Que me lo cambien por uno igual',
            'body' => 'Cambiar el {producto} por otro de iguales características, nuevo y en perfecto estado de funcionamiento.',
            'legal_basis' => 'Ley 1480 de 2011, Estatuto del Consumidor, en lo relativo a la garantía legal.',
            'variables' => [
                ['key' => 'producto', 'label' => '¿Qué producto?', 'type' => 'text'],
            ],
        ],
        [
            'key' => 'devolucion',
            'label' => 'Que me devuelvan el dinero',
            'body' => 'Devolver la suma de {valor} que pagué por el {producto} el {fecha de compra}, por el mismo medio en que se hizo el pago.',
            'legal_basis' => 'Ley 1480 de 2011, Estatuto del Consumidor, en lo relativo a la garantía legal.',
            'variables' => [
                ['key' => 'valor', 'label' => '¿Cuánto pagó?', 'type' => 'number'],
                ['key' => 'producto', 'label' => '¿Por qué producto o servicio?', 'type' => 'text'],
                ['key' => 'fecha de compra', 'label' => '¿Cuándo lo compró?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'retracto',
            'label' => 'Quiero retractarme de la compra (compré a distancia)',
            'body' => 'Aceptar el retracto de la compra del {producto} realizada el {fecha de compra} por medios no presenciales, y devolver la totalidad de lo pagado dentro del término legal.',
            'legal_basis' => 'Ley 1480 de 2011, en lo relativo al derecho de retracto en ventas no presenciales.',
            'variables' => [
                ['key' => 'producto', 'label' => '¿Qué compró?', 'type' => 'text'],
                ['key' => 'fecha de compra', 'label' => '¿Qué día lo compró?', 'type' => 'date'],
            ],
        ],
        [
            'key' => 'prestacion',
            'label' => 'Que presten el servicio que pagué y no recibí',
            'body' => 'Prestar el servicio de {servicio} contratado el {fecha de compra}, o devolver lo pagado si ya no es posible prestarlo en condiciones útiles para mí.',
            'legal_basis' => 'Ley 1480 de 2011, Estatuto del Consumidor, en lo relativo a la garantía legal de los servicios.',
            'variables' => [
                ['key' => 'servicio', 'label' => '¿Qué servicio?', 'type' => 'text'],
                ['key' => 'fecha de compra', 'label' => '¿Cuándo lo contrató?', 'type' => 'date'],
            ],
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

    'common_basis' => 'Ley 1480 de 2011, Estatuto del Consumidor. El productor y el proveedor responden '
        .'solidariamente frente al consumidor por la calidad e idoneidad de los bienes y servicios que '
        .'ponen en el mercado.',

    'closing' => 'Solicito que la respuesta se me remita por escrito al correo señalado, dentro del '
        .'término legal, y que se me entregue constancia de la presente reclamación.',

    'risk' => [
        ['kind' => 'question', 'level' => 'red', 'key' => 'dano', 'prompt' => '¿El problema le causó un daño a su salud, a otra persona o a otros bienes?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'mas_de_un_ano', 'prompt' => '¿La compra fue hace más de un año?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'valor_alto', 'prompt' => '¿El valor supera $10.000.000?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'inmueble_vehiculo', 'prompt' => '¿Es un inmueble o un vehículo?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'ya_negaron', 'prompt' => '¿Ya reclamó antes y le respondieron negando?'],
        ['kind' => 'question', 'level' => 'amber', 'key' => 'persona_natural', 'prompt' => '¿Le compró a una persona natural que no es comerciante, por ejemplo por una red social?'],

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'red', 'prompt' => $p], [
            'me quemé', 'intoxicación', 'se incendió', 'lesión', 'accidente',
        ]),

        ...array_map(fn (string $p) => ['kind' => 'keyword', 'level' => 'amber', 'prompt' => $p], [
            'hace dos años', 'ya me respondieron', 'me negaron', 'Mercado Libre',
            'por Instagram', 'le compré a una persona', 'estafa',
        ]),
    ],

    'fields' => [
        ['key' => 'establecimiento', 'label' => '¿A quién le reclama?', 'type' => 'text', 'required' => true,
         'help' => 'Nombre del almacén, la tienda o la empresa.'],
        ['key' => 'nit', 'label' => 'NIT, si lo conoce', 'type' => 'text'],
        ['key' => 'canal', 'label' => 'Dirección o canal de atención', 'type' => 'text',
         'help' => 'Dónde queda, o por dónde atienden: correo, página, teléfono.'],
        ['key' => 'factura', 'label' => 'Número de factura o de pedido', 'type' => 'text'],
    ],

    'filing_entity' => 'el vendedor o el fabricante',
    'response_deadline_days' => 15,

    'instructions_intro' => 'Una reclamación directa es el reclamo formal al vendedor antes de acudir '
        .'a las autoridades. Es obligatoria: si va directo a la Superintendencia sin haber reclamado, '
        .'le van a decir que reclame primero. Esta guía le dice cómo presentarla.',

    'instructions_attachments' => [
        'La reclamación impresa y firmada (dos copias).',
        'La factura, el recibo o el comprobante de la compra.',
        'Fotos del defecto, si es un producto.',
        'Capturas de las conversaciones previas con el vendedor, si las hubo.',
        'La orden de servicio, si contrató un servicio.',
    ],

    'filing_steps' => [
        [
            'title' => 'Escoja por dónde presentarla',
            'body' => 'Cualquiera de estas tres sirve. Elija la que le deje constancia más fácil:',
            'items' => [
                'El canal de PQR del establecimiento: su página web o su formulario de contacto.',
                'El correo de servicio al cliente. Pida confirmación de recibido.',
                'En el local, entregando una copia y pidiendo que le sellen la otra.',
            ],
        ],
        [
            'title' => 'Pida constancia con fecha',
            'body' => 'Sea cual sea el canal, no se vaya sin una prueba de que reclamó y de qué día: '
                .'la copia sellada, el número de radicado o el correo de confirmación.',
            'phrase' => 'Guarde la constancia. Es lo que después prueba que reclamó a tiempo.',
        ],
        [
            'title' => 'Dirija la reclamación a los dos, si puede',
            'body' => 'El vendedor y el fabricante responden por igual ante usted. Si conoce a los '
                .'dos, presente la reclamación a ambos: duplica sus posibilidades de que alguien '
                .'responda rápido.',
        ],
        [
            'title' => 'Espere la respuesta',
            'body' => 'En la página siguiente está la fecha de vencimiento calculada. Si le responden '
                .'ofreciendo algo, evalúe si de verdad resuelve: una reparación que deja el producto '
                .'igual de malo no es una solución.',
        ],
        [
            'title' => 'Si no responden o le niegan el reclamo',
            'body' => 'El siguiente paso es la Superintendencia de Industria y Comercio, y esto es lo '
                .'importante:',
            'items' => [
                'La demanda ante la SIC es gratuita.',
                'No necesita abogado para cuantías menores.',
                'Se presenta por la página de la SIC o en sus oficinas.',
                'Lleve la copia de esta reclamación y la constancia de que la presentó: es justo lo que le van a pedir.',
            ],
            'phrase' => 'Esa constancia es la razón por la que valía la pena reclamar primero.',
        ],
    ],

    'instructions_warnings' => [
        [
            'title' => 'Si el producto le causó un daño, esto se queda corto',
            'body' => 'Si hubo una quemadura, una intoxicación, un incendio o cualquier lesión a una '
                .'persona o a otros bienes, ya no se trata solo de la garantía sino de la '
                .'responsabilidad por producto defectuoso, que tiene otro camino y otros montos. '
                .'Busque orientación antes de reclamar solo la devolución.',
        ],
        [
            'title' => 'Si cree que fue víctima de un delito',
            'body' => 'Una reclamación de consumo no es una denuncia. Si cree que lo engañaron de '
                .'forma deliberada, puede presentar una denuncia en la Fiscalía o en una URI. Son '
                .'caminos distintos y puede seguir los dos.',
        ],
    ],

    'glossary' => [
        ['term' => 'Garantía legal', 'definition' => 'La que la ley da siempre, aunque el vendedor no la mencione. No es la garantía extendida que le venden aparte.'],
        ['term' => 'Retracto', 'definition' => 'Arrepentirse de una compra hecha por internet o por teléfono, dentro de un plazo corto, sin tener que dar explicaciones ni que el producto falle.'],
        ['term' => 'Reclamación directa', 'definition' => 'El reclamo formal al vendedor. Es el paso obligado antes de acudir a la Superintendencia.'],
        ['term' => 'SIC', 'definition' => 'Superintendencia de Industria y Comercio. Es la autoridad que resuelve los pleitos de consumo.'],
        ['term' => 'Solidariamente', 'definition' => 'Que puede reclamarle a cualquiera de los dos —vendedor o fabricante— y cualquiera tiene que responderle.'],
    ],

    'instructions_footer' => 'HazloLegal no presenta la reclamación por usted.',
];
