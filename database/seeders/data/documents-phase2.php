<?php

declare(strict_types=1);

/*
| Fase 2 — Servicios estrictamente manuales.
| Exigen narración cronológica, cálculos o cláusulas condicionales. Intentar
| automatizarlos con reemplazo de variables produce documentos inservibles.
*/

return [

    [
        'name' => 'Acción de Tutela por Salud',
        'short_name' => 'Acción de tutela',
        'slug' => 'accion-de-tutela-salud',
        'practice_area' => 'salud',
        'automation_phase' => 'manual',
        'emoji' => '⚕️',
        'tagline' => 'Exige a tu EPS el procedimiento, el medicamento o la cita que te negó.',
        'summary' => 'Acción de tutela redactada por un abogado a partir de tus hechos concretos, con los antecedentes jurisprudenciales aplicables y la medida provisional cuando hay riesgo de vida.',
        'legal_basis' => 'Constitución Política (art. 86), Decreto 2591 de 1991 y Ley 1751 de 2015',
        'supports_signature' => false,
        'is_featured' => true,
        'sort_order' => 1,
        'what_you_get' => [
            'Tutela redactada desde cero sobre los hechos de tu caso',
            'Narración cronológica de los hechos con las pruebas enumeradas',
            'Jurisprudencia de la Corte Constitucional aplicable a tu situación',
            'Solicitud de medida provisional cuando hay riesgo inminente',
            'Pretensiones redactadas de forma que el juez pueda ordenarlas',
            'Guía de radicación: ante qué juez, cómo y con qué anexos',
        ],
        'required_info' => [
            'Nombres completos, cédula y EPS',
            'Qué te negaron exactamente (procedimiento, medicamento, cita, traslado)',
            'Fechas: cuándo lo ordenó el médico, cuándo lo pediste, cuándo te lo negaron',
            'Orden médica o fórmula (la necesitamos para citar el diagnóstico)',
            'Respuesta de la EPS, si te dieron alguna por escrito',
            'Si hay riesgo de vida o deterioro acelerado',
        ],
        'use_cases' => [
            'La EPS te niega una cirugía ordenada por tu médico tratante',
            'Te niegan un medicamento por no estar en el PBS',
            'Llevas meses esperando una cita con especialista',
            'Te niegan el traslado a otra ciudad para un tratamiento',
            'No autorizan insumos, pañales o fórmula para una persona a cargo',
            'Te negaron la incapacidad o el pago de prestaciones económicas',
        ],
        'not_included' => [
            'Radicación ante el juzgado (te explicamos cómo hacerla; es gratuita)',
            'Representación en el proceso o en el incidente de desacato',
            'Garantía del resultado: la decisión es del juez',
            'Obtención de tu historia clínica (la EPS debe entregártela si la pides)',
        ],
        'seo_title' => 'Cómo hacer una Acción de Tutela por Salud en Colombia | HazloLegal',
        'seo_description' => 'Tutela contra tu EPS redactada por un abogado especialista a partir de tus hechos. Medida provisional si hay riesgo de vida. Entrega en 24 horas, desde $90.000.',
        'focus_keyword' => 'como hacer una tutela por salud',
        'seo_keywords' => [
            'como hacer una tutela', 'tutela por salud', 'modelo de tutela eps',
            'tutela contra eps', 'tutela medicamentos', 'accion de tutela colombia',
            'tutela por cirugia negada', 'tutela cita especialista', 'formato de tutela',
        ],
        'faqs' => [
            [
                'question' => '¿Necesito abogado para presentar una tutela?',
                'answer' => "No. La acción de tutela es un mecanismo informal y **cualquier persona puede presentarla sin abogado**, incluso a mano. Te lo decimos porque es la verdad y preferimos que lo sepas. Lo que cambia con un abogado es la probabilidad de éxito: la tutela bien redactada identifica el derecho fundamental vulnerado, narra los hechos en el orden que el juez necesita, cita la jurisprudencia aplicable y formula pretensiones que el juez pueda ordenar sin reformular. Una tutela mal escrita se puede negar aunque el derecho exista.",
            ],
            [
                'question' => '¿Cuánto se demora un juez en resolver una tutela?',
                'answer' => 'El término es de **10 días hábiles** desde la radicación, y es de los pocos plazos que la justicia colombiana cumple. Si pedimos medida provisional y el juez la concede, la EPS puede quedar obligada a autorizar en 48 horas mientras se decide el fondo.',
            ],
            [
                'question' => '¿Qué es la medida provisional y cuándo la piden?',
                'answer' => 'Es una orden urgente que el juez da antes de fallar, cuando esperar los 10 días podría causar un daño irreparable. La solicitamos siempre que haya riesgo de vida, deterioro acelerado, dolor severo o un menor de edad involucrado. Es la diferencia entre semanas y días.',
            ],
            [
                'question' => '¿Puedo tutelar si la EPS no me ha respondido nada?',
                'answer' => 'Sí. El silencio de la EPS frente a una orden médica también vulnera derechos. De hecho, la demora injustificada en autorizar es una de las causas más frecuentes de tutelas concedidas en salud.',
            ],
            [
                'question' => '¿Y si la EPS incumple el fallo?',
                'answer' => 'Procede un incidente de desacato, que puede terminar en arresto y multa para el representante legal de la EPS. Es un trámite aparte; si llegas a ese punto, escríbenos y lo manejamos como caso complejo.',
            ],
            [
                'question' => '¿Por qué este documento no es automatizable?',
                'answer' => 'Porque la tutela se gana o se pierde en la narración de los hechos. Hay que leer la orden médica, entender el diagnóstico, ordenar la cronología y elegir la línea jurisprudencial que corresponde. Un reemplazo de variables sobre una plantilla produce un documento genérico que el juez detecta de inmediato. Por eso la redacta un abogado desde cero.',
            ],
        ],
        'body' => <<<'HTML'
<h2>Qué es la acción de tutela y por qué funciona tan bien en salud</h2>
<p>La acción de tutela es el mecanismo del artículo 86 de la Constitución para proteger derechos fundamentales de forma inmediata. En salud se volvió la herramienta principal del ciudadano colombiano por una razón simple: el juez tiene <strong>10 días hábiles</strong> para decidir y la EPS tiene que cumplir.</p>
<p>La Corte Constitucional ha construido durante tres décadas una línea jurisprudencial muy favorable al paciente. La Ley 1751 de 2015 reconoció la salud como derecho fundamental autónomo, lo que significa que ya no hay que demostrar conexidad con la vida: basta con que se vulnere la salud.</p>

<h2>Lo que de verdad decide una tutela</h2>
<h3>La narración de los hechos</h3>
<p>El juez lee cientos de tutelas. La tuya tiene que contarle, en orden cronológico y con fechas, qué ordenó el médico, qué pediste, cuándo, y qué respondió la EPS. Sin esa cronología clara, el juez no puede ver la vulneración.</p>

<h3>El derecho fundamental invocado</h3>
<p>No es lo mismo invocar salud, vida digna, integridad personal o seguridad social. Según lo que te negaron, uno encaja mejor que otro, y el acierto cambia el resultado.</p>

<h3>Las pretensiones</h3>
<p>Un juez solo puede ordenar lo que se le pide de forma clara y posible. Pretensiones vagas &mdash;«que me atiendan bien»&mdash; no se pueden ordenar. Hay que pedir autorización de un procedimiento concreto, con código, en un plazo determinado.</p>

<h3>La medida provisional</h3>
<p>Cuando hay riesgo, pedimos que el juez ordene a la EPS autorizar de inmediato, antes del fallo. Muchos casos se resuelven ahí, en 48 horas.</p>

<h2>Contra quién se dirige</h2>
<p>Normalmente contra tu EPS. Pero según el caso puede ser necesario vincular también a la Secretaría de Salud, a la ADRES o a la IPS. Elegir mal al accionado hace que el juez tenga que vincular de oficio y eso toma tiempo.</p>

<h2>Qué necesitamos de ti</h2>
<p>Lo más importante es la <strong>orden médica o la fórmula</strong>: de ahí sale el diagnóstico y el sustento de que un médico tratante consideró necesario el servicio. Si tienes la negativa de la EPS por escrito, mucho mejor. Si no te han respondido nada, también sirve: el silencio es parte del caso.</p>

<h2>Honestidad sobre el resultado</h2>
<p>Ningún abogado serio te garantiza un fallo. La decisión es del juez. Lo que sí podemos asegurarte es que el documento estará técnicamente bien construido, con los hechos ordenados, la jurisprudencia aplicable y las pretensiones formuladas de forma que se puedan ordenar. Y si lo rechazan por un defecto de forma nuestro, el <a href="/garantia">Certificado de Garantía</a> cubre la devolución completa.</p>
HTML,
    ],

    [
        'name' => 'Revisión y Cálculo de Liquidación Laboral',
        'short_name' => 'Liquidación laboral',
        'slug' => 'revision-de-liquidacion-laboral',
        'practice_area' => 'laboral',
        'automation_phase' => 'manual',
        'emoji' => '💼',
        'tagline' => 'Sabe exactamente cuánto te deben y reclámalo con cifras, no con sospechas.',
        'summary' => 'Auditoría matemática de tu liquidación por un abogado laboralista: cesantías, intereses, prima, vacaciones, horas extras, recargos e indemnización, con el documento de reclamación listo.',
        'legal_basis' => 'Código Sustantivo del Trabajo (arts. 65, 186, 249, 253 y 306)',
        'supports_signature' => false,
        'is_featured' => true,
        'sort_order' => 2,
        'what_you_get' => [
            'Cálculo detallado, concepto por concepto, de todo lo que te corresponde',
            'Comparación contra lo que te pagaron, con la diferencia exacta',
            'Verificación de horas extras, recargos nocturnos, dominicales y festivos',
            'Cálculo de la sanción moratoria del artículo 65 si aplica',
            'Carta de reclamación directa al empleador, lista para radicar',
            'Concepto del abogado sobre la viabilidad de una demanda laboral',
        ],
        'required_info' => [
            'Fecha de ingreso y fecha de retiro',
            'Salario y si era fijo, variable o integral',
            'Tipo de contrato y motivo de la terminación',
            'Desprendibles de pago de los últimos meses',
            'La liquidación que te entregaron, si ya te pagaron',
            'Si trabajabas horas extras, dominicales o turnos nocturnos',
            'Si te pagaban comisiones o auxilios no salariales',
        ],
        'use_cases' => [
            'Te liquidaron y la cifra te parece baja pero no sabes calcularla',
            'Te despidieron sin justa causa y quieres saber la indemnización exacta',
            'Nunca te pagaron horas extras que sí trabajaste',
            'Renunciaste por acoso o incumplimientos y buscas despido indirecto',
            'Llevas meses esperando que te paguen la liquidación',
            'Te pagaron parte del salario como «auxilio» para no liquidar prestaciones',
        ],
        'not_included' => [
            'Demanda laboral ante el juez (se cotiza como caso complejo)',
            'Representación en audiencia de conciliación',
            'Obtención de documentos de la empresa que tú no tengas',
            'Cálculo de pensión o trámites ante Colpensiones',
        ],
        'seo_title' => 'Cómo calcular mi liquidación laboral en Colombia | Revisión por abogado',
        'seo_description' => 'Un abogado laboralista audita tu liquidación: cesantías, intereses, prima, vacaciones, horas extras e indemnización. Te decimos cuánto te deben y cómo reclamarlo.',
        'focus_keyword' => 'como calcular mi liquidacion',
        'seo_keywords' => [
            'como calcular mi liquidacion', 'liquidacion laboral colombia',
            'revisar mi liquidacion', 'calculo de liquidacion laboral',
            'indemnizacion por despido sin justa causa', 'sancion moratoria articulo 65',
            'me liquidaron mal', 'horas extras no pagadas colombia',
        ],
        'faqs' => [
            [
                'question' => '¿Qué conceptos debe incluir una liquidación?',
                'answer' => "Como mínimo: **cesantías** (un salario por año trabajado, proporcional), **intereses sobre las cesantías** (12 % anual), **prima de servicios** (un salario por año, proporcional), **vacaciones** (15 días hábiles por año, proporcional) y el salario de los días trabajados y no pagados. Si el despido fue sin justa causa, se suma la **indemnización**. Y si trabajaste extras, recargos nocturnos, dominicales o festivos que no te pagaron, también entran &mdash; y afectan la base de todo lo demás.",
            ],
            [
                'question' => '¿Qué es la sanción moratoria del artículo 65?',
                'answer' => 'Si al terminar el contrato el empleador no te paga salarios y prestaciones, debe pagarte **un día de salario por cada día de retardo**, hasta por 24 meses. Es la figura más poderosa del derecho laboral colombiano y la que más empleadores ignoran. En muchos casos la sanción termina valiendo más que la liquidación misma.',
            ],
            [
                'question' => '¿Cuánto tiempo tengo para reclamar?',
                'answer' => 'Las acreencias laborales **prescriben en tres años** contados desde que la obligación se hizo exigible. Una reclamación escrita al empleador interrumpe la prescripción por una sola vez. Si estás cerca del límite, no esperes.',
            ],
            [
                'question' => '¿Por qué no usan una calculadora automática?',
                'answer' => 'Porque las calculadoras asumen un salario fijo y un contrato limpio, y la realidad casi nunca es así. Si tenías comisiones, el salario variable cambia la base de cálculo. Si te pagaban parte como «auxilio de transporte» o «bonificación», hay que determinar si eran salario. Si hacías turnos, hay que calcular recargos proporcionales. Eso requiere criterio jurídico, no una fórmula. Por eso este servicio lo hace un abogado a mano.',
            ],
            [
                'question' => '¿Y si firmé un paz y salvo?',
                'answer' => 'Firmar un paz y salvo no impide reclamar lo que legalmente te corresponde. Los derechos laborales mínimos son **irrenunciables** (artículo 14 del Código Sustantivo del Trabajo). Una transacción puede ser válida sobre derechos discutibles, pero no sobre prestaciones ciertas.',
            ],
        ],
        'body' => <<<'HTML'
<h2>La mayoría de las liquidaciones mal pagadas no son fraude, son error</h2>
<p>Las empresas pequeñas liquidan con plantillas de Excel que no contemplan salario variable, no incluyen comisiones en la base, olvidan los recargos o calculan mal la proporcionalidad. El resultado es el mismo que si fuera intencional: te pagan menos de lo que te corresponde.</p>
<p>El problema es que para saberlo hay que hacer la cuenta bien, y la cuenta bien no es una resta.</p>

<h2>Qué revisamos, concepto por concepto</h2>
<h3>La base salarial</h3>
<p>Es el punto donde más se pierde plata. Si recibías comisiones, bonificaciones habituales o pagos disfrazados de auxilio, muy probablemente eran salario y debían entrar en la base de cesantías, prima y vacaciones. Determinarlo es un juicio jurídico.</p>

<h3>Cesantías e intereses</h3>
<p>Un salario mensual por año trabajado, proporcional al tiempo. Más 12 % anual de intereses sobre el saldo. Verificamos también si hubo retiros parciales y si estaban autorizados.</p>

<h3>Prima y vacaciones</h3>
<p>Prima: un salario por año, pagadero en dos cuotas. Vacaciones: 15 días hábiles por año. Las dos se liquidan proporcionalmente y las dos se calculan sobre la base salarial correcta.</p>

<h3>Horas extras y recargos</h3>
<p>Extra diurna 25 %, extra nocturna 75 %, recargo nocturno 35 %, dominical o festivo 75 %. Y las extras no solo se pagan: <strong>aumentan la base</strong> de las prestaciones del período.</p>

<h3>Indemnización por despido sin justa causa</h3>
<p>Depende del tipo de contrato y del salario. En término indefinido con salario menor a 10 SMLMV son 30 días por el primer año y 20 por cada año adicional. En término fijo, el tiempo que faltaba del contrato.</p>

<h3>Sanción moratoria</h3>
<p>Un día de salario por cada día de retardo en el pago, hasta 24 meses. La calculamos siempre, porque a menudo es el concepto más grande.</p>

<h2>Qué recibes</h2>
<p>Un documento con el cálculo completo, la comparación contra lo que te pagaron, la diferencia exacta y una carta de reclamación directa lista para radicar ante tu empleador. Más el concepto honesto del abogado sobre si vale la pena demandar o si la reclamación directa es suficiente.</p>

<h2>Si hay que demandar</h2>
<p>La reclamación directa resuelve muchos casos: puesto frente a cifras bien calculadas, el empleador suele pagar. Si no, el paso siguiente es una demanda laboral, que manejamos como <a href="/caso-complejo">caso complejo</a> con representación.</p>
HTML,
    ],

    [
        'name' => 'Contrato de Prestación de Servicios',
        'short_name' => 'Prestación de servicios',
        'slug' => 'contrato-de-prestacion-de-servicios',
        'practice_area' => 'empresarial',
        'automation_phase' => 'manual',
        'emoji' => '💻',
        'tagline' => 'Asegura tus pagos, limita las revisiones infinitas y define quién es dueño del trabajo.',
        'summary' => 'Contrato de prestación de servicios redactado según tu oficio: entregables, hitos de pago, límite de revisiones, cesión de propiedad intelectual y causales de terminación.',
        'legal_basis' => 'Código Civil (art. 2063) y Ley 23 de 1982 (Derechos de autor)',
        'supports_signature' => true,
        'is_featured' => false,
        'sort_order' => 60,
        'what_you_get' => [
            'Contrato ajustado a tu oficio (desarrollo, diseño, consultoría, marketing, obra)',
            'Entregables y alcance definidos para frenar el aumento de tareas',
            'Hitos de pago con fechas y consecuencias por mora',
            'Límite explícito de rondas de revisión',
            'Cláusula de propiedad intelectual: quién queda dueño y desde cuándo',
            'Cláusula de independencia para evitar que se declare relación laboral',
            'Firma electrónica con OTP para ambas partes',
        ],
        'required_info' => [
            'Qué servicio prestas y cuáles son los entregables concretos',
            'Valor total y cómo se pagará (anticipo, hitos, contra entrega)',
            'Duración o plazo de ejecución',
            'Cuántas rondas de revisión aceptas',
            'Si el cliente queda dueño del trabajo o solo con licencia de uso',
            'Si hay confidencialidad o exclusividad involucrada',
        ],
        'use_cases' => [
            'Eres desarrollador o diseñador y te cambian el alcance a mitad del proyecto',
            'Te deben pagos y no tienes un documento que fije las fechas',
            'Una agencia te subcontrata y necesitas definir la propiedad del entregable',
            'Contratas a un freelance y quieres asegurar que el código quede tuyo',
            'Haces consultoría y necesitas limitar tu responsabilidad',
        ],
        'not_included' => [
            'Contrato laboral (es un régimen distinto)',
            'Registro de obra ante la Dirección Nacional de Derecho de Autor',
            'Cobro judicial de facturas impagadas',
        ],
        'seo_title' => 'Contrato de Prestación de Servicios en Colombia | Para freelance y agencias',
        'seo_description' => 'Contrato de prestación de servicios redactado por abogados: entregables, hitos de pago, límite de revisiones y propiedad intelectual. Para desarrolladores, diseñadores y consultores.',
        'focus_keyword' => 'contrato de prestacion de servicios',
        'seo_keywords' => [
            'contrato de prestacion de servicios', 'modelo contrato prestacion de servicios colombia',
            'contrato freelance colombia', 'contrato para desarrollador de software',
            'contrato de servicios profesionales', 'propiedad intelectual contrato colombia',
        ],
        'faqs' => [
            [
                'question' => '¿Un contrato de prestación de servicios me puede convertir en empleado?',
                'answer' => "Puede, si en la práctica hay **subordinación**: horario impuesto, órdenes permanentes, herramientas del contratante y exclusividad. Los jueces miran la realidad, no el título del documento (principio de primacía de la realidad). Por eso incluimos una cláusula de independencia y te advertimos qué conductas evitar. Si lo que tienes es realmente una relación laboral, el contrato no la disfraza &mdash; y te lo diremos.",
            ],
            [
                'question' => '¿Quién queda dueño del trabajo que entrego?',
                'answer' => 'Depende de lo que pacten, y es la cláusula que más conflictos genera. En Colombia los derechos morales de autor son irrenunciables, pero los patrimoniales se pueden ceder. Lo normal es que el cliente reciba los derechos patrimoniales **una vez pagado el total**, no antes. Esa condición es tu mejor garantía de pago.',
            ],
            [
                'question' => '¿Por qué no es un documento automatizable?',
                'answer' => 'Porque un contrato para un desarrollador de software, uno para un diseñador y uno para una consultoría financiera no se parecen en nada. Los entregables, los hitos, el régimen de propiedad intelectual y los límites de responsabilidad cambian por completo. Un reemplazo de variables sobre una plantilla genérica produce un contrato que no protege a nadie.',
            ],
            [
                'question' => '¿Puedo incluir intereses por mora en los pagos?',
                'answer' => 'Sí, y deberías. Se puede pactar interés moratorio hasta el límite de la tasa máxima legal certificada por la Superintendencia Financiera. Lo incluimos con el tope correcto para que la cláusula no sea atacable por usura.',
            ],
        ],
        'body' => <<<'HTML'
<h2>Los tres problemas que arruinan un proyecto freelance</h2>
<h3>1. El alcance que crece</h3>
<p>Empieza como «una landing» y termina como una plataforma. Sin entregables definidos por escrito, cada petición nueva parece razonable y no hay punto desde el cual decir que eso ya es otro proyecto.</p>

<h3>2. Las revisiones infinitas</h3>
<p>«Un último cambio» repetido catorce veces. Un contrato serio dice cuántas rondas están incluidas y cuánto cuesta cada ronda adicional.</p>

<h3>3. El pago que nunca llega</h3>
<p>Entregaste todo y ahora el cliente no contesta. Si ya cediste la propiedad del trabajo y no fijaste fechas de pago, tu posición negociadora es cero.</p>

<h2>Cómo se blinda cada uno</h2>
<p><strong>Entregables enumerados</strong> con un criterio de aceptación claro. Lo que no está en la lista es un nuevo alcance y se cotiza aparte.</p>
<p><strong>Hitos de pago con fechas</strong> y anticipo. La ejecución se suspende si un hito no se paga, sin que eso te ponga en incumplimiento.</p>
<p><strong>Cesión condicionada al pago total.</strong> Mientras no te paguen, el cliente tiene a lo sumo una licencia temporal. Es la cláusula que más rápido desbloquea una factura.</p>
<p><strong>Límite de revisiones</strong> con tarifa para las adicionales.</p>
<p><strong>Cláusula de independencia</strong> para que un juez no reclasifique la relación como laboral.</p>

<h2>Por qué lo redacta un abogado y no un script</h2>
<p>Este es uno de los documentos de la Fase 2. Un contrato de desarrollo de software necesita hablar de repositorios, entornos, licencias de terceros y garantía de funcionamiento. Uno de diseño, de archivos fuente y de usos permitidos de la marca. Uno de consultoría, de límites de responsabilidad por las decisiones que tome el cliente. No hay plantilla que sirva para las tres: hay que escribirlo según tu caso.</p>
HTML,
    ],

    [
        'name' => 'Constitución de SAS por Documento Privado',
        'short_name' => 'Constituir SAS',
        'slug' => 'constitucion-de-sas',
        'practice_area' => 'empresarial',
        'automation_phase' => 'manual',
        'emoji' => '🏢',
        'tagline' => 'Formaliza tu empresa con estatutos hechos a tu medida, no copiados de un modelo.',
        'summary' => 'Documento privado de constitución de Sociedad por Acciones Simplificada con objeto social a la medida, estructura de capital, órganos de administración y facultades del representante legal.',
        'legal_basis' => 'Ley 1258 de 2008 (Sociedad por Acciones Simplificada)',
        'supports_signature' => true,
        'is_featured' => false,
        'sort_order' => 70,
        'what_you_get' => [
            'Documento privado de constitución listo para radicar en Cámara de Comercio',
            'Objeto social redactado para tu actividad real, con capacidad de crecer',
            'Estructura de capital: autorizado, suscrito y pagado, con la división exacta',
            'Nombramiento de representante legal y suplente, con facultades y límites',
            'Reglas de cesión de acciones y derecho de preferencia',
            'Cláusula de resolución de conflictos entre accionistas',
            'Guía del trámite completo: RUES, RUT, matrícula mercantil',
        ],
        'required_info' => [
            'Nombre de la sociedad y dos alternativas (por si el primero está tomado)',
            'Datos completos de cada accionista y el porcentaje de cada uno',
            'Actividad económica real y planes a mediano plazo',
            'Capital autorizado, suscrito y pagado',
            'Quién será representante legal y quién suplente',
            'Domicilio principal',
            'Si habrá junta directiva o revisor fiscal',
        ],
        'use_cases' => [
            'Vas a formalizar un negocio que ya opera',
            'Entran socios y necesitas que los porcentajes queden claros desde el día uno',
            'Necesitas factura y RUT para venderle a empresas grandes',
            'Buscas inversión y te piden estructura societaria',
            'Quieres separar tu patrimonio personal del riesgo del negocio',
        ],
        'not_included' => [
            'Pago de los derechos de matrícula en Cámara de Comercio',
            'Radicación del trámite (te damos la guía; también puedes delegárnosla)',
            'Inscripción del RUT ante la DIAN',
            'Acuerdo de accionistas (documento separado, lo cotizamos aparte)',
            'Registro de marca ante la SIC',
        ],
        'seo_title' => 'Cómo crear una SAS en Colombia | Documento privado de constitución',
        'seo_description' => 'Constitución de SAS por documento privado con estatutos a la medida: objeto social, capital, representación legal y cesión de acciones. Redactado por abogados societarios.',
        'focus_keyword' => 'como crear una sas en colombia',
        'seo_keywords' => [
            'como crear una sas en colombia', 'constituir sas', 'documento privado de constitucion sas',
            'crear empresa colombia', 'estatutos sas', 'ley 1258 de 2008',
            'cuanto cuesta crear una sas', 'sas unipersonal colombia',
        ],
        'faqs' => [
            [
                'question' => '¿Necesito notaría para constituir una SAS?',
                'answer' => "No, y es la gran ventaja de la Ley 1258 de 2008. Una SAS se constituye por **documento privado** que se radica directamente en la Cámara de Comercio. Solo se requiere escritura pública si se aportan bienes inmuebles al capital.",
            ],
            [
                'question' => '¿Puedo crear una SAS solo, sin socios?',
                'answer' => 'Sí. La SAS admite **un único accionista**, que puede ser persona natural o jurídica. Es la forma más usada para formalizar un emprendimiento individual limitando la responsabilidad al monto de los aportes.',
            ],
            [
                'question' => '¿Cuánto cuesta en total crear una SAS?',
                'answer' => 'Nuestro documento tiene el precio publicado. Aparte van los derechos de matrícula de la Cámara de Comercio, que se calculan sobre el capital y los activos: para un capital pequeño suelen estar entre $100.000 y $500.000, más el impuesto de registro. El RUT ante la DIAN es gratuito.',
            ],
            [
                'question' => '¿Por qué no usan un modelo de estatutos estándar?',
                'answer' => 'Porque el objeto social y las facultades del representante legal son decisiones de negocio, no formularios. Un objeto demasiado estrecho te obliga a reformar estatutos cuando crezcas; uno copiado de internet puede impedirte abrir una cuenta o firmar un contrato porque el banco considera que la actividad no está cubierta. Y las facultades del representante definen qué puede hacer sin consultar a los socios: ahí se juega el control de la empresa.',
            ],
            [
                'question' => '¿Qué diferencia hay entre capital autorizado, suscrito y pagado?',
                'answer' => 'El **autorizado** es el techo que fijas en estatutos. El **suscrito** es el que los accionistas se comprometen a aportar. El **pagado** es el que ya entregaron. En una SAS el suscrito se puede pagar en un plazo de hasta dos años, lo que da flexibilidad de caja al arrancar.',
            ],
        ],
        'body' => <<<'HTML'
<h2>Por qué la SAS se volvió la figura estándar en Colombia</h2>
<p>La Ley 1258 de 2008 creó un tipo societario pensado para que crear empresa no fuera un vía crucis: se constituye por documento privado, admite un solo accionista, limita la responsabilidad al monto de los aportes y permite una enorme libertad para diseñar los estatutos. Hoy es la forma jurídica de la gran mayoría de empresas nuevas del país.</p>
<p>Esa libertad es también el riesgo: como casi todo se puede pactar, lo que no se pacta bien queda mal.</p>

<h2>Las cuatro decisiones que definen tu empresa</h2>
<h3>1. El objeto social</h3>
<p>La SAS permite objeto indeterminado, pero en la práctica bancos, clientes corporativos y plataformas de pago revisan que tu actividad esté claramente descrita. Redactamos un objeto que cubra lo que haces hoy y deje espacio para lo que harás en tres años, sin tener que reformar estatutos.</p>

<h3>2. La estructura de capital</h3>
<p>Autorizado, suscrito y pagado, y la división exacta en acciones. Aquí se define el porcentaje de cada socio, y un error de redacción en los porcentajes es el origen de la mayoría de los conflictos societarios.</p>

<h3>3. Las facultades del representante legal</h3>
<p>¿Puede vender activos sin consultar? ¿Endeudar la sociedad? ¿Hasta qué monto firma solo? Estas facultades definen el control real. Las redactamos con topes y con las decisiones que quedan reservadas a la asamblea.</p>

<h3>4. La salida de los socios</h3>
<p>Derecho de preferencia en la cesión de acciones, autorización previa de la asamblea, y un mecanismo para resolver bloqueos. Pensarlo cuando todos se llevan bien es mucho más barato que pensarlo después.</p>

<h2>El trámite completo, paso a paso</h2>
<ol>
<li>Verificar la disponibilidad del nombre en el RUES.</li>
<li>Firmar el documento privado de constitución (es lo que te entregamos).</li>
<li>Radicarlo en la Cámara de Comercio del domicilio y pagar los derechos de matrícula.</li>
<li>Inscribir el RUT ante la DIAN.</li>
<li>Abrir la cuenta bancaria a nombre de la sociedad.</li>
</ol>

<h2>Lo que deberías considerar aparte</h2>
<p>Si son dos o más socios, los estatutos no bastan: conviene un <strong>acuerdo de accionistas</strong> que regule qué pasa si uno se va, si no aporta lo prometido o si hay desacuerdo en una decisión clave. Es un documento separado y lo cotizamos cuando nos escribas.</p>
HTML,
    ],

    [
        'name' => 'Derecho de Petición General',
        'short_name' => 'Derecho de petición',
        'slug' => 'derecho-de-peticion',
        'practice_area' => 'consumo',
        'automation_phase' => 'manual',
        'emoji' => '📄',
        'tagline' => 'Obliga a una entidad o empresa a responderte por escrito y en término.',
        'summary' => 'Derecho de petición redactado por un abogado para conflictos atípicos: cobros fantasma, aseguradoras, telcos, servicios públicos, entidades públicas y peticiones de información.',
        'legal_basis' => 'Constitución Política (art. 23) y Ley 1755 de 2015',
        'supports_signature' => false,
        'is_featured' => true,
        'sort_order' => 55,
        'what_you_get' => [
            'Derecho de petición dirigido a la entidad o empresa correcta',
            'Hechos ordenados y petición formulada de forma expresa y exigible',
            'Fundamento normativo del caso particular, no una plantilla genérica',
            'Solicitud de las pruebas o documentos que la entidad debe tener',
            'Advertencia de las consecuencias del silencio administrativo',
            'Guía de radicación y control de los términos de respuesta',
        ],
        'required_info' => [
            'Nombres completos y cédula',
            'Entidad o empresa a la que va dirigido',
            'Qué pasó, con fechas',
            'Qué quieres pedir exactamente',
            'Números de referencia: contrato, póliza, factura, radicado',
            'Si ya reclamaste antes y qué te respondieron',
        ],
        'use_cases' => [
            'Una telco te cobra un servicio que nunca pediste',
            'Una aseguradora niega un siniestro sin explicar bien por qué',
            'Una empresa de servicios públicos te facturó un consumo imposible',
            'Necesitas que una entidad pública te entregue información o copias',
            'Un banco te debita algo que no autorizaste',
            'Una universidad o colegio no te expide un documento',
        ],
        'not_included' => [
            'Radicación ante la entidad (te damos la guía; es gratuita)',
            'Representación en un proceso ante la SIC o la Superintendencia correspondiente',
            'Garantía de que la entidad resuelva a tu favor',
        ],
        'seo_title' => 'Cómo hacer un Derecho de Petición en Colombia | Redactado por abogado',
        'seo_description' => 'Derecho de petición redactado por abogados para cobros indebidos, aseguradoras, telcos y entidades públicas. Ley 1755 de 2015. Entrega en 24 horas desde $90.000.',
        'focus_keyword' => 'como hacer un derecho de peticion',
        'seo_keywords' => [
            'como hacer un derecho de peticion', 'derecho de peticion modelo',
            'formato derecho de peticion', 'derecho de peticion ley 1755',
            'derecho de peticion a una empresa privada', 'plazo para responder derecho de peticion',
            'cobro indebido colombia', 'reclamo a aseguradora colombia',
        ],
        'faqs' => [
            [
                'question' => '¿En cuánto tiempo deben responderme un derecho de petición?',
                'answer' => "Según la Ley 1755 de 2015: **15 días hábiles** como regla general; **10 días hábiles** si pides documentos o información; y **30 días hábiles** si es una consulta sobre materias a cargo de la entidad. Si no pueden responder en término, deben decírtelo antes del vencimiento explicando por qué y cuándo lo harán.",
            ],
            [
                'question' => '¿Le puedo hacer un derecho de petición a una empresa privada?',
                'answer' => 'Sí. El derecho de petición también se puede ejercer ante particulares, especialmente cuando prestan un servicio público, están en posición dominante o manejan tus datos personales. Bancos, EPS, aseguradoras, telcos y colegios están obligados a responder.',
            ],
            [
                'question' => '¿Qué pasa si no me responden?',
                'answer' => 'Se configura la violación de un derecho fundamental y procede una **acción de tutela**, que el juez resuelve en 10 días hábiles. En la práctica es muy eficaz: casi ninguna entidad quiere un fallo de tutela en su contra por no contestar. Si llegas a ese punto, podemos redactar la tutela.',
            ],
            [
                'question' => '¿Por qué no es automatizable como el de Datacrédito?',
                'answer' => 'Porque el de Datacrédito tiene una estructura fija y causales tipificadas en la ley. Un conflicto con una aseguradora o un cobro fantasma de una telco exige argumentar sobre la particularidad del caso: qué dice tu contrato, qué cláusula se está aplicando mal, qué norma de protección al consumidor aplica. Eso es criterio jurídico, no reemplazo de variables.',
            ],
        ],
        'body' => <<<'HTML'
<h2>El derecho de petición es la herramienta más subestimada de Colombia</h2>
<p>El artículo 23 de la Constitución te da derecho a presentar peticiones a las autoridades y obtener resolución. La Ley 1755 de 2015 lo desarrolló con plazos concretos y consecuencias por el incumplimiento. Es gratuito, no requiere abogado y obliga a la entidad a pronunciarse por escrito.</p>
<p>Lo que casi nadie aprovecha es que, bien usado, un derecho de petición genera un documento que después sirve como prueba: la respuesta de la entidad, o su silencio, queda registrada.</p>

<h2>Por qué la mayoría no funciona</h2>
<h3>Pide cosas que no se pueden ordenar</h3>
<p>«Solicito que resuelvan mi caso» no es una petición. «Solicito la devolución de $340.000 debitados el 14 de marzo de 2026 del contrato 884512, por no corresponder a un servicio contratado» sí lo es.</p>

<h3>No cita la norma correcta</h3>
<p>Un cobro indebido de una telco se ataca con el Régimen de Protección de Usuarios de Comunicaciones. Un siniestro negado, con el Código de Comercio y el Estatuto del Consumidor. Una facturación imposible de servicios públicos, con la Ley 142 de 1994. Citar la norma correcta cambia el tono de la respuesta.</p>

<h3>No pide las pruebas</h3>
<p>Casi siempre conviene pedir los documentos que la entidad está obligada a conservar: la grabación de la llamada donde supuestamente aceptaste, el contrato firmado, el soporte de la lectura del medidor. Muchas veces no los tienen, y ahí se gana el caso.</p>

<h2>Qué hacemos</h2>
<p>Un abogado lee tu caso, identifica la norma aplicable, ordena los hechos con fechas y referencias, formula la petición de forma expresa y exigible, solicita las pruebas pertinentes y advierte las consecuencias del silencio. Te entregamos el documento y la guía de radicación, con los términos que debes vigilar.</p>

<h2>Y si no responden</h2>
<p>El silencio frente a un derecho de petición vulnera un derecho fundamental y habilita la acción de tutela, que se resuelve en 10 días hábiles. Es el camino que más veces desatasca un trámite imposible.</p>
HTML,
    ],

];
