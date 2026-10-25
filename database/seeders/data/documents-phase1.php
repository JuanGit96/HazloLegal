<?php

declare(strict_types=1);

/*
| Fase 1 — Servicios automatizados.
| Plantilla maestra rígida + inyección de variables. El formato lo escriben y
| mantienen los abogados del equipo; el documento NO se revisa caso por caso.
*/

return [

    [
        'name' => 'Acuerdo de Confidencialidad (NDA)',
        'short_name' => 'Acuerdo NDA',
        'slug' => 'acuerdo-de-confidencialidad-nda',
        'practice_area' => 'empresarial',
        'automation_phase' => 'automated',
        'emoji' => '🔒',
        'tagline' => 'Protege tu idea, tu código o tu modelo de negocio antes de mostrarlo.',
        'summary' => 'Acuerdo de confidencialidad bilateral o unilateral bajo ley colombiana, con cláusula penal, plazo de reserva y definición precisa de qué información queda protegida.',
        'legal_basis' => 'Código Civil (arts. 1602 y 1613) y Decisión Andina 486 de 2000',
        'supports_signature' => true,
        'is_featured' => true,
        'sort_order' => 10,
        'what_you_get' => [
            'NDA en PDF y Word, listo para firmar',
            'Cláusula penal con monto definido para que disuada de verdad',
            'Definición cerrada de «información confidencial» y de las excepciones',
            'Plazo de reserva que sigue corriendo después de terminar la relación',
            'Opción de firma electrónica con OTP para ambas partes',
        ],
        'required_info' => [
            'Nombre completo o razón social de ambas partes',
            'Cédula o NIT de cada parte',
            'Ciudad donde se firma',
            'Propósito del negocio o la conversación que se va a proteger',
            'Plazo de confidencialidad deseado (por defecto, 5 años)',
        ],
        'use_cases' => [
            'Vas a mostrar tu idea de negocio a un posible inversionista o socio',
            'Contratas a un desarrollador o una agencia y les darás acceso a tu código o base de datos',
            'Negocias la venta de tu empresa y debes entregar cifras internas',
            'Contratas empleados o freelancers que verán información de clientes',
        ],
        'not_included' => [
            'Registro de marca o patente ante la SIC',
            'Acuerdo de no competencia (es un documento distinto, lo cotizamos aparte)',
            'Litigio por un incumplimiento que ya ocurrió',
        ],
        'seo_title' => 'Acuerdo de Confidencialidad (NDA) en Colombia | Redactado por abogados',
        'seo_description' => 'NDA bajo ley colombiana con cláusula penal y firma electrónica válida, sobre un formato escrito por abogados colombianos. Entrega en 24 horas desde $90.000.',
        'focus_keyword' => 'acuerdo de confidencialidad colombia',
        'seo_keywords' => [
            'acuerdo de confidencialidad', 'nda colombia', 'modelo acuerdo de confidencialidad',
            'contrato de confidencialidad', 'acuerdo de confidencialidad colombia',
            'nda para desarrolladores', 'proteger idea de negocio colombia',
        ],
        'faqs' => [
            [
                'question' => '¿Un NDA sirve de algo realmente en Colombia?',
                'answer' => "Sí, siempre que esté bien redactado. Un acuerdo de confidencialidad es un contrato y obliga como tal (artículo 1602 del Código Civil). La diferencia entre uno que sirve y uno decorativo está en tres cosas: que la información confidencial esté **definida con precisión**, que haya una **cláusula penal con monto concreto** y que el plazo de reserva sobreviva al final de la relación. Las plantillas gratuitas suelen fallar en las tres.",
            ],
            [
                'question' => '¿Cuánto debe ser la cláusula penal?',
                'answer' => 'Debe ser proporcional al daño que causaría la filtración. Un monto irrisorio no disuade y uno desproporcionado puede ser reducido por un juez. Nosotros la calibramos según el tipo de información y el tamaño del negocio que nos describas.',
            ],
            [
                'question' => '¿Necesito firmarlo ante notario?',
                'answer' => 'No. Un NDA es un documento privado y no requiere autenticación notarial para ser válido. Con la firma de ambas partes basta, y la firma electrónica con OTP tiene la misma validez probatoria según la Ley 527 de 1999.',
            ],
            [
                'question' => '¿Puede ser mutuo o solo protege a una parte?',
                'answer' => 'Lo redactamos como lo necesites. El unilateral protege solo a quien entrega la información; el bilateral protege a ambas partes y es el estándar cuando dos empresas exploran una alianza. Dinos cuál es tu caso en el formulario.',
            ],
        ],
        'body' => <<<'HTML'
<h2>Qué es un acuerdo de confidencialidad y cuándo lo necesitas</h2>
<p>Un acuerdo de confidencialidad &mdash;también llamado NDA, por <em>non-disclosure agreement</em>&mdash; es un contrato en el que una o ambas partes se comprometen a no divulgar ni usar en su propio beneficio la información que reciben de la otra. En Colombia no está regulado por una ley específica: vale como cualquier contrato, por el artículo 1602 del Código Civil, y por eso todo depende de cómo esté escrito.</p>
<p>El momento de firmarlo es <strong>antes</strong> de mostrar la información, no después. Un NDA no sirve para proteger algo que ya contaste.</p>

<h2>Los tres errores que vuelven inútil un NDA</h2>
<h3>1. Definir «información confidencial» de forma vaga</h3>
<p>Si el acuerdo dice que es confidencial «toda la información compartida», un juez puede considerar que la cláusula es demasiado amplia y no sabe qué proteger. Hay que listar las categorías concretas: código fuente, listas de clientes, estados financieros, fórmulas, estrategia comercial.</p>

<h3>2. No poner una consecuencia económica</h3>
<p>Sin cláusula penal, quien incumple sabe que tendrás que probar el daño en un proceso largo y costoso. Con cláusula penal, el monto está pactado de antemano y eso cambia por completo el incentivo.</p>

<h3>3. Que la obligación termine con el contrato</h3>
<p>Muchas plantillas hacen que la confidencialidad muera cuando termina la relación comercial. Debe ser al contrario: la reserva tiene que sobrevivir y durar un plazo propio, contado desde que la relación termina.</p>

<h2>Qué incluye el documento que te entregamos</h2>
<p>Partimos de una plantilla maestra revisada por nuestro equipo societario y la ajustamos a tu caso. Incluye identificación precisa de las partes, objeto y propósito del intercambio, definición cerrada de la información protegida con sus excepciones legales, obligaciones concretas de custodia, plazo de reserva, cláusula penal, ley aplicable y mecanismo de solución de conflictos.</p>

<h2>Cuánto tarda y cuánto cuesta</h2>
<p>Es un documento de la Fase 1: la estructura es estable y las variables son pocas, así que lo entregamos en <strong>24 horas hábiles</strong> por el precio estándar, o en <strong>menos de 4 horas</strong> si eliges Express. El formato lo escribieron nuestros abogados y lo actualizamos cuando cambia la norma; el documento en sí se arma de forma automatizada, y eso es justo lo que permite este precio.</p>
HTML,
    ],

    [
        'name' => 'Eliminación de reporte en Datacrédito',
        'short_name' => 'Datacrédito',
        'slug' => 'eliminar-reporte-datacredito',
        'practice_area' => 'financiero',
        'automation_phase' => 'automated',
        'emoji' => '💳',
        'tagline' => 'Derecho de petición para borrar reportes negativos injustos o vencidos.',
        'summary' => 'Derecho de petición dirigido a la entidad financiera y a la central de riesgo, con fundamento en la Ley 1266 de 2008, para eliminar o actualizar un reporte negativo que ya no debería estar ahí.',
        'legal_basis' => 'Ley 1266 de 2008 (Habeas Data) y Ley 2157 de 2021',
        'supports_signature' => false,
        'is_featured' => true,
        'sort_order' => 20,
        'what_you_get' => [
            'Derecho de petición dirigido a la entidad reportante y a la central de riesgo',
            'Fundamento normativo exacto según la causal de tu caso',
            'Solicitud expresa de eliminación o actualización del dato',
            'Advertencia de las consecuencias de no responder en los términos de ley',
            'Instrucciones de radicación por correo certificado y canal digital',
        ],
        'required_info' => [
            'Nombres completos y número de cédula',
            'Nombre de la entidad que te reportó',
            'Número de la obligación u oferta reportada',
            'Fecha aproximada en que dejaste de pagar o en que pagaste',
            'Si ya pagaste: soporte o fecha del pago',
        ],
        'use_cases' => [
            'Ya pagaste la deuda pero el reporte negativo sigue apareciendo',
            'El reporte lleva más de 4 años desde que se extinguió la obligación',
            'Nunca tuviste esa obligación: es un caso de suplantación',
            'Te reportaron sin haberte avisado previamente, como exige la ley',
            'El monto o el estado reportado está equivocado',
        ],
        'not_included' => [
            'Negociación de la deuda con la entidad o acuerdo de pago',
            'Garantía de que el puntaje de crédito suba a un valor determinado',
            'Eliminación de un reporte que es cierto, vigente y fue notificado',
        ],
        'seo_title' => 'Cómo salir de Datacrédito en Colombia | Derecho de petición por abogado',
        'seo_description' => 'Derecho de petición para eliminar reportes negativos injustos o vencidos en Datacrédito y TransUnion. Ley 1266 de 2008. Redactado por abogados, entrega en 24 horas.',
        'focus_keyword' => 'como salir de datacredito',
        'seo_keywords' => [
            'como salir de datacredito', 'eliminar reporte datacredito', 'borrar datacredito',
            'derecho de peticion datacredito', 'ley de borron y cuenta nueva',
            'quitar reporte negativo datacredito', 'habeas data colombia', 'salir de centrales de riesgo',
        ],
        'faqs' => [
            [
                'question' => '¿Cuánto tiempo puede durar un reporte negativo en Datacrédito?',
                'answer' => "Depende. Si pagaste la deuda, el reporte negativo **no puede permanecer más del doble del tiempo que estuviste en mora, con un máximo de 4 años** contados desde que extinguiste la obligación (Ley 1266 de 2008). Si la mora fue de 6 meses, el reporte no debería durar más de 12 meses después del pago. Mucha gente no sabe esto y carga un reporte que ya caducó.",
            ],
            [
                'question' => '¿Me pueden reportar sin avisarme?',
                'answer' => 'No. La entidad debe enviarte una comunicación previa con al menos **20 días calendario** de anticipación antes de reportarte, para que puedas controvertir o pagar. Si no lo hizo, el reporte es irregular y esa es una causal fuerte de eliminación. Es uno de los primeros puntos que revisamos.',
            ],
            [
                'question' => '¿Esto me garantiza que me borren el reporte?',
                'answer' => 'Seremos honestos: **no**, y quien te prometa lo contrario te está engañando. Si la deuda es real, está vigente y te notificaron en debida forma, el reporte es legal y ningún abogado lo puede borrar. Lo que hacemos es identificar si en tu caso hay una causal legal &mdash; pago, caducidad, falta de notificación, suplantación o error en el dato &mdash; y exigirla con el fundamento correcto. Cuando la causal existe, la tasa de éxito es alta.',
            ],
            [
                'question' => '¿En cuánto tiempo deben responderme?',
                'answer' => 'La central de riesgo tiene **15 días hábiles** para resolver un reclamo, prorrogables por 8 días más si avisa. Si no responde o responde negando sin fundamento, el siguiente paso es una acción de tutela por violación al habeas data, que también podemos redactar.',
            ],
            [
                'question' => '¿Qué es la «ley de borrón y cuenta nueva»?',
                'answer' => 'Es la Ley 2157 de 2021, que ordenó eliminar por una sola vez los reportes negativos de quienes pagaran sus deudas dentro de un plazo determinado. Ese beneficio transitorio ya venció, pero la ley también dejó reglas permanentes más favorables al ciudadano que seguimos usando como fundamento.',
            ],
        ],
        'body' => <<<'HTML'
<h2>Por qué un reporte en Datacrédito te bloquea</h2>
<p>Datacrédito y TransUnion no prestan plata: administran información. Pero esa información decide si te aprueban un crédito de vivienda, un celular en cuotas, un arriendo o incluso un empleo. Por eso un reporte que ya no debería estar ahí tiene un costo real y diario.</p>
<p>La buena noticia es que la Ley 1266 de 2008 te da derechos concretos y plazos exigibles. La mala es que las entidades rara vez los aplican solas: hay que pedirlo por escrito, con el fundamento correcto.</p>

<h2>Las cinco causales que sí funcionan</h2>
<h3>1. Caducidad del dato negativo</h3>
<p>Si ya pagaste, el reporte negativo no puede durar más del doble del tiempo que estuviste en mora, con tope de 4 años desde que se extinguió la obligación. Es la causal más común y la más desaprovechada.</p>

<h3>2. Falta de notificación previa</h3>
<p>La entidad debe avisarte con 20 días calendario de anticipación antes de reportarte. Si no hay prueba de ese aviso, el reporte se hizo violando el debido proceso.</p>

<h3>3. Pago ya realizado y no actualizado</h3>
<p>Pagaste, tienes el soporte, y el dato sigue apareciendo como moroso. La entidad tiene el deber de actualizarlo.</p>

<h3>4. Suplantación de identidad</h3>
<p>Nunca adquiriste esa obligación. Aquí el derecho de petición va acompañado de la denuncia y la carga de probar se invierte: le toca a la entidad demostrar que fuiste tú.</p>

<h3>5. Dato inexacto</h3>
<p>El monto, la fecha o el estado de la obligación están mal. Tienes derecho a que la información sea veraz y completa.</p>

<h2>Qué hacemos exactamente</h2>
<p>Revisamos los datos que nos das, identificamos cuál de las causales aplica a tu caso y redactamos un derecho de petición dirigido <strong>tanto a la entidad que te reportó como a la central de riesgo</strong> &mdash; las dos tienen obligaciones, y pedirlo solo a una es el error más frecuente. El documento cita la norma aplicable, formula la petición de forma expresa y advierte las consecuencias de no responder en término.</p>

<h2>Si no responden</h2>
<p>Cuando la central guarda silencio o niega sin motivar, procede una acción de tutela por violación del derecho al habeas data. Es un trámite distinto que también redactamos, y en estos casos los jueces suelen fallar a favor del ciudadano.</p>
HTML,
    ],

    [
        'name' => 'Promesa de Compraventa de Vehículo',
        'short_name' => 'Compraventa vehículo',
        'slug' => 'promesa-de-compraventa-vehiculo',
        'practice_area' => 'consumo',
        'automation_phase' => 'automated',
        'emoji' => '🚗',
        'tagline' => 'Vende o compra tu carro sin quedar expuesto a multas, embargos o vicios ocultos.',
        'summary' => 'Promesa de compraventa de vehículo con cláusulas que definen quién responde por comparendos anteriores, cómo se maneja el traspaso y qué pasa si una parte se arrepiente.',
        'legal_basis' => 'Código Civil (art. 1611) y Código Nacional de Tránsito (Ley 769 de 2002)',
        'supports_signature' => true,
        'is_featured' => true,
        'sort_order' => 30,
        'what_you_get' => [
            'Promesa de compraventa en PDF y Word',
            'Cláusula de responsabilidad por comparendos y multas anteriores al traspaso',
            'Plazo y lugar exactos para firmar el traspaso ante el organismo de tránsito',
            'Cláusula penal si una de las partes se retracta',
            'Declaración del vendedor sobre prendas, embargos y estado del vehículo',
            'Opción de firma electrónica con OTP',
        ],
        'required_info' => [
            'Datos completos de comprador y vendedor (nombre, cédula, dirección)',
            'Placa, marca, línea, modelo, color, número de motor y chasis',
            'Precio acordado y forma de pago',
            'Fecha y lugar pactados para el traspaso',
            'Si hay prenda o pignoración vigente',
        ],
        'use_cases' => [
            'Vendes tu carro a un particular y quieres blindarte de las multas que haga después',
            'Compras un usado y necesitas que el vendedor responda por vicios ocultos',
            'El pago se hará en cuotas o contra entrega y hay que fijar las condiciones',
            'El traspaso se hará días después de entregar el vehículo',
        ],
        'not_included' => [
            'Trámite del traspaso ante el organismo de tránsito',
            'Levantamiento de prenda ante el banco',
            'Peritaje mecánico o revisión del estado real del vehículo',
            'Consulta de comparendos (puedes hacerla gratis en el RUNT y el SIMIT)',
        ],
        'seo_title' => 'Promesa de Compraventa de Vehículo en Colombia | Formato por abogado',
        'seo_description' => 'Promesa de compraventa de carro o moto con cláusulas de protección contra multas, embargos y vicios ocultos. Redactada por abogados. Entrega en 24 horas.',
        'focus_keyword' => 'promesa de compraventa vehiculo',
        'seo_keywords' => [
            'promesa de compraventa vehiculo', 'contrato de compraventa de carro',
            'formato promesa de compraventa vehiculo', 'contrato venta de moto colombia',
            'como vender mi carro seguro', 'traspaso de vehiculo colombia',
        ],
        'faqs' => [
            [
                'question' => 'Si vendo mi carro y el comprador no hace el traspaso, ¿quién responde por las multas?',
                'answer' => "Mientras el vehículo esté a tu nombre en el RUNT, **las multas te llegan a ti**. Por eso la promesa debe fijar un plazo máximo para el traspaso, con una cláusula penal si no se cumple, y una declaración de que todo comparendo posterior a la entrega es del comprador. Sin eso escrito, estás asumiendo el riesgo completo.",
            ],
            [
                'question' => '¿La promesa de compraventa necesita notaría?',
                'answer' => 'No es obligatorio autenticarla para que sea válida entre las partes. Pero la autenticación en notaría le da fecha cierta y hace mucho más difícil que alguien niegue su firma después. Para montos altos vale los $15.000 que cuesta.',
            ],
            [
                'question' => '¿Qué pasa si el carro tiene prenda con un banco?',
                'answer' => 'El traspaso no se puede hacer hasta que se levante la prenda. La promesa debe decir quién paga el saldo, en qué plazo se levanta y qué pasa si el banco no lo libera. Lo incluimos cuando nos avisas que hay pignoración.',
            ],
            [
                'question' => '¿Y si aparecen daños que el vendedor no me contó?',
                'answer' => 'Para eso sirve la cláusula de vicios ocultos y la declaración del estado del vehículo. El vendedor declara lo que sabe, y si oculta algo relevante responde por ello. No reemplaza un peritaje mecánico, pero te da con qué reclamar.',
            ],
        ],
        'body' => <<<'HTML'
<h2>El riesgo real de vender un carro sin contrato</h2>
<p>En Colombia el vehículo responde a quien figura en el RUNT. Si entregas el carro, recibes la plata y el comprador no hace el traspaso, los comparendos siguen llegando a tu nombre, se acumulan en el SIMIT y te bloquean trámites. Hay casos de personas que descubrieron años después que seguían siendo propietarias de un carro involucrado en un accidente.</p>
<p>La promesa de compraventa es lo que separa un acuerdo de palabra de un documento que puedes hacer valer.</p>

<h2>Las cláusulas que marcan la diferencia</h2>
<h3>Plazo máximo para el traspaso</h3>
<p>Una fecha concreta, no «a la mayor brevedad». Y una consecuencia económica si se pasa de ahí.</p>

<h3>Responsabilidad por multas anteriores y posteriores</h3>
<p>Debe quedar escrito que los comparendos previos a la entrega son del vendedor y los posteriores del comprador, con el kilometraje y la fecha exacta de la entrega como corte.</p>

<h3>Declaración de libertad de gravámenes</h3>
<p>El vendedor afirma que el vehículo no tiene prenda, embargo ni pleito pendiente. Si miente, hay consecuencia.</p>

<h3>Cláusula penal por retracto</h3>
<p>Si una parte se arrepiente después de recibir el anticipo, la otra no queda a merced de su buena voluntad.</p>

<h2>Qué debes verificar tú antes de firmar</h2>
<p>Ningún contrato reemplaza la verificación. Antes de firmar, consulta gratis la <strong>historia del vehículo en el RUNT</strong>, los <strong>comparendos en el SIMIT</strong> y pide un peritaje mecánico si es un usado. Nosotros redactamos el documento que protege el acuerdo; la revisión del carro es tuya.</p>

<h2>Entrega</h2>
<p>Es un documento de estructura estable, así que lo entregamos en <strong>24 horas hábiles</strong> o en <strong>menos de 4 horas</strong> con el plan Express. Incluye la opción de firma electrónica con OTP para que ambas partes firmen desde el celular.</p>
HTML,
    ],

    [
        'name' => 'Contrato de Arrendamiento de Vivienda Urbana',
        'short_name' => 'Arrendamiento',
        'slug' => 'contrato-de-arrendamiento-vivienda-urbana',
        'practice_area' => 'inmobiliario',
        'automation_phase' => 'automated',
        'emoji' => '🏠',
        'tagline' => 'Contrato de arriendo blindado bajo la Ley 820, con cláusulas que sí puedes hacer cumplir.',
        'summary' => 'Contrato de arrendamiento de vivienda urbana conforme a la Ley 820 de 2003, con inventario, reglas de incremento del canon, causales de terminación y garantías admitidas por la ley.',
        'legal_basis' => 'Ley 820 de 2003 (Régimen de arrendamiento de vivienda urbana)',
        'supports_signature' => true,
        'is_featured' => true,
        'sort_order' => 40,
        'what_you_get' => [
            'Contrato de arrendamiento en PDF y Word',
            'Inventario de entrega del inmueble y su estado',
            'Regla de incremento anual del canon conforme a la ley',
            'Causales de terminación y preavisos con los plazos correctos',
            'Cláusulas de servicios públicos, administración y reparaciones',
            'Firma electrónica con OTP para arrendador, arrendatario y codeudor',
        ],
        'required_info' => [
            'Datos del arrendador y del arrendatario',
            'Dirección exacta del inmueble y matrícula inmobiliaria',
            'Canon mensual y día de pago',
            'Duración del contrato',
            'Si incluye administración, parqueadero o depósito',
            'Datos del codeudor o la garantía ofrecida, si aplica',
        ],
        'use_cases' => [
            'Arriendas tu apartamento o casa directamente, sin inmobiliaria',
            'Vas a tomar en arriendo y quieres revisar que las cláusulas sean legales',
            'Necesitas un contrato que te sirva para una restitución si dejan de pagar',
            'Quieres fijar bien el inventario para no pelear por los daños al final',
        ],
        'not_included' => [
            'Arrendamiento de locales comerciales u oficinas (es otro régimen legal)',
            'Estudio de crédito o verificación de ingresos del arrendatario',
            'Proceso de restitución del inmueble (lo cotizamos como caso complejo)',
            'Registro del contrato o trámites ante la alcaldía',
        ],
        'seo_title' => 'Contrato de Arrendamiento de Vivienda Urbana | Ley 820 | Hazlo Legal',
        'seo_description' => 'Contrato de arriendo de vivienda conforme a la Ley 820 de 2003, con inventario y cláusulas exigibles. Redactado por abogados colombianos. Entrega en 24 horas.',
        'focus_keyword' => 'contrato de arrendamiento vivienda urbana',
        'seo_keywords' => [
            'contrato de arrendamiento vivienda urbana', 'modelo contrato de arrendamiento',
            'contrato de arriendo colombia', 'ley 820 de 2003 arrendamiento',
            'contrato de arrendamiento para imprimir', 'incremento canon de arrendamiento',
        ],
        'faqs' => [
            [
                'question' => '¿Cuánto puedo subir el arriendo cada año?',
                'answer' => "Según la Ley 820 de 2003, el incremento solo puede hacerse **una vez cada doce meses** y no puede superar el **100 % del IPC** del año anterior. Cualquier cláusula que pacte un porcentaje fijo superior es ineficaz. En el contrato lo redactamos remitiendo al IPC para que siempre quede dentro de la ley.",
            ],
            [
                'question' => '¿Puedo pedir depósito en dinero como garantía?',
                'answer' => 'No. La Ley 820 **prohíbe expresamente** exigir depósitos en dinero u otra forma de garantía real para asegurar el cumplimiento del contrato de vivienda. Es una de las cláusulas ilegales más comunes en las plantillas que circulan por internet. Las garantías válidas son el codeudor solidario, la póliza de arrendamiento y la fianza.',
            ],
            [
                'question' => '¿Con cuánto tiempo debo avisar si no quiero renovar?',
                'answer' => 'El arrendador debe avisar con **tres meses** de anticipación al vencimiento e invocar una causal legal; en algunos casos debe además indemnizar. El arrendatario puede terminar avisando con tres meses y pagando una indemnización equivalente a tres meses de canon, o sin indemnización si avisa en el término legal. Lo dejamos explícito en el contrato para evitar discusiones.',
            ],
            [
                'question' => '¿Sirve para un local comercial?',
                'answer' => 'No. El arrendamiento comercial se rige por el Código de Comercio y tiene reglas muy distintas, como el derecho de renovación del arrendatario. Si necesitas un contrato de local, escríbenos y lo cotizamos aparte.',
            ],
            [
                'question' => '¿Es obligatorio hacer inventario?',
                'answer' => 'No es obligatorio, pero es lo que decide la pelea al final del contrato. Sin inventario firmado, probar que una puerta estaba rayada desde el principio es casi imposible. Lo incluimos siempre.',
            ],
        ],
        'body' => <<<'HTML'
<h2>Por qué las plantillas de arriendo que circulan por internet te dejan expuesto</h2>
<p>El arrendamiento de vivienda urbana en Colombia está regulado por la Ley 820 de 2003, y esa ley es <strong>imperativa</strong>: lo que contradiga sus mínimos no vale, aunque las dos partes lo firmen. El problema es que la mayoría de las plantillas gratuitas copian cláusulas que la ley prohíbe.</p>
<p>Las tres que vemos más seguido: exigir depósito en dinero, pactar incrementos fijos por encima del IPC, y permitir la terminación unilateral sin causal ni preaviso. Las tres son ineficaces, y descubrirlo cuando ya hay conflicto es caro.</p>

<h2>Qué exige la Ley 820 y cómo lo cumplimos</h2>
<h3>Incremento del canon</h3>
<p>Una vez cada doce meses, máximo el 100 % del IPC del año anterior. Lo redactamos por remisión al IPC, no con un número fijo.</p>

<h3>Garantías permitidas</h3>
<p>Codeudor solidario, póliza de arrendamiento o fianza. Nunca depósito en efectivo.</p>

<h3>Causales y preavisos de terminación</h3>
<p>Los plazos son distintos para arrendador y arrendatario, y equivocarse invalida el aviso. Quedan escritos con precisión.</p>

<h3>Servicios públicos</h3>
<p>Quién paga qué, y la cláusula que te protege de quedar con una deuda de servicios del inquilino anterior o del que se va.</p>

<h2>El inventario: la cláusula que más plata ahorra</h2>
<p>Al final de todo arriendo aparece la misma discusión: si ese daño ya estaba. Un inventario firmado al momento de la entrega, con el estado de pisos, paredes, baños, cocina, ventanas y electrodomésticos, convierte una pelea de meses en un documento de dos páginas. Va incluido.</p>

<h2>Si el inquilino deja de pagar</h2>
<p>Un contrato bien redactado es la base del proceso de restitución del inmueble arrendado. Si ya estás en esa situación, el documento no basta: necesitas representación, y eso lo manejamos como <a href="/caso-complejo">caso complejo</a>.</p>

<h2>Entrega y firma</h2>
<p>Lo recibes en <strong>24 horas hábiles</strong>, o en <strong>menos de 4</strong> con Express. Incluye firma electrónica con OTP para que arrendador, arrendatario y codeudor firmen desde el celular sin reunirse.</p>
HTML,
    ],

    [
        'name' => 'Derecho de Petición por Fotomultas',
        'short_name' => 'Fotomultas',
        'slug' => 'derecho-de-peticion-fotomultas',
        'practice_area' => 'transito',
        'automation_phase' => 'automated',
        'emoji' => '📷',
        'tagline' => 'Pide la nulidad del comparendo por indebida notificación o caducidad.',
        'summary' => 'Derecho de petición ante la secretaría de tránsito solicitando la nulidad o el archivo del comparendo por detección electrónica, cuando hubo falla en la notificación o venció el término para sancionar.',
        'legal_basis' => 'Ley 1843 de 2017, Ley 769 de 2002 y Ley 1437 de 2011 (CPACA)',
        'supports_signature' => false,
        'is_featured' => true,
        'sort_order' => 50,
        'what_you_get' => [
            'Derecho de petición dirigido a la secretaría de tránsito correspondiente',
            'Argumentación según la causal: notificación, caducidad o falla probatoria',
            'Solicitud de las pruebas que la entidad está obligada a tener',
            'Petición expresa de archivo o nulidad del comparendo',
            'Instrucciones de radicación y plazos de respuesta',
        ],
        'required_info' => [
            'Nombres completos y cédula',
            'Número del comparendo',
            'Placa del vehículo',
            'Fecha de la infracción y fecha en que te enteraste',
            'Secretaría de tránsito que lo impuso',
            'Si recibiste alguna notificación física o por correo',
        ],
        'use_cases' => [
            'Te enteraste de la fotomulta meses después, al consultar el SIMIT',
            'Nunca te llegó la notificación a tu dirección registrada',
            'Pasaron más de tres años desde la infracción sin que te cobraran',
            'El vehículo ya no era tuyo cuando se impuso el comparendo',
            'La foto no permite identificar la placa o el conductor con claridad',
        ],
        'not_included' => [
            'Pago del comparendo o trámite de descuento por pronto pago',
            'Representación en audiencia ante la autoridad de tránsito',
            'Garantía de anulación cuando la notificación fue correcta y el hecho es cierto',
        ],
        'seo_title' => 'Cómo impugnar una fotomulta en Colombia | Derecho de petición',
        'seo_description' => 'Derecho de petición para anular comparendos por fotomulta mal notificada o caducada. Ley 1843 de 2017. Redactado por abogados de tránsito. Entrega en 24 horas.',
        'focus_keyword' => 'como impugnar una fotomulta',
        'seo_keywords' => [
            'como impugnar una fotomulta', 'fotomulta mal notificada', 'derecho de peticion fotomulta',
            'caducidad comparendo colombia', 'anular comparendo', 'fotomulta sin notificacion',
            'como quitar una fotomulta', 'comparendo prescrito',
        ],
        'faqs' => [
            [
                'question' => '¿En cuánto tiempo deben notificarme una fotomulta?',
                'answer' => "La Ley 1843 de 2017 obliga a enviar la notificación del comparendo por detección electrónica dentro de los **tres días hábiles** siguientes a la validación de la infracción, al domicilio registrado en el RUNT. Si te enteraste meses después porque apareció en el SIMIT, hay una falla de notificación y eso vicia el debido proceso.",
            ],
            [
                'question' => '¿Las fotomultas caducan?',
                'answer' => 'Sí. La facultad de la administración para imponer la sanción **caduca a los tres años** contados desde el día de la infracción (artículo 52 del CPACA). Y una vez impuesta, el cobro también prescribe a los tres años. Si tu comparendo supera esos términos, procede el archivo.',
            ],
            [
                'question' => '¿Me pueden cobrar una fotomulta de un carro que ya vendí?',
                'answer' => 'Si el traspaso ya estaba registrado en el RUNT cuando ocurrió la infracción, no. El problema aparece cuando el comprador no hizo el traspaso: ahí el comparendo llega a tu nombre y toca demostrar la fecha real de la entrega. Por eso insistimos tanto en la <a href="/documentos/promesa-de-compraventa-vehiculo">promesa de compraventa</a>.',
            ],
            [
                'question' => '¿Esto me garantiza que me quiten la multa?',
                'answer' => 'No. Si la notificación se hizo bien, el término está vigente y la infracción es cierta, el comparendo es válido y lo honesto es decírtelo. Lo que hacemos es revisar si existe una causal real y exigirla correctamente. Cuando hay falla de notificación o caducidad, los resultados son buenos.',
            ],
            [
                'question' => '¿Cuánto tiempo tienen para responderme?',
                'answer' => 'Quince días hábiles para un derecho de petición de interés particular. Si no responden, hay silencio administrativo y procede una tutela por violación del derecho fundamental de petición.',
            ],
        ],
        'body' => <<<'HTML'
<h2>La mayoría de las fotomultas no se pelean por el hecho, sino por el procedimiento</h2>
<p>Discutir si pasaste o no en rojo suele ser inútil: la cámara tiene la foto. Lo que sí se puede atacar &mdash;y donde las secretarías de tránsito fallan con frecuencia&mdash; es el procedimiento: cómo te notificaron, cuándo, y si todavía tenían facultad para sancionarte.</p>

<h2>Las tres causales que funcionan</h2>
<h3>1. Indebida notificación</h3>
<p>La Ley 1843 de 2017 exige enviar la notificación dentro de los tres días hábiles siguientes a la validación, al domicilio del RUNT. Si te enteraste consultando el SIMIT meses después, esa notificación no existió o no se hizo en término. Sin notificación válida no hay debido proceso, y sin debido proceso la sanción es nula.</p>

<h3>2. Caducidad de la facultad sancionatoria</h3>
<p>La administración tiene tres años desde la infracción para imponer la sanción. Pasado ese término, pierde la competencia. Hay comparendos que llevan años durmiendo en el sistema y aparecen cuando vas a hacer un trámite.</p>

<h3>3. Falla probatoria</h3>
<p>La entidad debe conservar la evidencia: la fotografía con placa legible, el certificado de calibración del equipo, el registro de la validación por un agente. Si pides esas pruebas y no las tiene o están incompletas, el soporte de la sanción se cae.</p>

<h2>Qué redactamos</h2>
<p>Un derecho de petición dirigido a la secretaría de tránsito que impuso el comparendo, que (i) solicita copia del expediente completo y de las pruebas, (ii) argumenta la causal aplicable a tu caso con la norma exacta, (iii) pide de forma expresa el archivo o la declaratoria de nulidad, y (iv) advierte las consecuencias del silencio administrativo.</p>

<h2>Qué puedes verificar gratis antes de contratarnos</h2>
<p>Consulta tu placa en el <strong>SIMIT</strong> y en el <strong>RUNT</strong>. Mira la fecha de la infracción y la fecha de imposición. Si la diferencia con hoy supera tres años, o si nunca recibiste nada en tu domicilio registrado, tienes caso. Si quieres, cuéntanos lo que ves y te decimos con franqueza si vale la pena.</p>
HTML,
    ],

];
